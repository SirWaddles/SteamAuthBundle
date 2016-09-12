<?php
namespace SteamAuthBundle\Security\User;

interface SteamUserInterface
{
    public function setNickname($nickname);
    public function getUsername();
    public function setUsername($username);
    public function setAvatar($avatar);
    public function setPassword($password);
}
