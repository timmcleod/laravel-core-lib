<?php namespace TimMcLeod\LaravelCoreLib\Calendar;

use Illuminate\Support\Collection;

class VCalendar
{
    /*
    |--------------------------------------------------------------------------
    | Excerpt from: https://tools.ietf.org/html/rfc2446#section-3.2
    |
    +================+==================================================+
    | Method         |  Description                                     |
    |================+==================================================|
    | PUBLISH        | Post notification of an event. Used primarily as |
    |                | a method of advertising the existence of an      |
    |                | event.                                           |
    |                |                                                  |
    | REQUEST        | Make a request for an event. This is an explicit |
    |                | invitation to one or more "Attendees". Event     |
    |                | Requests are also used to update or change an    |
    |                | existing event. Clients that cannot handle       |
    |                | REQUEST may degrade the event to view it as an   |
    |                | PUBLISH.                                         |
    |                |                                                  |
    | REPLY          | Reply to an event request. Clients may set their |
    |                | status ("partstat") to ACCEPTED, DECLINED,       |
    |                | TENTATIVE, or DELEGATED.                         |
    |                |                                                  |
    | ADD            | Add one or more instances to an existing event.  |
    |                |                                                  |
    | CANCEL         | Cancel one or more instances of an existing      |
    |                | event.                                           |
    |                |                                                  |
    | REFRESH        | A request is sent to an "Organizer" by an        |
    |                | "Attendee" asking for the latest version of an   |
    |                | event to be resent to the requester.             |
    |                |                                                  |
    | COUNTER        | Counter a REQUEST with an alternative proposal,  |
    |                | Sent by an "Attendee" to the "Organizer".        |
    |                |                                                  |
    | DECLINECOUNTER | Decline a counter proposal. Sent to an           |
    |                | "Attendee" by the "Organizer".                   |
    +================+==================================================+
    |
    */

    const METHOD_PUBLISH = 'PUBLISH';
    const METHOD_REQUEST = 'REQUEST';
    const METHOD_REPLY = 'REPLY';
    const METHOD_ADD = 'ADD';
    const METHOD_CANCEL = 'CANCEL';
    const METHOD_DECLINECOUNTER = 'DECLINECOUNTER';

    /** @var Collection */
    protected $vEvents;

    /** @var string */
    protected $method = self::METHOD_PUBLISH;

    /** @var string */
    protected $version = '2.0';

    /** @var string */
    protected $prodId = '';

    /**
     * VCalendar constructor.
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
        $this->vEvents = new Collection();
    }

    /**
     * @param VEvent $vEvent
     */
    public function addVEvent(VEvent $vEvent)
    {
        $this->vEvents->push($vEvent);
    }

    /**
     * @return Collection
     */
    public function vEvents()
    {
        return $this->vEvents;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @param $prodId
     */
    public function setProdId($prodId)
    {
        $this->prodId = $prodId;
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
            $this->beginVCalendar() .
            $this->version() .
            $this->method() .
            $this->prodId() .
            $this->vEventsToString() .
            $this->endVCalendar();
    }

    /**
     * @return string
     */
    public function beginVCalendar()
    {
        return 'BEGIN:VCALENDAR' . PHP_EOL;
    }

    /**
     * @return string
     */
    public function version()
    {
        return 'VERSION:' . $this->version . PHP_EOL;
    }

    /**
     * @return string
     */
    public function method()
    {
        return 'METHOD:' . $this->method . PHP_EOL;
    }

    /**
     * Returns the $prodId property if it has been set. Otherwise, it returns the
     * default "calendar.product_id" value from the config file, if it was set.
     *
     * @return string
     */
    public function prodId()
    {
        $configProdId = config('calendar.product_id', '-//timmcleod//calendar v1.0//EN');

        $prodId = empty($this->prodId) ? $configProdId : $this->prodId;

        return 'PRODID:' . $prodId . PHP_EOL;
    }

    /**
     * @return string
     */
    public function vEventsToString()
    {
        return implode(PHP_EOL, $this->vEvents->all()) . PHP_EOL;
    }

    /**
     * @return string
     */
    public function endVCalendar()
    {
        return 'END:VCALENDAR';
    }
}