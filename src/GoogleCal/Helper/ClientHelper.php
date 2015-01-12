<?php

namespace GoogleCal\Helper;

use GoogleCal\Plugin\MeetupClient;
use Silex\Application;

class ClientHelper
{
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function createGoogleClient()
    {
        $client = new \Google_Client();
        $client->setClientId($this->app['google.client_id']);
        $client->setClientSecret($this->app['google.client_secret']);
        $client->setRedirectUri($this->app['google.redirect_uri']);
        $client->setScopes('https://www.googleapis.com/auth/calendar');
        $client->setAccessType("offline");
        return $client;
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