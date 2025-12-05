<?php

namespace RSSoftBD\QrCode\Payloads;

class UrlPayload implements PayloadInterface
{
    /**
     * The URL.
     *
     * @var string|null
     */
    protected ?string $url = null;

    /**
     * Generates the Payload Object and sets all of its properties.
     *
     * @param array $arguments
     * @return void
     */
    public function create(array $arguments): void
    {
        $this->url = $arguments[0];
    }

    /**
     * Returns the correct QrCode content string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->url;
    }
}
