<?php

namespace GoogleCal\Plugin;

class MeetupClient extends \Meetup
{
    public function __construct(array $parameters = array())
    {
        parent::__construct($parameters);
    }

    public function createAuthUrl()
    {
        $parameters = $this->_parameters;
        unset($parameters['client_secret']);

        $location = $this::AUTHORIZE . '?' . http_build_query(
                array_merge($parameters, array('response_type' => 'code')));

        return $location;
    }

    public function refresh($refreshToken)
    {
        return parent::refresh(array('refresh_token' => $refreshToken));
    }

    public function authenticate($code)
    {
        $this->_parameters = array_merge($this->_parameters, array('code' => $code));
        $response = $this->access();
        $this->setAccessToken($response->access_token);
        $this->setRefreshToken($response->refresh_token);
        return $response;
    }

    public function setAccessToken($accessToken)
    {
        $this->_parameters = array_merge($this->_parameters, array('access_token' => $accessToken));
    }

    public function setRefreshToken($refreshToken)
    {
        $this->_parameters = array_merge($this->_parameters, array('refresh_token' => $refreshToken));
    }

    public function getCurrentMember()
    {
        return $this->post('/2/member/:id', array('id' => 'self', 'photo' => 'thumb_link'));
    }
}