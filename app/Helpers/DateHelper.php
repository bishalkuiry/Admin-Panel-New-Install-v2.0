<?php

namespace App\Helpers;

use App\Models\Setting;
use Carbon\Carbon;

class DateHelper
{
    /**
     * Format a date instance to the configured system format
     */
    public static function format($date, bool $includeTime = false): string
    {
        if (!$date) {
            return '-';
        }

        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        // Get configured timezone
        $timezone = Setting::get('system_timezone', config('app.timezone'));
        
        // Set timezone
        $date->setTimezone($timezone);
        
        // Get configured formats
        $dateFormat = Setting::get('date_format', 'd/m/Y');
        $timeFormat = Setting::get('time_format', '12') === '12' ? 'g:i A' : 'H:i';
        
        $format = $dateFormat;
        
        if ($includeTime) {
            $format .= ' ' . $timeFormat;
        }
        
        return $date->format($format);
    }
    
    /**
     * Format a date instance to the configured system time format only
     */
    public static function formatTime($date): string
    {
        if (!$date) {
            return '-';
        }

        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        // Get configured timezone
        $timezone = Setting::get('system_timezone', config('app.timezone'));
        
        // Set timezone
        $date->setTimezone($timezone);
        
        // Get configured time format
        $timeFormat = Setting::get('time_format', '12') === '12' ? 'g:i A' : 'H:i';
        
        return $date->format($timeFormat);
    }
    
    /**
     * Format a human readable difference (e.g. 2 hours ago)
     */
    public static function diffForHumans($date): string
    {
        if (!$date) {
            return '-';
        }

        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        return $date->diffForHumans();
    }
}
