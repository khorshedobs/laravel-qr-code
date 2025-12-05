<?php

namespace RSSoftBD\QrCode\Payloads;

class SmsPayload implements PayloadInterface
{
    /**
     * The prefix of the QrCode.
     *
     * @var string
     */
    protected string $prefix = 'sms:';

    /**
     * The separator between the variables.
     *
     * @var string
     */
    protected string $separator = ':';

    /**
     * The phone number.
     *
     * @var string|null
     */
    protected ?string $phoneNumber = null;

    /**
     * The SMS message.
     *
     * @var string|null
     */
    protected ?string $message = null;

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
        return $this->buildSmsString();
    }

    /**
     * Sets the phone number and message for a sms message.
     *
     * @param array $arguments
     */
    protected function setProperties(array $arguments): void
    {
        if (isset($arguments[0])) {
            $this->phoneNumber = $arguments[0];
        }
        if (isset($arguments[1])) {
            $this->message = $arguments[1];
        }
    }

    /**
     * Builds a SMS string.
     *
     * @return string
     */
    protected function buildSmsString(): string
    {
        $sms = $this->prefix . $this->phoneNumber;

        if (isset($this->message)) {
            $sms .= $this->separator . $this->message;
        }

        return $sms;
    }
}
