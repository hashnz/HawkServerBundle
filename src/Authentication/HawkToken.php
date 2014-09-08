<?php

namespace Hashnz\HawkServerBundle\Authentication;

use Dflydev\Hawk\Server\Response;
use Dflydev\Hawk\Server\Server;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class HawkToken extends AbstractToken
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var string Authorization header
     */
    private $auth;

    /**
     * @var Response
     */
    private $hawkResponse;

    /**
     * @var Server
     */
    private $hawkServer;

    /**
     * @param array $roles
     */
    public function __construct(array $roles = array())
    {
        parent::__construct($roles);

        // If the user has roles, consider it authenticated
        $this->setAuthenticated(count($roles) > 0);
    }

    /**
     * @param Server $hawkServer
     */
    public function setHawkServer($hawkServer)
    {
        $this->hawkServer = $hawkServer;
    }

    /**
     * @return Server
     */
    public function getHawkServer()
    {
        return $this->hawkServer;
    }

    /**
     * @param Response $hawkResponse
     */
    public function setHawkResponse($hawkResponse)
    {
        $this->hawkResponse = $hawkResponse;
    }

    /**
     * @return Response
     */
    public function getHawkResponse()
    {
        return $this->hawkResponse;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return mixed|string
     */
    public function getCredentials()
    {
        return '';
    }

    /**
     * @param string $auth
     */
    public function setAuth($auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return string
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->request->getContent() ?: null;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->request->headers->get('Content-Type');
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->request->getHost();
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->request->getMethod();
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->request->getPort();
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->request->getRequestUri();
    }
}
