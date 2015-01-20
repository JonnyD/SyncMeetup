<?php

namespace GoogleCal\Entity;

/**
 * @ORM\Entity
 * @ORM\Table(name="google_details")
 */
class GoogleDetails
{
    /**
     * GoogleDetails id
     *
     * @var integer
     */
    private $id;

    /**
     * GoogleDetails accessToken
     *
     * @var string
     */
    private $accessToken;

    /**
     * GoogleDetails refreshToken
     *
     * @var string
     */
    private $refreshToken;

    /**
     * GoogleDetails expires
     *
     * @var \DateTime
     */
    private $expires;

    /**
     * GoogleDetails calendar
     *
     * @var string
     */
    private $calendar;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
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

    public function getCalendar()
    {
        return $this->calendar;
    }

    public function hasExpired()
    {
        $now = new \DateTime();
        return $now > $this->expires;
    }

    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;
    }
}