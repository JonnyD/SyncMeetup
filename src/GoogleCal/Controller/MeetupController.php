<?php

namespace GoogleCal\Controller;

use GoogleCal\Entity\MeetupDetails;
use GoogleCal\Entity\User;
use GoogleCal\Helper\ClientHelper;
use GoogleCal\Service\UserService;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class MeetupController
{
    private $session;
    private $userService;
    private $clientHelper;

    public function __construct(Session $session, UserService $userService,
                                ClientHelper $clientHelper)
    {
        $this->session = $session;
        $this->userService = $userService;
        $this->clientHelper = $clientHelper;
    }

    public function connectAction(Request $request)
    {
        $client = $this->clientHelper->createMeetupClient();
        $redirectUrl = $client->createAuthUrl();

        if($request->get('code')) {
            $response = $client->authenticate($request->get('code'));
            $client->setAccessToken($response->access_token);
            $meetupUser = $client->getCurrentMember();

            $meetupDetails = $this->populateMeetupDetails($meetupUser, $response);
            $user = $this->populateUser($meetupUser, $meetupDetails);
            $this->userService->save($user);

            $userSession = new ParameterBag();
            $userSession->set('id', $user->getId());
            $this->session->set('user', $userSession);

            $redirectUrl = "../";
        }

        return new RedirectResponse($redirectUrl);
    }

    private function populateMeetupDetails($meetupUser, $response)
    {
        $meetupDetails = $this->userService->getMeetupDetailsByMeetupId($meetupUser->id);
        if ($meetupDetails == null) {
            $meetupDetails = new MeetupDetails();
        }
        $meetupDetails->setMeetupId($meetupUser->id);
        $meetupDetails->setName($meetupUser->name);
        $meetupDetails->setAccessToken($response->access_token);
        $meetupDetails->setRefreshToken($response->refresh_token);
        if (isset($meetupUser->photo)) {
            $meetupDetails->setThumbnail($meetupUser->photo->thumb_link);
        }
        return $meetupDetails;
    }

    private function populateUser($meetupUser, $meetupDetails)
    {
        $user = $this->userService->getUserByMeetupId($meetupUser->id);
        if ($user == null) {
            $user = new User();
        }
        $user->setMeetupDetails($meetupDetails);
        return $user;
    }
}