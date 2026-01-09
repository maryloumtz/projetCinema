<?php

namespace App\Service;

class MovieClientService
{
    /**
     * Convertit une durée en minutes en une chaîne formatée (heures et minutes)
     * 
     * @param int $minutes La durée en minutes
     * @return string La durée formatée (ex: "2h", "1h30", "45m")
     */
    public function formatDuration(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0m';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours === 0) {
            return $remainingMinutes . 'm';
        }

        if ($remainingMinutes === 0) {
            return $hours . 'h';
        }

        return $hours . 'h' . $remainingMinutes;
    }
}
