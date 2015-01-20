<?php

namespace GoogleCal\Controller;

use GoogleCal\Form\SelectCalendarForm;
use GoogleCal\Service\GoogleService;
use GoogleCal\Service\MeetupService;
use GoogleCal\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class CalendarController extends Controller
{
    private $googleService;
    private $userService;
    private $meetupService;

    public function __construct(GoogleService $googleService,
                                UserService $userService,
                                MeetupService $meetupService)
    {
        $this->googleService = $googleService;
        $this->userService = $userService;
        $this->meetupService = $meetupService;
    }

    public function populateParent($twig, $session, $formFactory, $urlGenerator)
    {
        parent::__construct($twig, $session, $formFactory, $urlGenerator);
    }

    public function selectCalendarAction(Request $request)
    {
        $user = $this->userService->getLoggedInUser();
        $client = $this->googleService->getClientForUser($user);
        $selectCalendarForm = new SelectCalendarForm($client->getCalendars());
        $form = $this->formFactory->createBuilder($selectCalendarForm, array())->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $calendar = $data["calendar"];

            $user = $this->userService->getLoggedInUser();
            $user->setCalendar($calendar);
            $this->userService->save($user);
        }

        return $this->render('selectCalendar.twig', array(
            'form' => $form->createView()
        ));
    }

    public function syncAction()
    {
        $user = $this->userService->getLoggedInUser();

        $meetupDetails = $user->getMeetupDetails();
        if ($meetupDetails->hasExpired()) {
            $redirectUrl = $this->generateUrl('meetup.refresh', array('referrer' => 'google.sync'));
            return $this->redirect($redirectUrl);
        }

        $meetupClient = $this->meetupService->getClientByUser($user);
        $events = $meetupClient->getEvents(array('member_id' => 'self'));

        $googleDetails = $user->getGoogleDetails();
        if ($googleDetails->hasExpired()) {
            $redirectUrl = $this->generateUrl('google.refresh', array('referrer' => 'google.sync'));
            return $this->redirect($redirectUrl);
        }
        $calendarName = $googleDetails->getCalendar();

        $googleClient = $this->clientHelper->getGoogleClient();
        var_dump($googleClient->getCalendars());

        $googleClient = $this->clientHelper->createGoogleClient();

        foreach ($events->results as $event) {
            $t = $event->time/1000;
            $offset = $event->utc_offset/1000;
            echo $t + $offset;
            $time = date('Y-m-d H:i:s',$t + $offset);
            $dateTime = new \DateTime($time);
            print_r($dateTime);
            echo "<br />";
        }
    }
}