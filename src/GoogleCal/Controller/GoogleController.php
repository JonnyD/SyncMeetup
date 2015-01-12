<?php

namespace GoogleCal\Controller;

use GoogleCal\Helper\ClientHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class GoogleController
{
    private $session;
    private $clientHelper;

    public function __construct(Session $session, ClientHelper $clientHelper)
    {
        $this->session = $session;
        $this->clientHelper = $clientHelper;
    }

    public function connectAction(Request $request)
    {
        $client = $this->clientHelper->createGoogleClient();
        $redirectUrl = $client->createAuthUrl();

        if ($request->get('code')) {
            $client->authenticate($request->get('code'));
            $redirectUrl = '../';
        }

        return new RedirectResponse($redirectUrl);
    }
}