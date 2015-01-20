<?php

namespace GoogleCal\Helper;

class DateHelper
{
    public static function createExpiryDate()
    {
        $date = new \DateTime();
        $date->add(new \DateInterval('PT3500S'));
        return $date;
    }
}