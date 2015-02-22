<?php

namespace GoogleCal\Controller;

use GoogleCal\Entity\Event;
use GoogleCal\Form\SelectCalendarForm;
use GoogleCal\Service\EventService;
use GoogleCal\Service\GoogleService;
use GoogleCal\Service\MeetupService;
use GoogleCal\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class CalendarController
{
    private $googleService;
    private $userService;
    private $meetupService;
    private $eventService;
    private $twig;
    private $session;
    private $formFactory;
    private $urlGenerator;

    public function __construct(GoogleService $googleService,
                                UserService $userService,
                                MeetupService $meetupService,
                                EventService $eventService,
                                $twig,
                                $session,
                                $formFactory,
                                $urlGenerator)
    {
        $this->googleService = $googleService;
        $this->userService = $userService;
        $this->meetupService = $meetupService;
        $this->eventService = $eventService;
        $this->twig = $twig;
        $this->session = $session;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
    }

    public function selectAction(Request $request)
    {
        $user = $this->userService->getLoggedInUser();
        $client = $this->googleService->getClientForUser($user);
        $selectCalendarForm = new SelectCalendarForm($client->getCalendars());
        $form = $this->formFactory->createBuilder($selectCalendarForm, array())->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            var_dump($request);
            $data = $form->getData();
            $calendar = $data["calendar"];

            $user = $this->userService->getLoggedInUser();
            $user->setCalendar($calendar);
            $this->userService->save($user);
        }

        return $this->twig->render('selectCalendar.twig', array(
            'form' => $form->createView()
        ));
    }

    public function syncAction()
    {
        $user = $this->userService->getLoggedInUser();

        $meetupClient = $this->meetupService->getClientByUser($user);
        $events = $meetupClient->getEvents(array('member_id' => 'self'));

        $googleDetails = $user->getGoogleDetails();
        $calendarName = $googleDetails->getCalendar();

        $googleClient = $this->googleService->getClientForUser($user);
        $calendarService = new \Google_Service_Calendar($googleClient);

        foreach ($events->results as $event) {
            $t = $event->time/1000;
            $offset = $event->utc_offset/1000;
            $time = date('Y-m-d H:i:s',$t + $offset);

            $startDateTime = new \DateTime($time, new \DateTimeZone("UTC"));
            $startDateTime->setTimezone(new \DateTimeZone("Europe/Dublin"));
            $seconds = 10800;
            if (isset($event->duration)) {
                $duration = $event->duration;
                $seconds = $duration / 1000;
            }
            $interval = new \DateInterval('PT'.$seconds.'S');

            $endDateTime = clone $startDateTime;
            $endDateTime->add($interval);

            $name = $event->name;
            $description = "";
            if (isset($event->description)) {
                $description = $event->description;
            }
            $location = "";
            if (isset($event->venue)) {
                $location = $event->venue->name;
            }

            $savedEvent = $this->eventService->getEventByMeetupId($event->id);
            if (!$savedEvent) {
                $googleEvent = new \Google_Service_Calendar_Event();
                $googleEvent->setSummary($name);
                $googleEvent->setLocation($location);
                $googleEvent->setDescription($description);

                $start = new \Google_Service_Calendar_EventDateTime();
                $start->setTimeZone('Europe/Dublin');
                $start->setDateTime($startDateTime->format('Y-m-d\TH:i:s\Z'));
                $googleEvent->setStart($start);

                $end = new \Google_Service_Calendar_EventDateTime();
                $end->setTimeZone('Europe/Dublin');
                $end->setDateTime($endDateTime->format('Y-m-d\TH:i:s\Z'));
                $googleEvent->setEnd($end);

                echo "Inserting event: " . $name . " from " .
                    $startDateTime->format('Y-m-d\TH:i:s\Z') . " to " . $endDateTime->format('Y-m-d\TH:i:s\Z') . "<br/>";
                $createdEvent = $calendarService->events->insert($calendarName, $googleEvent);

                $saveEvent = new Event();
                $saveEvent->setGoogleEventId($createdEvent->id);
                $saveEvent->setMeetupEventId($event->id);
                $this->eventService->save($saveEvent);
            } else {
                $googleEvent = $calendarService->events->get($calendarName, $savedEvent->getGoogleEventId());

                $isDirty = false;
                if ($name != $googleEvent->getSummary()) {
                    $googleEvent->setSummary($name);
                    $isDirty = true;
                }
                if ($location != $googleEvent->getLocation()) {
                    $googleEvent->setLocation($location);
                    $isDirty = true;
                }
                if ($description != $googleEvent->getDescription()) {
                    $googleEvent->setDescription($description);
                    $isDirty = true;
                }
                if ($startDateTime->format('Y-m-d\TH:i:s\Z') != $googleEvent->getStart()->getDateTime()) {
                    $googleEvent->getStart()->setDateTime($startDateTime->format('Y-m-d\TH:i:s\Z'));
                    $isDirty = true;
                }
                if ($endDateTime->format('Y-m-d\TH:i:s\Z') != $googleEvent->getEnd()->getDateTime()) {
                    $googleEvent->getEnd()->setDateTime($endDateTime->format('Y-m-d\TH:i:s\Z'));
                    $isDirty = true;
                }

                if ($isDirty) {
                    echo "Updating event: " . $name . " from " .
                        $startDateTime->format('Y-m-d\TH:i:s\Z') . " to " . $endDateTime->format('Y-m-d\TH:i:s\Z') . "<br />";
                    $calendarService->events->update($calendarName, $googleEvent->getId(), $googleEvent);
                }
            }
        }
    }
}