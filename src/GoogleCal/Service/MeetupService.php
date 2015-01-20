<?php

namespace GoogleCal\Service;

use GoogleCal\Entity\MeetupDetails;
use GoogleCal\Entity\User;
use GoogleCal\Helper\DateHelper;
use GoogleCal\Plugin\MeetupClient;
use GoogleCal\Repository\MeetupDetailsRepository;
use Silex\Application;

class MeetupService
{
    private $app;
    private $meetupDetailsRepository;

    public function __construct(Application $app, MeetupDetailsRepository $meetupDetailsRepository)
    {
        $this->app = $app;
        $this->meetupDetailsRepository = $meetupDetailsRepository;
    }

    public function createClient()
    {
        $client = new MeetupClient(array(
            'client_id'     => $this->app['meetup.client_id'],
            'client_secret' => $this->app['meetup.client_secret'],
            'redirect_uri'  => $this->app['meetup.redirect_uri'],
            'response_type' => 'code'
        ));
        return $client;
    }

    public function getClientByUser(User $user)
    {
        $meetupDetails = $user->getMeetupDetails();
        $client = $this->createClient();
        $client->setAccessToken($meetupDetails->getAccessToken());
        $client->setRefreshToken($meetupDetails->getRefreshToken());
        return $client;
    }

    public function ensureMeetupDetails($meetupId)
    {
        $meetupDetails = $this->meetupDetailsRepository->findByMeetupId($meetupId);
        if ($meetupDetails == null) {
            $meetupDetails = new MeetupDetails();
        }
        return $meetupDetails;
    }

    public function mapFromAPI($meetupUser, $response)
    {
        $meetupDetails = $this->ensureMeetupDetails($meetupUser->id);
        $meetupDetails->setMeetupId($meetupUser->id);
        $meetupDetails->setName($meetupUser->name);
        $meetupDetails->setAccessToken($response->access_token);
        $meetupDetails->setRefreshToken($response->refresh_token);
        if (isset($meetupUser->photo)) {
            $meetupDetails->setThumbnail($meetupUser->photo->thumb_link);
        }
        $meetupDetails->setExpires(DateHelper::createExpiryDate());
        return $meetupDetails;
    }

    public function refreshTokenByUser(User $user)
    {
        $meetupDetails = $user->getMeetupDetails();
        $client = $this->getClientByUser($user);
        $response = $client->refresh($meetupDetails->getRefreshToken());
        $meetupDetails->setRefreshToken($response->refresh_token);
        $meetupDetails->setAccessToken($response->access_token);
        $meetupDetails->setExpires(DateHelper::createExpiryDate());
        $this->save($meetupDetails);
    }

    public function save(MeetupDetails $meetupDetails)
    {
        $this->meetupDetailsRepository->save($meetupDetails);
    }
}