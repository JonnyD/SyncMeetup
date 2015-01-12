<?php

namespace GoogleCal\Service;

use GoogleCal\Entity\User;
use GoogleCal\Repository\GoogleDetailsRepository;
use GoogleCal\Repository\MeetupDetailsRepository;
use GoogleCal\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Session\Session;

class UserService
{
    private $userRepository;
    private $meetupDetailsRepository;
    private $googleDetailsRepository;
    private $session;

    public function __construct(UserRepository $userRepository,
                                MeetupDetailsRepository $meetupDetailsRepository,
                                GoogleDetailsRepository $googleDetailsRepository,
                                Session $session)
    {
        $this->userRepository = $userRepository;
        $this->meetupDetailsRepository = $meetupDetailsRepository;
        $this->googleDetailsRepository = $googleDetailsRepository;
        $this->session = $session;
    }

    public function getLoggedInUser()
    {
        $userId = $this->session->get('user')->get('id');
        return $this->getUser($userId);
    }

    public function logoutUser()
    {
        $this->session->remove('user');
    }

    public function getUser($id)
    {
        return $this->userRepository->find($id);
    }

    public function getUserByMeetupId($meetupId)
    {
        return $this->userRepository->findByMeetupId($meetupId);
    }

    public function getMeetupDetailsByMeetupId($meetupId)
    {
        return $this->meetupDetailsRepository->findByMeetupId($meetupId);
    }

    public function save(User $user)
    {
        $meetupDetails = $user->getMeetupDetails();
        if ($meetupDetails) {
            $meetupDetails = $this->meetupDetailsRepository->save($meetupDetails);
            $user->setMeetupDetails($meetupDetails);
        }
        $googleDetails = $user->getGoogleDetails();
        if ($googleDetails) {
            $googleDetails = $this->googleDetailsRepository->save($googleDetails);
            $user->setGoogleDetails($googleDetails);
        }
        $user = $this->userRepository->save($user);
        return $user;
    }
}