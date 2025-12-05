<?php

namespace RSSoftBD\QrCode\Payloads;

class WifiPayload implements PayloadInterface
{
    /**
     * The prefix of the QrCode.
     *
     * @var string
     */
    protected string $prefix = 'WIFI:';

    /**
     * The separator between the variables.
     *
     * @var string
     */
    protected string $separator = ';';

    /**
     * The encryption of the network. WEP or WPA.
     *
     * @var string|null
     */
    protected ?string $encryption = null;

    /**
     * The SSID of the WiFi network.
     *
     * @var string|null
     */
    protected ?string $ssid = null;

    /**
     * The password of the network.
     *
     * @var string|null
     */
    protected ?string $password = null;

    /**
     * Whether the network is a hidden SSID or not.
     *
     * @var bool
     */
    protected bool $hidden = false;

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
        return $this->buildWifiString();
    }

    /**
     * Builds the WiFi string.
     *
     * @return string
     */
    protected function buildWifiString(): string
    {
        $wifi = $this->prefix;

        if (isset($this->encryption)) {
            $wifi .= 'T:' . $this->encryption . $this->separator;
        }
        if (isset($this->ssid)) {
            $wifi .= 'S:' . $this->ssid . $this->separator;
        }
        if (isset($this->password)) {
            $wifi .= 'P:' . $this->password . $this->separator;
        }
        if ($this->hidden) {
            $wifi .= 'H:true' . $this->separator;
        } else {
            // Optional: H:false or omit
        }

        // Wifi string usually ends with ;;
        $wifi .= $this->separator;

        return $wifi;
    }

    /**
     * Sets the WiFi properties.
     *
     * @param array $arguments
     */
    protected function setProperties(array $arguments): void
    {
        if (isset($arguments[0]) && is_array($arguments[0])) {
            $args = $arguments[0];
            if (isset($args['encryption'])) {
                $this->encryption = $args['encryption'];
            }
            if (isset($args['ssid'])) {
                $this->ssid = $args['ssid'];
            }
            if (isset($args['password'])) {
                $this->password = $args['password'];
            }
            if (isset($args['hidden'])) {
                $this->hidden = (bool) $args['hidden'];
            }
        }
    }
}
