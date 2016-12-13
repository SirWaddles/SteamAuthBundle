<?php
namespace SteamAuthBundle\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use SteamAuthBundle\Service\SteamUserService;
use Doctrine\ORM\EntityManager;
use SteamAuthBundle\Security\User\SteamUserInterface;

class SteamUserProvider implements UserProviderInterface
{
    private $em;
    private $userClass;
    private $userService;

    public function __construct(EntityManager $em, SteamUserService $userService, $userClass)
    {
        $this->em = $em;
        $this->userClass = $userClass;
        $this->userService = $userService;
    }

    public function loadUserByUsername($username)
    {
        $userRepo = $this->em->getRepository($this->userClass);
        $user = $userRepo->findOneBy(['username' => $username]);

        if (!$user) {
            $user = new $this->userClass();
            $user->setUsername($username);
            $user->setPassword(base64_encode(random_bytes(20)));
            $this->userService->updateUserEntry($user);

            $this->em->persist($user);
            $this->em->flush($user);
        }

        return $user;
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
