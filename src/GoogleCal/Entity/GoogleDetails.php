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
}