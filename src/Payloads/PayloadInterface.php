<?php

namespace RSSoftBD\QrCode\Payloads;

interface PayloadInterface
{
    /**
     * Generates the Payload Object and sets all of its properties.
     *
     * @param array $arguments
     * @return void
     */
    public function create(array $arguments): void;

    /**
     * Returns the correct QrCode content string.
     *
     * @return string
     */
    public function __toString(): string;
}
