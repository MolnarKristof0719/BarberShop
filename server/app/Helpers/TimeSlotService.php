<?php

namespace App\Helpers;

use Carbon\Carbon;

class TimeSlotService
{
    public function generateHalfHourSlots(
        string $start,
        string $end
    ): array {
        $slots = [];

        $time = Carbon::createFromTimeString($start);
        $endTime = Carbon::createFromTimeString($end);

        while ($time < $endTime) {
            $slots[] = $time->format('H:i:s');
            $time->addMinutes(30);
        }

        return $slots;
    }
}
