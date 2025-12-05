<?php

namespace RSSoftBD\QrCode\Support;

use RSSoftBD\QrCode\Payloads\BitcoinPayload;
use RSSoftBD\QrCode\Payloads\CalendarEventPayload;
use RSSoftBD\QrCode\Payloads\EmailPayload;
use RSSoftBD\QrCode\Payloads\FacetimePayload;
use RSSoftBD\QrCode\Payloads\GeoPayload;
use RSSoftBD\QrCode\Payloads\MeCardPayload;
use RSSoftBD\QrCode\Payloads\PhoneNumberPayload;
use RSSoftBD\QrCode\Payloads\SmsPayload;
use RSSoftBD\QrCode\Payloads\UrlPayload;
use RSSoftBD\QrCode\Payloads\VCardPayload;
use RSSoftBD\QrCode\Payloads\WifiPayload;

class PayloadRegistry
{
    /**
     * Map of method aliases to Payload classes.
     *
     * @var array<string, string>
     */
    protected static array $payloads = [
        'bitcoin' => BitcoinPayload::class,
        'btc' => BitcoinPayload::class,
        'calendar' => CalendarEventPayload::class,
        'email' => EmailPayload::class,
        'facetime' => FacetimePayload::class,
        'geo' => GeoPayload::class,
        'mecard' => MeCardPayload::class,
        'phoneNumber' => PhoneNumberPayload::class,
        'phone' => PhoneNumberPayload::class,
        'sms' => SmsPayload::class,
        'url' => UrlPayload::class,
        'link' => UrlPayload::class,
        'vcard' => VCardPayload::class,
        'wifi' => WifiPayload::class,
    ];

    /**
     * Get the Payload class for a given method name.
     *
     * @param string $method
     * @return string|null
     */
    public static function getClass(string $method): ?string
    {
        return self::$payloads[$method] ?? null;
    }

    /**
     * Register a custom payload.
     *
     * @param string $method
     * @param string $class
     * @return void
     */
    public static function register(string $method, string $class): void
    {
        self::$payloads[$method] = $class;
    }
}
