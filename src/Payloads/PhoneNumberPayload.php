<?php

namespace RSSoftBD\QrCode\Payloads;

class PhoneNumberPayload implements PayloadInterface
{
    /**
     * The prefix of the QrCode.
     *
     * @var string
     */
    protected string $prefix = 'tel:';

    /**
     * The phone number.
     *
     * @var string|null
     */
    protected ?string $phoneNumber = null;

    /**
     * Generates the Payload Object and sets all of its properties.
     *
     * @param array $arguments
     * @return void
     */
    public function create(array $arguments): void
    {
        $this->phoneNumber = $arguments[0];
    }

    /**
     * Returns the correct QrCode content string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->prefix . $this->phoneNumber;
    }
}
