<?php
namespace App\Services;

class DateManager
{
    /**
     * @var \Twig_Extensions_Extension_Date
     */
    private $twig_date;
    /**
     * @var \Twig_Environment
     */
    private $environment;

    public function __construct(\Twig_Extensions_Extension_Date $twig_date, \Twig_Environment $environment)
    {
        $this->twig_date = $twig_date;
        $this->environment = $environment;
    }

    /**
     * Returns a string which contains how much time passed for a given date
     * @param \DateTimeInterface $date
     * @return string
     */
    public function timeAgo(\DateTimeInterface $date)
    {
        return $this->twig_date->diff(
            $this->environment,
            $date
        );
    }

}