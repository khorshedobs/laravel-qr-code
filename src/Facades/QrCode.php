<?php

namespace RSSoftBD\QrCode\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \RSSoftBD\QrCode\QrGenerator setDimensions(int $pixels)
 * @method static \RSSoftBD\QrCode\QrGenerator setOutputFormat(string $format)
 * @method static \RSSoftBD\QrCode\QrGenerator setForegroundColor(int $red, int $green, int $blue, ?int $alpha = null)
 * @method static \RSSoftBD\QrCode\QrGenerator setBackgroundColor(int $red, int $green, int $blue, ?int $alpha = null)
 * @method static mixed render(string $content, ?string $filename = null)
 * @see \RSSoftBD\QrCode\QrGenerator
 */
class QrCode extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'qrcode';
    }
}
