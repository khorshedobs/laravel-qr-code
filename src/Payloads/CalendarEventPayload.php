<?php

namespace RSSoftBD\QrCode\Payloads;

class CalendarEventPayload implements PayloadInterface
{
    /**
     * The prefix of the QrCode.
     *
     * @var string
     */
    protected string $prefix = "BEGIN:VEVENT\n";

    /**
     * The suffix of the QrCode.
     *
     * @var string
     */
    protected string $suffix = "END:VEVENT";

    /**
     * The event summary/title.
     *
     * @var string|null
     */
    protected ?string $summary = null;

    /**
     * The event start date (Ymd\THis\Z).
     *
     * @var string|null
     */
    protected ?string $dtStart = null;

    /**
     * The event end date (Ymd\THis\Z).
     *
     * @var string|null
     */
    protected ?string $dtEnd = null;

    /**
     * The event location.
     *
     * @var string|null
     */
    protected ?string $location = null;

    /**
     * The event description.
     *
     * @var string|null
     */
    protected ?string $description = null;

    /**
     * Generates the Payload Object and sets all of its properties.
     *
     * @param array $arguments
     * @return void
     */
    public function create(array $arguments): void
    {
        $this->summary = $arguments[0];
        $this->dtStart = $arguments[1];
        $this->dtEnd = $arguments[2];

        if (isset($arguments[3]) && is_array($arguments[3])) {
            $this->location = $arguments[3]['location'] ?? null;
            $this->description = $arguments[3]['description'] ?? null;
        }
    }

    /**
     * Returns the correct QrCode content string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->buildCalendarString();
    }

    /**
     * Builds the Calendar string.
     *
     * @return string
     */
    protected function buildCalendarString(): string
    {
        $event = $this->prefix;
        $event .= "SUMMARY:" . $this->summary . "\n";
        $event .= "DTSTART:" . $this->dtStart . "\n";
        $event .= "DTEND:" . $this->dtEnd . "\n";

        if ($this->location) {
            $event .= "LOCATION:" . $this->location . "\n";
        }
        if ($this->description) {
            $event .= "DESCRIPTION:" . $this->description . "\n";
        }

        $event .= $this->suffix;

        return $event;
    }
}
