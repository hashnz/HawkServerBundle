<?php

namespace Hashnz\HawkServerBundle\Authentication;

use Dflydev\Hawk\Credentials\Credentials;
use Dflydev\Hawk\Server\ServerBuilder;
use Dflydev\Hawk\Server\UnauthorizedException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class HawkProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        // create a credentials provider for hawk
        $credentialsProvider = function ($id) use ($user) {
            if ($id === $user->getUsername()) {
                return new Credentials($user->getPassword(), 'sha256', $user->getUsername());
            }
        };
        $hawkServer = ServerBuilder::create($credentialsProvider)->build();

        try {
            $hawkResponse = $hawkServer->authenticate(
                $token->getMethod(),
                $token->getHost(),
                $token->getPort(),
                $token->getUri(),
                $token->getContentType(),
                $token->getContent(),
                $token->getAuth()
            );
        }
        catch(UnauthorizedException $e) {
            throw new AuthenticationException();
        }

        // create and return authenticated token
        $authenticatedToken = new HawkToken($user->getRoles());
        $authenticatedToken->setUser($user);
        $authenticatedToken->setAuthenticated(true);
        $authenticatedToken->setHawkResponse($hawkResponse);
        $authenticatedToken->setHawkServer($hawkServer);

        return $authenticatedToken;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof HawkToken;
    }
} 