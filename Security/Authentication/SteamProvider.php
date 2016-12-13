<?php
namespace SteamAuthBundle\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use GuzzleHttp\Client;
use SteamAuthBundle\Security\Token\SteamToken;

class SteamProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $guzzle;

    public function __construct(UserProviderInterface $userProvider, Client $guzzle)
    {
        $this->userProvider = $userProvider;
        $this->guzzle = $guzzle;
    }

    public function authenticate(TokenInterface $token)
    {
        if ($token->getAttribute('openid.ns') != "http://specs.openid.net/auth/2.0") {
            throw new AuthenticationException('Invalid Token');
        }

        $checkAuth = $token->getAttributes();
        $checkAuth['openid.mode'] = 'check_authentication';
        $response = $this->guzzle->request('GET', 'login', ['query' => $checkAuth]);
        
        if ((string)$response->getBody() == "ns:http://specs.openid.net/auth/2.0\nis_valid:true\n") {
            $user = $this->userProvider->loadUserByUsername($token->getUsername());
            $token->setUser($user);
            $token->setAuthenticated(true);
        }

        return $token;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof SteamToken;
    }
}
