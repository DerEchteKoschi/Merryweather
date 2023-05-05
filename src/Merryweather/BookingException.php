<?php

namespace App\Merryweather;

class BookingException extends \Exception
{
    public const CRITICAL = 1;
    public const WARNING = 0;

    public static function alreadyBooked(): self
    {
        return new self('slot_already_booked');
    }

    public static function failed(): self
    {
        return new self('booking_failed');
    }

    public static function notBookable(): self
    {
        return new self('slot_not_bookable');
    }

    public static function slotNotFound(): self
    {
        return new self('slot_not_found', self::CRITICAL);
    }

    public static function slotNotYours(): self
    {
        return new self('slot_not_yours');
    }
}
