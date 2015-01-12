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

    public function authenticate($code)
    {
        $this->_parameters = array_merge($this->_parameters, array('code' => $code));
        $response = $this->access();
        return $response;
    }

    public function setAccessToken($accessToken)
    {
        $this->_parameters = array_merge($this->_parameters, array('access_token' => $accessToken));
    }

    public function getCurrentMember()
    {
        return $this->post('/2/member/:id', array('id' => 'self', 'photo' => 'thumb_link'));
    }
}