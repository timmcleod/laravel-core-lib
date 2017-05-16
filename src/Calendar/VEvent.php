<?php namespace TimMcLeod\LaravelCoreLib\Calendar;

use Carbon\Carbon;
use TimMcLeod\LaravelCoreLib\Calendar\IcsFormatter as Format;

class VEvent
{
    /*
    |--------------------------------------------------------------------------
    | Excerpt from: http://www.ietf.org/rfc/rfc2445.txt (4.8.1.11 Status)
    |
    | In a group scheduled calendar, the property is used by the "Organizer"
    | to provide an event confirmation to the "Attendees". For example, in
    | a "VEVENT" calendar component, the "Organizer" can indicate that a
    | meeting status is one of these: tentative, confirmed or cancelled.
    |
    */

    const STATUS_TENTATIVE = 'TENTATIVE';
    const STATUS_CONFIRMED = 'CONFIRMED';
    const STATUS_CANCELLED = 'CANCELLED';

    /** @var string */
    protected $uid;

    /** @var int */
    protected $sequence;

    /** @var string */
    protected $summary = '';

    /** @var string */
    protected $description = '';

    /** @var Carbon */
    protected $lastModified;

    /** @var Carbon */
    protected $dtStamp;

    /** @var Carbon */
    protected $dtStart;

    /** @var Carbon */
    protected $dtEnd;

    /** @var bool */
    protected $allDay = true;

    /** @var string */
    protected $location = '';

    /** @var string */
    protected $url = '';

    /** @var string */
    protected $status = self::STATUS_CONFIRMED;

    /**
     * VEvent constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Initializes fields with default values.
     */
    public function init()
    {
        $this->uid = uniqid();
        $this->sequence = time();
        $this->dtStart = Carbon::now();
        $this->dtEnd = Carbon::now();
        $this->lastModified = Carbon::now();
        $this->dtStamp = Carbon::now();
    }

    /**
     * @return string
     */
    public function title()
    {
        return $this->summary();
    }

    /**
     * @return string
     */
    public function summary()
    {
        if (empty($this->summary)) return '';

        return 'SUMMARY:' . Format::escape($this->summary) . PHP_EOL;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->setSummary($title);
    }

    /**
     * @param string $summary
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param string $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * @param Carbon $dtStart
     */
    public function setDtStart(Carbon $dtStart)
    {
        $this->dtStart = $dtStart;
    }

    /**
     * @param Carbon $dtEnd
     */
    public function setDtEnd(Carbon $dtEnd)
    {
        $this->dtEnd = $dtEnd;
    }

    /**
     * @param bool $allDay
     */
    public function allDayEvent($allDay)
    {
        $this->allDay = $allDay;
    }

    /**
     * @param int $sequence
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    /**
     * @param Carbon $lastModified
     */
    public function setLastModified(Carbon $lastModified)
    {
        $this->lastModified = $lastModified;
    }

    /**
     * @param Carbon $dtStamp
     */
    public function setDtStamp(Carbon $dtStamp)
    {
        $this->dtStamp = $dtStamp;
    }

    /**
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Convert string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toIcs();
    }

    /**
     * Convert string representation.
     *
     * @return string
     */
    public function toIcs()
    {
        return
            $this->beginVEvent() .
            $this->summary() .
            $this->description() .
            $this->uid() .
            $this->status() .
            $this->dtStart() .
            $this->dtEnd() .
            $this->dtStamp() .
            $this->sequence() .
            $this->lastModified() .
            $this->url() .
            $this->location() .
            $this->endVEvent();
    }

    /**
     * @return string
     */
    public function beginVEvent()
    {
        return 'BEGIN:VEVENT' . PHP_EOL;
    }

    /**
     * @return string
     */
    public function description()
    {
        if (empty($this->description)) return '';

        return 'DESCRIPTION:' . Format::escape($this->description) . PHP_EOL;
    }

    /**
     * @return string
     */
    public function uid()
    {
        if (empty($this->uid)) return '';

        return 'UID:' . $this->uid . PHP_EOL;
    }

    /**
     * @return string
     */
    public function status()
    {
        if (empty($this->status)) return '';

        return 'STATUS:' . $this->status . PHP_EOL;
    }

    /**
     * @return string
     */
    public function dtStart()
    {
        if (empty($this->dtStart)) return '';

        return Format::dateForProperty('DTSTART', $this->dtStart, $this->includeTimeInStartEndDates()) . PHP_EOL;
    }

    /**
     * @return bool
     */
    protected function includeTimeInStartEndDates()
    {
        return !$this->allDay;
    }

    /**
     * @return string
     */
    public function dtEnd()
    {
        if (empty($this->dtEnd)) return '';

        return Format::dateForProperty('DTEND', $this->dtEnd, $this->includeTimeInStartEndDates()) . PHP_EOL;
    }

    /**
     * @return string
     */
    public function dtStamp()
    {
        if (empty($this->dtStamp)) return '';

        return 'DTSTAMP:' . $this->dtStamp->timezone('UTC')->format('Ymd\THis\Z') . PHP_EOL;
    }

    /**
     * @return string
     */
    public function sequence()
    {
        if (empty($this->sequence)) return '';

        return 'SEQUENCE:' . $this->sequence . PHP_EOL;
    }

    /**
     * @return string
     */
    public function lastModified()
    {
        if (empty($this->lastModified)) return '';

        return Format::dateForProperty('LAST-MODIFIED', $this->lastModified, true) . PHP_EOL;
    }

    /**
     * @return string
     */
    public function url()
    {
        if (empty($this->url)) return '';

        return 'URL:' . Format::escape($this->url) . PHP_EOL;
    }

    /**
     * @return string
     */
    public function location()
    {
        if (empty($this->location)) return '';

        return 'LOCATION:' . Format::escape($this->location) . PHP_EOL;
    }

    /**
     * @return string
     */
    public function endVEvent()
    {
        return 'END:VEVENT';
    }
}