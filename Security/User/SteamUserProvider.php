<?php
namespace SteamAuthBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use GuzzleHttp\Client;
use Doctrine\ORM\EntityManager;
use SteamAuthBundle\Security\User\SteamUserInterface;

class SteamUserProvider implements UserProviderInterface
{
    private $em;
    private $guzzle;
    private $steamKey;
    private $userClass;

    public function __construct(EntityManager $em, Client $guzzle, $steamKey, $userClass)
    {
        $this->em = $em;
        $this->guzzle = $guzzle;
        $this->steamKey = $steamKey;
        $this->userClass = $userClass;
    }

    public function loadUserByUsername($username)
    {
        $userRepo = $this->em->getRepository($this->userClass);
        $user = $userRepo->findOneBy(['username' => $username]);
        if ($user) return $user;

        $response = $this->guzzle->request('GET', 'GetPlayerSummaries/v0002/', ['query' => ['steamids' => $username, 'key' => $this->steamKey]]);
        $userdata = json_decode($response->getBody(), true);
        if (isset($userdata['response']['players'][0])) {
            $data = $userdata['response']['players'][0];
            $user = new $this->userClass();
            if (!$user instanceof SteamUserInterface) throw new UnsupportedUserException("User class does not implement SteamUserInterface");
            $user->setUsername($username);
            $user->setNickname($data['personaname']);
            $user->setAvatar($data['avatar']);
            $user->setPassword(base64_encode(random_bytes(20)));
            $this->em->persist($user);
            $this->em->flush($user);
            return $user;
        }

        throw new UsernameNotFoundException("Username does not exist");
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof SteamUserInterface) {
            throw new UnsupportedUserException("User not supported");
        }
        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === $this->userClass;
    }
}
