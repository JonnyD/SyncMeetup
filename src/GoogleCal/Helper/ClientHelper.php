<?php

namespace GoogleCal\Helper;

use GoogleCal\Entity\User;
use GoogleCal\Plugin\GoogleClient;
use GoogleCal\Plugin\MeetupClient;
use Silex\Application;

class ClientHelper
{
    private $app;
    private $googleClient;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function createGoogleClient($approvalPrompt = "auto")
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

    public function getGoogleClient($approvalPrompt = "auto")
    {
        if (!$this->googleClient) {
            $googleClient = $this->createGoogleClient($approvalPrompt);
            $this->googleClient = $googleClient;
        }
        return $this->googleClient;
    }

    public function createMeetupClient()
    {
        $client = new MeetupClient(array(
            'client_id'     => $this->app['meetup.client_id'],
            'client_secret' => $this->app['meetup.client_secret'],
            'redirect_uri'  => $this->app['meetup.redirect_uri'],
            'response_type' => 'code'
        ));
        return $client;
    }
}