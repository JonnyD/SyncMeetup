<?php

namespace GoogleCal\Controller;

use GoogleCal\Service\GoogleService;
use GoogleCal\Service\MeetupService;
use GoogleCal\Service\UserService;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthController
{
    private $googleService;
    private $userService;
    private $meetupService;
    private $twig;
    private $session;
    private $formFactory;
    private $urlGenerator;

    public function __construct(GoogleService $googleService,
                                UserService $userService,
                                MeetupService $meetupService,
                                $twig,
                                $session,
                                $formFactory,
                                $urlGenerator)
    {
        $this->googleService = $googleService;
        $this->userService = $userService;
        $this->meetupService = $meetupService;
        $this->twig = $twig;
        $this->session = $session;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
    }

    public function connectGoogleAction(Request $request)
    {
        $client = $this->googleService->createClient("force");
        $redirectUrl = $client->createAuthUrl();

        if ($request->get('code')) {
            $client->authenticate($request->get('code'));

            $user = $this->userService->getLoggedInUser();
            $googleDetails = $this->googleService->getOrCreateGoogleDetails($user, $client);
            $user->setGoogleDetails($googleDetails);
            $this->userService->save($user);

            $redirectUrl = $this->urlGenerator->generate('home');
        }

        return new RedirectResponse($redirectUrl);
    }

    public function refreshGoogleAction(Request $request)
    {
        $user = $this->userService->getLoggedInUser();
        $this->googleService->refreshTokenByUser($user);

        $redirectUrl = $this->urlGenerator->generate('home');
        $referrer = $request->get('referrer');
        if ($referrer) {
            $redirectUrl = $this->urlGenerator->generate($referrer);
        }

        return new RedirectResponse($redirectUrl);
    }

    public function connectMeetupAction(Request $request)
    {
        $client = $this->meetupService->createClient();
        $redirectUrl = $client->createAuthUrl();

        if ($request->get('code')) {
            $response = $client->authenticate($request->get('code'));
            $meetupUser = $client->getCurrentMember();
            $meetupDetails = $this->meetupService->mapFromAPI($meetupUser, $response);

            $user = $this->userService->ensureUserByMeetupId($meetupUser->id);
            $user->setMeetupDetails($meetupDetails);
            $this->userService->save($user);

            $userSession = new ParameterBag();
            $userSession->set('id', $user->getId());
            $this->session->set('user', $userSession);

            $redirectUrl = $this->urlGenerator->generate('home');
        }

        return new RedirectResponse($redirectUrl);
    }

    public function refreshMeetupAction(Request $request)
    {
        $user = $this->userService->getLoggedInUser();
        $this->meetupService->refreshTokenByUser($user);

        $redirectUrl = $this->urlGenerator->generate('home');
        $referrer = $request->get('referrer');
        if ($referrer) {
            $redirectUrl = $this->urlGenerator->generate($referrer);
        }

        return new RedirectResponse($redirectUrl);
    }

    public function logoutAction()
    {
        $this->userService->logoutUser();

        return new RedirectResponse('home');
    }
}