<?php
namespace SteamAuthBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Router;
use SteamAuthBundle\Security\Token\SteamToken;

class SteamListener implements ListenerInterface
{
    use TargetPathTrait;

    private $tokenStorage;
    private $authenticationManager;
    private $router;
    private $rememberMeServices;
    private $providerKey;
    private $defaultRoute;

    public function __construct($defaultRoute, TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, Router $router)
    {
        $this->defaultRoute = $defaultRoute;
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->router = $router;
    }

    public function setProviderKey($providerKey)
    {
        $this->providerKey = $providerKey;
    }

    public function getProviderKey($providerKey)
    {
        return $this->providerKey;
    }

    public function setRememberMeServices(RememberMeServicesInterface $rememberMeServices)
    {
        $this->rememberMeServices = $rememberMeServices;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($request->get('_route') != 'login_check') {
            return;
        }

        $token = new SteamToken();
        $token->setUsername(str_replace("http://steamcommunity.com/openid/id/", "", $request->query->get('openid_claimed_id')));
        $token->setAttributes($request->query->all());

        try {
            $authToken = $this->authenticationManager->authenticate($token);
            $this->tokenStorage->setToken($authToken);

            $targetPath = $this->getTargetPath($request->getSession(), $this->providerKey);
            if ($targetPath !== null) {
                $this->removeTargetPath($request->getSession(), $this->providerKey);
            } else {
                $targetPath = $this->router->generate($this->defaultRoute);
            }

            $response = new RedirectResponse($targetPath);
            if ($this->rememberMeServices !== null) {
                $this->rememberMeServices->loginSuccess($request, $response, $token);
            }
            $event->setResponse($response);
            return;
            
        } catch (AuthenticationException $e) {
           throw new AuthenticationException($e->getMessage());
        }
    }
}
