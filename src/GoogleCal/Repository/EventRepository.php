<?php

namespace GoogleCal\Repository;

use GoogleCal\Entity\Event;

use Doctrine\DBAL\Connection;

/**
 * Event repository
 */
class EventRepository
{
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function find($id)
    {
        $sql = 'SELECT * FROM event WHERE id = ?';
        $eventData = $this->db->fetchAssoc($sql, array($id));
        $event = $this->buildEvent($eventData);
        return $event;
    }

    public function findEventByGoogleId($googleEventId)
    {
        $sql = 'SELECT * FROM event
                WHERE google_event_id = ?';
        $eventData = $this->db->fetchAssoc($sql, array($googleEventId));
        $event = $this->buildEvent($eventData);
        return $event;
    }

    public function findEventByMeetupId($meetupEventId)
    {
        $sql = 'SELECT * FROM event
                WHERE meetup_event_id = ?';
        $eventData = $this->db->fetchAssoc($sql, array($meetupEventId));
        $event = $this->buildEvent($eventData);
        return $event;
    }

    public function findEventByGoogleAndMeetup($googleEventId, $meetupEventId)
    {
        $sql = 'SELECT * FROM event
                WHERE google_event_id = ?
                  AND meetup_event_id = ?';
        $eventData = $this->db->fetchAssoc($sql, array($googleEventId, $meetupEventId));
        $event = $this->buildEvent($eventData);
        return $event;
    }

    public function save(Event $event)
    {
        $eventData = array(
            'id' => $event->getId(),
            'google_event_id' => $event->getGoogleEventId(),
            'meetup_event_id' => $event->getMeetupEventId()
        );

        $eventId = $event->getId();
        if ($eventId) {
            $this->db->update('event', $eventData, array('id' => $eventId));
        } else {
            $this->db->insert('event', $eventData);
            $id = $this->db->lastInsertId();
            $event->setId($id);
        }

        return $event;
    }

    public function buildEvent($eventData)
    {
        if (!$eventData) {
            return null;
        }

        $event = new Event();
        $event->setId($eventData['id']);
        $event->setGoogleEventId($eventData['google_event_id']);
        $event->setMeetupEventId($eventData['meetup_event_id']);
        return $event;
    }
}