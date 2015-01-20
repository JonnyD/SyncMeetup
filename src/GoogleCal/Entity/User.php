<?php

namespace GoogleCal\Entity;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User
{
    /**
     * User id
     *
     * @var integer
     */
    private $id;

    /**
     * User meetupDetails
     *
     * @var MeetupDetails
     */
    private $meetupDetails;

    /**
     * User googleDetails
     *
     * @var GoogleDetails
     */
    private $googleDetails;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getMeetupDetails()
    {
        return $this->meetupDetails;
    }

    public function setMeetupDetails(MeetupDetails $meetupDetails = null)
    {
        $this->meetupDetails = $meetupDetails;
    }

    public function getGoogleDetails()
    {
        return $this->googleDetails;
    }

    public function setGoogleDetails(GoogleDetails $googleDetails = null)
    {
        $this->googleDetails = $googleDetails;
    }

    public function setCalendar($calendar)
    {
        $this->googleDetails->setCalendar($calendar);
    }
}