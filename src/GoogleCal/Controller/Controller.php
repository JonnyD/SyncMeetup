<?php

namespace GoogleCal\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;

class Controller
{
    protected $twig;
    protected $session;
    protected $formFactory;
    protected $urlGenerator;

    public function __construct($twig, Session $session, $formFactory, UrlGenerator $urlGenerator)
    {
        $this->twig = $twig;
        $this->session = $session;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
    }

    public function redirect($url)
    {
        return new RedirectResponse($url);
    }

    public function render($view, $parameters)
    {
        return $this->twig->render($view, $parameters);
    }

    public function generateUrl($view, $parameters)
    {
        return $this->urlGenerator->generate($view, $parameters);
    }
}