<?php

namespace GoogleCal\Entity;

/**
 * @ORM\Entity
 * @ORM\Table(name="meetup_details")
 */
class MeetupDetails
{
    /**
     * MeetupDetails id
     *
     * @var integer
     */
    private $id;

    /**
     * MeetupDetails meetupId
     *
     * @var integer
     */
    private $meetupId;

    /**
     * MeetupDetails name
     *
     * @var string
     */
    private $name;

    /**
     * MeetupDetails thumbnail
     *
     * @var string
     */
    private $thumbnail;

    /**
     * MeetupDetails accessToken
     *
     * @var string
     */
    private $accessToken;

    /**
     * MeetupDetails refreshToken
     *
     * @var string
     */
    private $refreshToken;

    /**
     * MeetupDetils expires
     *
     * @var \DateTime
     */
    private $expires;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getMeetupId()
    {
        return $this->meetupId;
    }

    public function setMeetupId($meetupId)
    {
        $this->meetupId = $meetupId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    public function getExpires()
    {
        return $this->expires;
    }

    public function setExpires(\DateTime $expires)
    {
        $this->expires = $expires;
    }

    public function hasExpired()
    {
        $now = new \DateTime();
        return $now > $this->expires;
    }
}