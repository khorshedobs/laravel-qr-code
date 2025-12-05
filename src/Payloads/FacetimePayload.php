<?php

namespace RSSoftBD\QrCode\Payloads;

class FacetimePayload implements PayloadInterface
{
    /**
     * The prefix of the QrCode.
     *
     * @var string
     */
    protected string $prefix = 'facetime:';

    /**
     * The contact (phone or email).
     *
     * @var string|null
     */
    protected ?string $contact = null;

    /**
     * Whether it is an audio call.
     *
     * @var bool
     */
    protected bool $audio = false;

    /**
     * Generates the Payload Object and sets all of its properties.
     *
     * @param array $arguments
     * @return void
     */
    public function create(array $arguments): void
    {
        $this->contact = $arguments[0];
        if (isset($arguments[1])) {
            $this->audio = (bool) $arguments[1];
        }
    }

    /**
     * Returns the correct QrCode content string.
     *
     * @return string
     */
    public function __toString(): string
    {
        $prefix = $this->audio ? 'facetime-audio:' : $this->prefix;
        return $prefix . $this->contact;
    }
}
