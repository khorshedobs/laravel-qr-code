<?php

namespace RSSoftBD\QrCode\Payloads;

class GeoPayload implements PayloadInterface
{
    /**
     * The prefix of the QrCode.
     *
     * @var string
     */
    protected string $prefix = 'geo:';

    /**
     * The separator between the variables.
     *
     * @var string
     */
    protected string $separator = ',';

    /**
     * The latitude.
     *
     * @var float|null
     */
    protected ?float $latitude = null;

    /**
     * The longitude.
     *
     * @var float|null
     */
    protected ?float $longitude = null;

    /**
     * Generates the Payload Object and sets all of its properties.
     *
     * @param array $arguments
     * @return void
     */
    public function create(array $arguments): void
    {
        $this->latitude = (float) $arguments[0];
        $this->longitude = (float) $arguments[1];
    }

    /**
     * Returns the correct QrCode content string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->prefix . $this->latitude . $this->separator . $this->longitude;
    }
}
