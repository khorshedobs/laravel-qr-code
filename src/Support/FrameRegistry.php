<?php

namespace RSSoftBD\QrCode\Support;

class FrameRegistry
{
    public static function getFrame(string $style, string $qrCode, int $size)
    {
        $method = 'get' . ucfirst($style) . 'Frame';

        if (method_exists(self::class, $method)) {
            return self::$method($qrCode, $size);
        }

        return $qrCode;
    }

    protected static function getPhoneFrame(string $qrCode, int $size)
    {
        // Simple Phone Frame SVG wrapper
        $frameSize = $size * 1.2;
        $offset = ($frameSize - $size) / 2;

        return sprintf(
            '<svg width="%1$d" height="%1$d" viewBox="0 0 %1$d %1$d" xmlns="http://www.w3.org/2000/svg">
                <rect x="0" y="0" width="%1$d" height="%1$d" rx="30" fill="#333"/>
                <rect x="10" y="10" width="%2$d" height="%2$d" rx="20" fill="white"/>
                <svg x="%3$d" y="%3$d" width="%4$d" height="%4$d" viewBox="0 0 %4$d %4$d">%5$s</svg>
            </svg>',
            $frameSize,
            $frameSize - 20,
            $offset,
            $size,
            strip_tags($qrCode, '<path><rect><circle><g><defs><style>')
        );
    }

    protected static function getLaptopFrame(string $qrCode, int $size)
    {
        // Simple Laptop Frame
        $width = $size * 1.5;
        $height = $size * 1.0;
        $screenW = $width * 0.8;
        $screenH = $height * 0.7;
        $offsetX = ($width - $screenW) / 2;
        $offsetY = 20;

        return sprintf(
            '<svg width="%1$d" height="%2$d" viewBox="0 0 %1$d %2$d" xmlns="http://www.w3.org/2000/svg">
                <!-- Base -->
                <rect x="0" y="%3$d" width="%1$d" height="20" rx="5" fill="#555"/>
                <!-- Screen Border -->
                <rect x="%4$d" y="0" width="%5$d" height="%6$d" rx="10" fill="#333"/>
                <!-- Screen -->
                <rect x="%7$d" y="10" width="%8$d" height="%9$d" fill="white"/>
                <!-- QR -->
                <svg x="%10$d" y="%11$d" width="%12$d" height="%12$d" viewBox="0 0 %12$d %12$d">%13$s</svg>
            </svg>',
            $width,
            $height, // 1, 2
            $height - 20, // 3 (base y)
            ($width - ($screenW + 40)) / 2, // 4 (screen border x)
            $screenW + 40, // 5
            $screenH + 40, // 6
            ($width - $screenW) / 2, // 7 (screen x)
            $screenW, // 8
            $screenH, // 9
            ($width - $size) / 2, // 10 (qr x)
            30, // 11 (qr y)
            $size, // 12
            strip_tags($qrCode, '<path><rect><circle><g><defs><style>') // 13
        );
    }

    protected static function getTabletFrame(string $qrCode, int $size)
    {
        $frameW = $size * 1.3;
        $frameH = $size * 1.6;
        $screenW = $size;
        $screenH = $size; // Square QR

        return sprintf(
            '<svg width="%1$d" height="%2$d" viewBox="0 0 %1$d %2$d" xmlns="http://www.w3.org/2000/svg">
                <rect x="0" y="0" width="%1$d" height="%2$d" rx="20" fill="#222"/>
                <rect x="15" y="40" width="%3$d" height="%3$d" fill="white"/>
                <circle cx="%4$d" cy="%5$d" r="10" fill="#444"/>
                <svg x="15" y="40" width="%3$d" height="%3$d" viewBox="0 0 %3$d %3$d">%6$s</svg>
            </svg>',
            $frameW,
            $frameH,
            $size, // 3
            $frameW / 2, // 4 (button x)
            $frameH - 25, // 5 (button y)
            strip_tags($qrCode, '<path><rect><circle><g><defs><style>')
        );
    }

    protected static function getSimpleFrame(string $qrCode, int $size)
    {
        $padding = 20;
        $frameSize = $size + ($padding * 2);

        return sprintf(
            '<svg width="%1$d" height="%1$d" viewBox="0 0 %1$d %1$d" xmlns="http://www.w3.org/2000/svg">
                <rect x="0" y="0" width="%1$d" height="%1$d" fill="white" stroke="#000" stroke-width="5"/>
                <svg x="%2$d" y="%2$d" width="%3$d" height="%3$d" viewBox="0 0 %3$d %3$d">%4$s</svg>
            </svg>',
            $frameSize,
            $padding,
            $size,
            strip_tags($qrCode, '<path><rect><circle><g><defs><style>')
        );
    }
}
