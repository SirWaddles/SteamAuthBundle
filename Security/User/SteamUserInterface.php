<?php
namespace SteamAuthBundle\Security\User;

interface SteamUserInterface
{
    /**
     * Returns the current Steam nickname of the user.
     *
     * @return string The current nickname
     */
    public function getNickname();

    /**
     * Sets the users current nickname.
     *
     * @param string $nickname
     */
    public function setNickname($nickname);

    /**
     * Sets the username.
     *
     * The username represents the unique SteamID.
     *
     * @param string $username
     */
    public function setUsername($username);

    /**
     * Returns the url to the avatar of the user.
     *
     * @return string The avatar url
     */
    public function getAvatar();

    /**
     * Sets the url to the users avatar
     *
     * @param string $avatar
     */
    public function setAvatar($avatar);
}
