<?php

namespace GoogleCal\Plugin;

class GoogleClient extends \Google_Client
{
    private $calendarService;

    public function __construct($config = null)
    {
        parent::__construct($config);
    }

    public function getCalendars()
    {
        return $this->calendarService->calendarList->listCalendarList()->getItems();
    }

    public function activateCalendarService()
    {
        $this->calendarService = new \Google_Service_Calendar($this);
    }
}