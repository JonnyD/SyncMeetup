<?php

namespace GoogleCal\Controller;

use GoogleCal\Helper\ClientHelper;
use GoogleCal\Service\GoogleService;
use GoogleCal\Service\MeetupService;
use GoogleCal\Service\UserService;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RootController extends Controller
{
    private $userService;
    private $meetupService;
    private $googleService;

    public function __construct(UserService $userService,
                                MeetupService $meetupService,
                                GoogleService $googleService)
    {
        $this->userService = $userService;
        $this->meetupService = $meetupService;
        $this->googleService = $googleService;
    }

    public function populateParent($twig, $session, $formFactory, $urlGenerator)
    {
        parent::__construct($twig, $session, $formFactory, $urlGenerator);
    }

    public function indexAction()
    {
        $user = $this->userService->getLoggedInUser();
        $meetupDetails = $user->getMeetupDetails();

        $googleClient = $this->googleService->createClient();
        $googleDetails = $user->getGoogleDetails();
        $googleClient->setAccessToken($googleDetails->getAccessToken());
        $googleAuthUrl = $googleClient->createAuthUrl();

        $meetupClient = $this->meetupService->createClient();
        $meetupAuthUrl = $meetupClient->createAuthUrl();

        return $this->render('index.twig', array(
            'user' => $user,
            'googleAuthUrl' => $googleAuthUrl,
            'meetupAuthUrl' => $meetupAuthUrl
        ));
    }

    public function logoutAction()
    {
        $this->userService->logoutUser();

        return $this->redirect('home');
    }
}