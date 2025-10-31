<?php

namespace App\Helpers;

class ZoneTimeHelper
{
    public static function getTimes($zone)
    {
        if (!$zone) return [null, null];

        $zone = strtolower(trim($zone));

        // Daftar waktu per zona
        $zones = [
            '1a' => ['07:30', '08:20'],
            '1b' => ['08:20', '09:10'],
            '1c' => ['09:10', '10:00'],
            '2a' => ['10:00', '10:50'],
            '2b' => ['10:50', '11:40'],
            '2c' => ['11:40', '12:30'],
            '3a' => ['12:30', '13:20'],
            '3b' => ['13:20', '14:10'],
            '3c' => ['14:10', '15:00'],
        ];

        // Pisahkan range misalnya "1a-2b"
        $parts = explode('-', $zone);
        $startZone = $parts[0];
        $endZone = $parts[1] ?? $parts[0];

        $start = $zones[$startZone][0] ?? null;
        $end = $zones[$endZone][1] ?? null;

        return [$start, $end];
    }
}
