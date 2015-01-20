<?php

namespace GoogleCal\Service;

use GoogleCal\Entity\GoogleDetails;
use GoogleCal\Entity\User;
use GoogleCal\Helper\DateHelper;
use GoogleCal\Plugin\GoogleClient;
use GoogleCal\Repository\GoogleDetailsRepository;
use Silex\Application;

class GoogleService
{
    private $app;
    private $googleDetailsRepository;

    public function __construct(Application $app, GoogleDetailsRepository $googleDetailsRepository)
    {
        $this->app = $app;
        $this->googleDetailsRepository = $googleDetailsRepository;
    }

    public function createClient($approvalPrompt = "auto")
    {
        $client = new GoogleClient();
        $client->setClientId($this->app['google.client_id']);
        $client->setClientSecret($this->app['google.client_secret']);
        $client->setRedirectUri($this->app['google.redirect_uri']);
        $client->setScopes('https://www.googleapis.com/auth/calendar');
        $client->setAccessType("offline");
        $client->setApprovalPrompt($approvalPrompt);
        return $client;
    }

    public function getClientForUser(User $user)
    {
        $googleDetails = $user->getGoogleDetails();
        $googleClient = $this->createClient();
        $googleClient->setAccessToken($googleDetails->getAccessToken());
        $googleClient->activateCalendarService();
        return $googleClient;
    }

    public function refreshTokenByUser(User $user)
    {
        $googleDetails = $user->getGoogleDetails();
        $googleClient = $this->getClientForUser($user);
        $googleClient->refreshToken($googleDetails->getRefreshToken());
        $googleDetails->setAccessToken($googleClient->getAccessToken());
        $googleDetails->setExpires(DateHelper::createExpiryDate());
        $this->save($googleDetails);
    }

    public function getOrCreateGoogleDetails(User $user, GoogleClient $client)
    {
        $googleDetails = $user->getGoogleDetails();
        if (!$googleDetails) {
            $googleDetails = new GoogleDetails();
        }
        $googleDetails->setAccessToken($client->getAccessToken());
        $googleDetails->setRefreshToken($client->getRefreshToken());
        $googleDetails->setExpires(DateHelper::createExpiryDate());
        return $googleDetails;
    }

    public function save(GoogleDetails $googleDetails)
    {
        $this->googleDetailsRepository->save($googleDetails);
    }
}