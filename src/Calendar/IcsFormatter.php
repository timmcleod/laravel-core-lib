<?php namespace TimMcLeod\LaravelCoreLib\Calendar;

use Carbon\Carbon;

class IcsFormatter
{
    /**
     * @param string $string
     * @return string
     */
    public static function escape($string)
    {
        // Escape single backslashes
        $string = str_replace("\\", "\\\\", $string);

        // Escape newlines
        $string = str_replace("\r\n", "\\n", $string);
        $string = str_replace(PHP_EOL, "\\n", $string);

        return $string;
    }

    /**
     * Formats the date for the given property.
     *
     * Example (including time):
     * DTEND:19960401T235959Z
     *
     * Example (date only, excludes time):
     * DTEND;VALUE=DATE:19980704
     *
     * @param string $property
     * @param Carbon $dt
     * @param bool   $includeTime
     * @return string
     */
    public static function dateForProperty($property, Carbon $dt, $includeTime = false)
    {
        $dt->timezone('UTC');

        if ($includeTime)
        {
            return "$property:" . $dt->format('Ymd\THis\Z');
        }
        else
        {
            return "$property;VALUE=DATE:" . $dt->format('Ymd');
        }
    }
}