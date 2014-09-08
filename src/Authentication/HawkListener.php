<?php

namespace Hashnz\HawkServerBundle\Authentication;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Hashnz\HawkServerBundle\Authentication\HawkToken;

class HawkListener implements ListenerInterface, EventSubscriberInterface
{
    protected $securityContext;
    protected $authenticationManager;

    public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, EventDispatcherInterface $dispatcher)
    {
        $this->securityContext = $securityContext;
        $this->authenticationManager = $authenticationManager;
        // need to subscribe to the response event to sign the response
        $dispatcher->addSubscriber($this);
    }

    public function handle(GetResponseEvent $event)
    {
        $auth = $event->getRequest()->headers->get('authorization');
        // extract user id from auth header
        preg_match('/id="(.*)"/iU', $auth, $matches);
        $id = isset($matches[1]) ? $matches[1] : '';

        $token = new HawkToken();
        $token->setUser($id);
        $token->setRequest($event->getRequest());
        $token->setAuth($auth);

        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->securityContext->setToken($authToken);

            return;
        } catch (AuthenticationException $failed) {
            $response = new Response();
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            $event->setResponse($response);
        }
    }

    /**
     * On response notified - Use hawk to sign the response if necessary
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        // if there is a hawk security token, sign the response
        $token = $this->securityContext->getToken();
        if ($token instanceof HawkToken) {

            $hawkResponse = $token->getHawkResponse();
            $hawkServer = $token->getHawkServer();

            if (!empty($hawkResponse)) { // only set the header if there is a Hawk Response to use
                $response = $event->getResponse();
                $hawkHeader = $hawkServer->createHeader($hawkResponse->credentials(), $hawkResponse->artifacts(), array(
                    'payload' => $response->getContent() ?: null,
                    'content_type' => $response->headers->get('Content-Type'),
                ));
                $response->headers->set($hawkHeader->fieldName(), $hawkHeader->fieldValue());
            }
        }
    }

    /**
     * @inheritdoc
     */
    static public function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE   => 'onKernelResponse',
        );
    }
}