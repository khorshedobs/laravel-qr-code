<?php

namespace RSSoftBD\QrCode\Payloads;

class VCardPayload implements PayloadInterface
{
    /**
     * The prefix of the QrCode.
     *
     * @var string
     */
    protected string $prefix = "BEGIN:VCARD\nVERSION:3.0\n";

    /**
     * The suffix of the QrCode.
     *
     * @var string
     */
    protected string $suffix = "END:VCARD";

    /**
     * The contact properties.
     *
     * @var array
     */
    protected array $properties = [];

    /**
     * Generates the Payload Object and sets all of its properties.
     *
     * @param array $arguments
     * @return void
     */
    public function create(array $arguments): void
    {
        // Argument 0: Name (FN)
        // Argument 1: Properties array

        if (isset($arguments[0])) {
            $this->properties['FN'] = $arguments[0];
            $this->properties['N'] = $arguments[0]; // Simple mapping, ideally split name
        }

        if (isset($arguments[1]) && is_array($arguments[1])) {
            foreach ($arguments[1] as $key => $value) {
                $this->properties[strtoupper($key)] = $value;
            }
        }
    }

    /**
     * Returns the correct QrCode content string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->buildVCardString();
    }

    /**
     * Builds the VCard string.
     *
     * @return string
     */
    protected function buildVCardString(): string
    {
        $vCard = $this->prefix;

        foreach ($this->properties as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $vCard .= $key . ':' . $v . "\n";
                }
            } else {
                $vCard .= $key . ':' . $value . "\n";
            }
        }

        $vCard .= $this->suffix;

        return $vCard;
    }
}
