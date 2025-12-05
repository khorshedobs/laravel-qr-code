<?php

namespace RSSoftBD\QrCode\Payloads;

use BaconQrCode\Exception\InvalidArgumentException;

class EmailPayload implements PayloadInterface
{
    /**
     * The prefix of the QrCode.
     *
     * @var string
     */
    protected string $prefix = 'mailto:';

    /**
     * The email address.
     *
     * @var string|null
     */
    protected ?string $email = null;

    /**
     * The subject of the email.
     *
     * @var string|null
     */
    protected ?string $subject = null;

    /**
     * The body of an email.
     *
     * @var string|null
     */
    protected ?string $body = null;

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
        return $this->buildEmailString();
    }

    /**
     * Builds the email string.
     *
     * @return string
     */
    protected function buildEmailString(): string
    {
        $email = $this->prefix . $this->email;

        if (isset($this->subject) || isset($this->body)) {
            $data = array_filter([
                'subject' => $this->subject,
                'body' => $this->body,
            ], fn($value) => !is_null($value));

            if (!empty($data)) {
                $email .= '?' . http_build_query($data);
            }
        }

        return $email;
    }

    /**
     * Sets the objects properties.
     *
     * @param array $arguments
     */
    protected function setProperties(array $arguments): void
    {
        if (isset($arguments[0])) {
            $this->setEmail($arguments[0]);
        }
        if (isset($arguments[1])) {
            $this->subject = $arguments[1];
        }
        if (isset($arguments[2])) {
            $this->body = $arguments[2];
        }
    }

    /**
     * Sets the email property.
     *
     * @param string $email
     */
    protected function setEmail(string $email): void
    {
        if ($this->isValidEmail($email)) {
            $this->email = $email;
        }
    }

    /**
     * Ensures an email is valid.
     *
     * @param string $email
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    protected function isValidEmail(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email provided');
        }

        return true;
    }
}
