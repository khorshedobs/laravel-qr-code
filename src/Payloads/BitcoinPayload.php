<?php

namespace RSSoftBD\QrCode\Payloads;

class BitcoinPayload implements PayloadInterface
{
    /**
     * The prefix of the QrCode.
     *
     * @var string
     */
    protected string $prefix = 'bitcoin:';

    /**
     * The BitCoin address.
     *
     * @var string|null
     */
    protected ?string $address = null;

    /**
     * The amount to send.
     *
     * @var float|null
     */
    protected ?float $amount = null;

    /**
     * The BitCoin transaction label.
     *
     * @var string|null
     */
    protected ?string $label = null;

    /**
     * The BitCoin message to send.
     *
     * @var string|null
     */
    protected ?string $message = null;

    /**
     * The BitCoin return URL.
     *
     * @var string|null
     */
    protected ?string $returnAddress = null;

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
        return $this->buildBitcoinString();
    }

    /**
     * Sets the BitCoin arguments.
     *
     * @param array $arguments
     */
    protected function setProperties(array $arguments): void
    {
        if (isset($arguments[0])) {
            $this->address = $arguments[0];
        }

        if (isset($arguments[1])) {
            $this->amount = (float) $arguments[1];
        }

        if (isset($arguments[2]) && is_array($arguments[2])) {
            $this->setOptions($arguments[2]);
        }
    }

    /**
     * Sets the optional BitCoin options.
     *
     * @param array $options
     */
    protected function setOptions(array $options): void
    {
        if (isset($options['label'])) {
            $this->label = $options['label'];
        }

        if (isset($options['message'])) {
            $this->message = $options['message'];
        }

        if (isset($options['returnAddress'])) {
            $this->returnAddress = $options['returnAddress'];
        }
    }

    /**
     * Builds a BitCoin string.
     *
     * @return string
     */
    protected function buildBitcoinString(): string
    {
        $query = http_build_query(array_filter([
            'amount' => $this->amount,
            'label' => $this->label,
            'message' => $this->message,
            'r' => $this->returnAddress,
        ], fn($value) => !is_null($value)));

        $btc = $this->prefix . $this->address;

        if (!empty($query)) {
            $btc .= '?' . $query;
        }

        return $btc;
    }
}
