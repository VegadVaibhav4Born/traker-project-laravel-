<?php

use Carbon\Carbon;

if (!function_exists('formatDuration')) {
    function formatDuration($startDate) {
        $start_date = Carbon::parse($startDate);
        $current_date = Carbon::now();
        $diff = $start_date->diff($current_date);

        $duration = '';
        if ($diff->y > 0) {
            $duration .= $diff->y . ' Year' . ($diff->y > 1 ? 's' : '') . ', ';
        }
        if ($diff->m > 0) {
            $duration .= $diff->m . ' Month' . ($diff->m > 1 ? 's' : '') . ', ';
        }
        if ($diff->d > 0) {
            $duration .= $diff->d . ' Day' . ($diff->d > 1 ? 's' : '');
        }
        $duration = rtrim($duration, ', ');

        return $duration;
    }
   
}
 if (!function_exists('formatDurationInSeconds')) {
    function formatDurationInSeconds($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        // Handle formatting for cases where hours are present
        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        // Handle formatting for cases where hours are not present
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}

