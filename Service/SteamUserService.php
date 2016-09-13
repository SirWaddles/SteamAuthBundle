<?php
namespace SteamAuthBundle\Service;

use GuzzleHttp\Client;
use SteamAuthBundle\Security\User\SteamUserInterface;

class SteamUserService
{
    private $guzzle;
    private $steamKey;

    public function __construct(Client $guzzle, $steamKey)
    {
        $this->guzzle = $guzzle;
        $this->steamKey = $steamKey;
    }

    public function getUserData($communityId)
    {
        $response = $this->guzzle->request('GET', 'GetPlayerSummaries/v0002/', ['query' => ['steamids' => $communityId, 'key' => $this->steamKey]]);
        $userdata = json_decode($response->getBody(), true);
        if (isset($userdata['response']['players'][0])) {
            return $userdata['response']['players'][0];
        }
        return NULL;
    }

    public function updateUserEntry(SteamUserInterface $user)
    {
        $userdata = $this->getUserData($user->getUsername());
        $user->setNickname($userdata['personaname']);
        $user->setAvatar($userdata['avatar']);
        return $user;
    }
}
