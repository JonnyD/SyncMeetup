<?php

namespace GoogleCal\Service;

use GoogleCal\Entity\Event;
use GoogleCal\Repository\EventRepository;
use Silex\Application;

class EventService
{
    private $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function getEvent($id)
    {
        return $this->eventRepository->find($id);
    }

    public function getEventByGoogleAndMeetup($googleEventId, $meetupEventId)
    {
        return $this->eventRepository->findEventByGoogleAndMeetup($googleEventId, $meetupEventId);
    }

    public function getEventByMeetupId($meetupEventId)
    {
        return $this->eventRepository->findEventByMeetupId($meetupEventId);
    }

    public function save(Event $event)
    {
        $this->eventRepository->save($event);
    }
}