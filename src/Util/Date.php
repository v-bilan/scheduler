<?php

namespace App\Util;

class Date extends \DateTime
{
    public function getFullYear(): int
    {
        return intval($this->format('o'));
    }
    public function getWeek(): int
    {
        return intval($this->format('W'));
    }

    public function __toString(): string
    {
        return $this->format('Y-m-d H:i:s');
    }
}
