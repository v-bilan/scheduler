<?php

namespace App\Services;

use App\Util\Date;

class DateManager
{
    public function getDate($year, $week): Date
    {
        $date = new Date();
        $year = $year ?: date_format($date, 'o');
        $week = $week ?: date_format($date, 'W');
        $date->setISODate($year, $week);
        $date->setTime(0, 0, 0);
        return $date;
    }

    public function getMeetingDateData($date)
    {
        return [date_format($date, 'o'), date_format($date, 'W')];
    }
}
