<?php

namespace RSSoftBD\QrCode\Payloads;

class MeCardPayload implements PayloadInterface
{
    /**
     * The prefix of the QrCode.
     *
     * @var string
     */
    protected string $prefix = 'MECARD:';

    /**
     * The separator between the variables.
     *
     * @var string
     */
    protected string $separator = ';';

    /**
     * The name of the contact.
     *
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * The reading sound of the name.
     *
     * @var string|null
     */
    protected ?string $reading = null;

    /**
     * The phone number(s).
     *
     * @var array
     */
    protected array $phoneNumbers = [];

    /**
     * The video call number(s).
     *
     * @var array
     */
    protected array $videoCallNumbers = [];

    /**
     * The email(s).
     *
     * @var array
     */
    protected array $emails = [];

    /**
     * The memo/note.
     *
     * @var string|null
     */
    protected ?string $note = null;

    /**
     * The birthday (YYYYMMDD).
     *
     * @var string|null
     */
    protected ?string $birthday = null;

    /**
     * The address.
     *
     * @var string|null
     */
    protected ?string $address = null;

    /**
     * The URL.
     *
     * @var string|null
     */
    protected ?string $url = null;

    /**
     * The nickname.
     *
     * @var string|null
     */
    protected ?string $nickname = null;

    /**
     * Generates the Payload Object and sets all of its properties.
     *
     * @param array $arguments
     * @return void
     */
    public function create(array $arguments): void
    {
        $this->setProperties($arguments);
    }

    /**
     * Returns the correct QrCode content string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->buildMeCardString();
    }

    /**
     * Sets the MeCard properties.
     *
     * @param array $arguments
     */
    protected function setProperties(array $arguments): void
    {
        $this->name = $arguments[0];
        $this->phoneNumbers = (array) ($arguments[1] ?? []);
        $this->emails = (array) ($arguments[2] ?? []);

        if (isset($arguments[3]) && is_array($arguments[3])) {
            $options = $arguments[3];
            $this->reading = $options['reading'] ?? null;
            $this->note = $options['note'] ?? null;
            $this->birthday = $options['birthday'] ?? null;
            $this->address = $options['address'] ?? null;
            $this->url = $options['url'] ?? null;
            $this->nickname = $options['nickname'] ?? null;
            $this->videoCallNumbers = (array) ($options['videoCall'] ?? []);
        }
    }

    /**
     * Builds the MeCard string.
     *
     * @return string
     */
    protected function buildMeCardString(): string
    {
        $meCard = $this->prefix;

        if ($this->name) {
            $meCard .= 'N:' . $this->name . $this->separator;
        }
        if ($this->reading) {
            $meCard .= 'SOUND:' . $this->reading . $this->separator;
        }
        foreach ($this->phoneNumbers as $phone) {
            $meCard .= 'TEL:' . $phone . $this->separator;
        }
        foreach ($this->videoCallNumbers as $video) {
            $meCard .= 'TEL-AV:' . $video . $this->separator;
        }
        foreach ($this->emails as $email) {
            $meCard .= 'EMAIL:' . $email . $this->separator;
        }
        if ($this->note) {
            $meCard .= 'NOTE:' . $this->note . $this->separator;
        }
        if ($this->birthday) {
            $meCard .= 'BDAY:' . $this->birthday . $this->separator;
        }
        if ($this->address) {
            $meCard .= 'ADR:' . $this->address . $this->separator;
        }
        if ($this->url) {
            $meCard .= 'URL:' . $this->url . $this->separator;
        }
        if ($this->nickname) {
            $meCard .= 'NICKNAME:' . $this->nickname . $this->separator;
        }

        $meCard .= $this->separator;

        return $meCard;
    }
}
