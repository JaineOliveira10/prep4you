<?php

namespace App\Helpers;

use Carbon\Carbon;
use Cmixin\BusinessDay;

class BusinessDaysHelper
{
    public static function addBusinessDays($date, $days)
    {
        BusinessDay::enable(Carbon::class);
        
        $date = Carbon::parse($date);
        
        // Configurar feriados nacionais do Brasil
        $date->setHolidaysRegion('BR');
        
        return $date->addBusinessDays($days);
    }
}