<?php

namespace GoogleCal\Form;

use \Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SelectCalendarForm extends AbstractType
{
    private $calendars;

    public function __construct(array $calendars)
    {
        $this->calendars = $calendars;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array();
        foreach ($this->calendars as $calendar) {
            $id = $calendar->id;
            $name = $calendar->summary;
            $choices = array_merge($choices, array($id => $name));
        }

        $builder->add('calendar', 'choice', array(
            'choices' => $choices
        ));
    }

    public function getName()
    {
        return 'select_calendar';
    }
}