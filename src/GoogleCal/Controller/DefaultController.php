<?php

namespace GoogleCal\Controller;

use GoogleCal\Helper\ClientHelper;
use GoogleCal\Service\UserService;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController
{
    private $userService;
    private $clientHelper;
    private $twig;

    public function __construct(UserService $userService, ClientHelper $clientHelper,
                                $twig)
    {
        $this->userService = $userService;
        $this->clientHelper = $clientHelper;
        $this->twig = $twig;
    }

    public function indexAction()
    {
        $user = $this->userService->getLoggedInUser();

        $googleClient = $this->clientHelper->createGoogleClient();
        $googleAuthUrl = $googleClient->createAuthUrl();

        $meetupClient = $this->clientHelper->createMeetupClient();
        $meetupAuthUrl = $meetupClient->createAuthUrl();

        return $this->twig->render('index.twig', array(
            'user' => $user,
            'googleAuthUrl' => $googleAuthUrl,
            'meetupAuthUrl' => $meetupAuthUrl
        ));
    }

    public function logoutAction()
    {
        $this->userService->logoutUser();

        return new RedirectResponse("../");
    }
}