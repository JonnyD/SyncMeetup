<?php

namespace GoogleCal\Entity;

/**
 * @ORM\Entity
 * @ORM\Table(name="event")
 */
class Event
{
    /**
     * User id
     *
     * @var integer
     */
    private $id;

    /**
     * Event googleEventId
     *
     * @var integer
     */
    private $googleEventId;

    /**
     * Event meetupEventId
     *
     * @var integer
     */
    private $meetupEventId;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getGoogleEventId()
    {
        return $this->googleEventId;
    }

    public function setGoogleEventId($googleEventId)
    {
        $this->googleEventId = $googleEventId;
    }

    public function getMeetupEventId()
    {
        return $this->meetupEventId;
    }

    public function setMeetupEventId($meetupEventId)
    {
        $this->meetupEventId = $meetupEventId;
    }
}