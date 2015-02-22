<?php

namespace GoogleCal\Controller;

use GoogleCal\Helper\ClientHelper;
use GoogleCal\Service\GoogleService;
use GoogleCal\Service\MeetupService;
use GoogleCal\Service\UserService;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RootController
{
    private $userService;
    private $meetupService;
    private $googleService;
    private $twig;
    private $session;
    private $formFactory;
    private $urlGenerator;

    public function __construct(UserService $userService,
                                MeetupService $meetupService,
                                GoogleService $googleService,
                                $twig,
                                $session,
                                $formFactory,
                                $urlGenerator)
    {
        $this->userService = $userService;
        $this->meetupService = $meetupService;
        $this->googleService = $googleService;
        $this->twig = $twig;
        $this->session = $session;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
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

        return $this->twig->render('index.twig', array(
            'user' => $user,
            'googleAuthUrl' => $googleAuthUrl,
            'meetupAuthUrl' => $meetupAuthUrl
        ));
    }
}