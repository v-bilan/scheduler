<?php

namespace App\Entity;

class DateWithYearAndWeek extends \DateTime
{
    public function getFullYear(): int
    {
        return intval($this->format('o'));
    }
    public function getWeek(): int
    {
        return intval($this->format('W'));
    }
}