<?php

namespace RSSoftBD\QrCode\Support;

use BaconQrCode\Encoder\ByteMatrix;
use BaconQrCode\Renderer\Path\Path;
use RSSoftBD\QrCode\Shapes\DynamicEye;
use RSSoftBD\QrCode\Shapes\DynamicShape;
use BaconQrCode\Renderer\Module\SquareModule;
use BaconQrCode\Renderer\Eye\SquareEye;

class StyleRegistry
{
    public static function getShape(string $style, float $size)
    {
        $method = 'get' . ucfirst($style) . 'Shape';


        if (method_exists(self::class, $method)) {
            return self::$method($size);
        }

        // Fallback to SquareModule if not found (or handle default)
        return SquareModule::instance();
    }

    public static function getEye(string $style)
    {
        $method = 'get' . ucfirst($style) . 'Eye';

        if (method_exists(self::class, $method)) {
            return self::$method();
        }

        // Fallback to SquareEye
        return SquareEye::instance();
    }

    // -- Shapes --

    protected static function getSquareShape(float $size)
    {
        return SquareModule::instance();
    }

    protected static function getDotShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }

                    $path = $path
                        ->move($x + ($size / 2), $y)
                        ->ellipticArc($size / 2, $size / 2, 0, false, true, $x + $size, $y + ($size / 2))
                        ->ellipticArc($size / 2, $size / 2, 0, false, true, $x + ($size / 2), $y + $size)
                        ->ellipticArc($size / 2, $size / 2, 0, false, true, $x, $y + ($size / 2))
                        ->ellipticArc($size / 2, $size / 2, 0, false, true, $x + ($size / 2), $y)
                        ->close();
                }
            }
            return $path;
        });
    }

    // -- Eyes --

    protected static function getSquareEye()
    {
        return SquareEye::instance();
    }

    // -- Batch 1 --

    protected static function getPlusBoldShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $points = [
                [6, 1.5],
                [4.5, 1.5],
                [4.5, 0],
                [1.5, 0],
                [1.5, 1.5],
                [0, 1.5],
                [0, 4.5],
                [1.5, 4.5],
                [1.5, 6],
                [4.5, 6],
                [4.5, 4.5],
                [6, 4.5]
            ];
            $scale = $size / 6.0;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }

                    $offset_x = $x + (1 - $size) / 2;
                    $offset_y = $y + (1 - $size) / 2;
                    $first = true;

                    foreach ($points as [$px, $py]) {
                        $fx = $offset_x + $px * $scale;
                        $fy = $offset_y + $py * $scale;
                        if ($first) {
                            $path = $path->move($fx, $fy);
                            $first = false;
                        } else {
                            $path = $path->line($fx, $fy);
                        }
                    }
                    $path = $path->close();
                }
            }
            return $path;
        });
    }

    protected static function getPlusBoldEye()
    {
        return new DynamicEye(
            function () {
                return new Path(); // Empty path for external
            },
            function () {
                $path = new Path();
                $scale = 0.5;
                $offset = 3.0;
                $points = [
                    [6, 1.5],
                    [4.5, 1.5],
                    [4.5, 0],
                    [1.5, 0],
                    [1.5, 1.5],
                    [0, 1.5],
                    [0, 4.5],
                    [1.5, 4.5],
                    [1.5, 6],
                    [4.5, 6],
                    [4.5, 4.5],
                    [6, 4.5]
                ];
                $first = true;
                foreach ($points as [$x, $y]) {
                    $nx = ($x - $offset) * $scale;
                    $ny = ($y - $offset) * $scale;
                    if ($first) {
                        $path = $path->move($nx, $ny);
                        $first = false;
                    } else {
                        $path = $path->line($nx, $ny);
                    }
                }
                return $path->close();
            }
        );
    }

    protected static function getCutCornerShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $cut_ratio = 0.4;
            $margin = (1 - $size) / 2;
            $end = $size;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $origin_x = $x + $margin;
                    $origin_y = $y + $margin;
                    $cut = $size * $cut_ratio;

                    $path = $path->move($origin_x + $cut, $origin_y)
                        ->line($origin_x + $end, $origin_y)
                        ->line($origin_x + $end, $origin_y + $end - $cut)
                        ->line($origin_x + $end - $cut, $origin_y + $end)
                        ->line($origin_x, $origin_y + $end)
                        ->line($origin_x, $origin_y + $cut)
                        ->close();
                }
            }
            return $path;
        });
    }

    protected static function getXBoldShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $teeth = 3;
            $margin = (1 - $size) / 2;
            $s = $size;
            $tooth_width = $s / ($teeth * 2);

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }

                    $ox = $x + $margin;
                    $oy = $y + $margin;
                    $points = [];

                    for ($i = 0; $i < $teeth; $i++) {
                        $x1 = $ox + $i * 2 * $tooth_width;
                        $x2 = $x1 + $tooth_width;
                        $x3 = $x1 + 2 * $tooth_width;
                        $points[] = [$x1, $oy];
                        $points[] = [$x2, $oy + $tooth_width];
                        $points[] = [$x3, $oy];
                    }
                    for ($i = 0; $i < $teeth; $i++) {
                        $y1 = $oy + $i * 2 * $tooth_width;
                        $y2 = $y1 + $tooth_width;
                        $y3 = $y1 + 2 * $tooth_width;
                        $points[] = [$ox + $s, $y1];
                        $points[] = [$ox + $s - $tooth_width, $y2];
                        $points[] = [$ox + $s, $y3];
                    }
                    for ($i = $teeth - 1; $i >= 0; $i--) {
                        $x1 = $ox + $i * 2 * $tooth_width;
                        $x2 = $x1 + $tooth_width;
                        $x3 = $x1 + 2 * $tooth_width;
                        $points[] = [$x3, $oy + $s];
                        $points[] = [$x2, $oy + $s - $tooth_width];
                        $points[] = [$x1, $oy + $s];
                    }
                    for ($i = $teeth - 1; $i >= 0; $i--) {
                        $y1 = $oy + $i * 2 * $tooth_width;
                        $y2 = $y1 + $tooth_width;
                        $y3 = $y1 + 2 * $tooth_width;
                        $points[] = [$ox, $y3];
                        $points[] = [$ox + $tooth_width, $y2];
                        $points[] = [$ox, $y1];
                    }

                    $path = $path->move($points[0][0], $points[0][1]);
                    for ($i = 1; $i < count($points); $i++) {
                        $path = $path->line($points[$i][0], $points[$i][1]);
                    }
                    $path = $path->close();
                }
            }
            return $path;
        });
    }

    protected static function getCircleEye()
    {
        return new DynamicEye(
            function () {
                return (new Path())
                    ->move(3.5, 0)
                    ->ellipticArc(3.5, 3.5, 0., false, true, 0., 3.5)
                    ->ellipticArc(3.5, 3.5, 0., false, true, -3.5, 0.)
                    ->ellipticArc(3.5, 3.5, 0., false, true, 0., -3.5)
                    ->ellipticArc(3.5, 3.5, 0., false, true, 3.5, 0.)
                    ->close()
                    ->move(2.5, 0)
                    ->ellipticArc(2.5, 2.5, 0., false, true, 0., 2.5)
                    ->ellipticArc(2.5, 2.5, 0., false, true, -2.5, 0.)
                    ->ellipticArc(2.5, 2.5, 0., false, true, 0., -2.5)
                    ->ellipticArc(2.5, 2.5, 0., false, true, 2.5, 0.)
                    ->close();
            },
            function () {
                return (new Path())
                    ->move(1.5, 0)
                    ->ellipticArc(1.5, 1.5, 0., false, true, 0., 1.5)
                    ->ellipticArc(1.5, 1.5, 0., false, true, -1.5, 0.)
                    ->ellipticArc(1.5, 1.5, 0., false, true, 0., -1.5)
                    ->ellipticArc(1.5, 1.5, 0., false, true, 1.5, 0.)
                    ->close();
            }
        );
    }

    // -- Batch 2 --

    protected static function getXCrossShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $points = [
                [3, 5.1],
                [3.9, 6],
                [6, 6],
                [6, 3.9],
                [5.1, 3],
                [6, 2.1],
                [6, 0],
                [3.9, 0],
                [3, 0.9],
                [2.1, 0],
                [0, 0],
                [0, 2.1],
                [0.9, 3],
                [0, 3.9],
                [0, 6],
                [2.1, 6]
            ];
            $scale = $size / 6.0;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $offset_x = $x + (1 - $size) / 2;
                    $offset_y = $y + (1 - $size) / 2;
                    $first = true;
                    foreach ($points as [$px, $py]) {
                        $fx = $offset_x + $px * $scale;
                        $fy = $offset_y + $py * $scale;
                        if ($first) {
                            $path = $path->move($fx, $fy);
                            $first = false;
                        } else {
                            $path = $path->line($fx, $fy);
                        }
                    }
                    $path = $path->close();
                }
            }
            return $path;
        });
    }

    protected static function getXCrossEye()
    {
        return new DynamicEye(
            function () {
                return new Path(); // Empty external
            },
            function () {
                $path = new Path();
                $scale = 0.5;
                $offset = 3.0;
                $points = [
                    [3, 5.1],
                    [3.9, 6],
                    [6, 6],
                    [6, 3.9],
                    [5.1, 3],
                    [6, 2.1],
                    [6, 0],
                    [3.9, 0],
                    [3, 0.9],
                    [2.1, 0],
                    [0, 0],
                    [0, 2.1],
                    [0.9, 3],
                    [0, 3.9],
                    [0, 6],
                    [2.1, 6]
                ];
                $first = true;
                foreach ($points as [$x, $y]) {
                    $nx = ($x - $offset) * $scale;
                    $ny = ($y - $offset) * $scale;
                    if ($first) {
                        $path = $path->move($nx, $ny);
                        $first = false;
                    } else {
                        $path = $path->line($nx, $ny);
                    }
                }
                return $path->close();
            }
        );
    }

    protected static function getXCurvyShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $scale = $size / 6.0;
            $margin = (1 - $size) / 2;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $ox = $x + $margin;
                    $oy = $y + $margin;

                    $path = $path->move($ox + 3.0 * $scale, $oy + 5.1 * $scale)
                        ->line($ox + 3.4 * $scale, $oy + 5.5 * $scale)
                        ->curve($ox + 3.7 * $scale, $oy + 5.8 * $scale, $ox + 4.1 * $scale, $oy + 6.0 * $scale, $ox + 4.5 * $scale, $oy + 6.0 * $scale)
                        ->curve($ox + 5.3 * $scale, $oy + 6.0 * $scale, $ox + 6.0 * $scale, $oy + 5.3 * $scale, $ox + 6.0 * $scale, $oy + 4.5 * $scale)
                        ->curve($ox + 6.0 * $scale, $oy + 4.1 * $scale, $ox + 5.8 * $scale, $oy + 3.7 * $scale, $ox + 5.6 * $scale, $oy + 3.4 * $scale)
                        ->line($ox + 5.1 * $scale, $oy + 3.0 * $scale)
                        ->line($ox + 5.6 * $scale, $oy + 2.6 * $scale)
                        ->curve($ox + 5.8 * $scale, $oy + 2.3 * $scale, $ox + 6.0 * $scale, $oy + 1.9 * $scale, $ox + 6.0 * $scale, $oy + 1.5 * $scale)
                        ->curve($ox + 6.0 * $scale, $oy + 0.7 * $scale, $ox + 5.3 * $scale, $oy + 0.0 * $scale, $ox + 4.5 * $scale, $oy + 0.0 * $scale)
                        ->curve($ox + 4.1 * $scale, $oy + 0.0 * $scale, $ox + 3.7 * $scale, $oy + 0.2 * $scale, $ox + 3.4 * $scale, $oy + 0.4 * $scale)
                        ->line($ox + 3.0 * $scale, $oy + 0.9 * $scale)
                        ->line($ox + 2.6 * $scale, $oy + 0.4 * $scale)
                        ->curve($ox + 2.3 * $scale, $oy + 0.2 * $scale, $ox + 1.9 * $scale, $oy + 0.0 * $scale, $ox + 1.5 * $scale, $oy + 0.0 * $scale)
                        ->curve($ox + 0.7 * $scale, $oy + 0.0 * $scale, $ox + 0.0 * $scale, $oy + 0.7 * $scale, $ox + 0.0 * $scale, $oy + 1.5 * $scale)
                        ->curve($ox + 0.0 * $scale, $oy + 1.9 * $scale, $ox + 0.2 * $scale, $oy + 2.3 * $scale, $ox + 0.4 * $scale, $oy + 2.6 * $scale)
                        ->line($ox + 0.9 * $scale, $oy + 3.0 * $scale)
                        ->line($ox + 0.4 * $scale, $oy + 3.4 * $scale)
                        ->curve($ox + 0.2 * $scale, $oy + 3.7 * $scale, $ox + 0.0 * $scale, $oy + 4.1 * $scale, $ox + 0.0 * $scale, $oy + 4.5 * $scale)
                        ->curve($ox + 0.0 * $scale, $oy + 5.3 * $scale, $ox + 0.7 * $scale, $oy + 6.0 * $scale, $ox + 1.5 * $scale, $oy + 6.0 * $scale)
                        ->curve($ox + 1.9 * $scale, $oy + 6.0 * $scale, $ox + 2.3 * $scale, $oy + 5.8 * $scale, $ox + 2.6 * $scale, $oy + 5.6 * $scale)
                        ->line($ox + 3.0 * $scale, $oy + 5.1 * $scale)
                        ->close();
                }
            }
            return $path;
        });
    }

    protected static function getXCurvyEye()
    {
        return new DynamicEye(
            function () {
                return new Path(); // Empty external
            },
            function () {
                $path = new Path();
                $scale = 0.5;
                $offset = 3.0;
                $points = [
                    ['move', [3.0, 5.1]],
                    ['curve', [3.4, 5.5, 3.7, 5.8, 4.5, 6.0]],
                    ['curve', [5.3, 6.0, 6.0, 5.3, 6.0, 4.5]],
                    ['curve', [6.0, 4.1, 5.8, 3.7, 5.6, 3.4]],
                    ['line', [5.1, 3.0]],
                    ['line', [5.6, 2.6]],
                    ['curve', [5.8, 2.3, 6.0, 1.9, 6.0, 1.5]],
                    ['curve', [6.0, 0.7, 5.3, 0.0, 4.5, 0.0]],
                    ['curve', [4.1, 0.0, 3.7, 0.2, 3.4, 0.4]],
                    ['line', [3.0, 0.9]],
                    ['line', [2.6, 0.4]],
                    ['curve', [2.3, 0.2, 1.9, 0.0, 1.5, 0.0]],
                    ['curve', [0.7, 0.0, 0.0, 0.7, 0.0, 1.5]],
                    ['curve', [0.0, 1.9, 0.2, 2.3, 0.4, 2.6]],
                    ['line', [0.9, 3.0]],
                    ['line', [0.4, 3.4]],
                    ['curve', [0.2, 3.7, 0.0, 4.1, 0.0, 4.5]],
                    ['curve', [0.0, 5.3, 0.7, 6.0, 1.5, 6.0]],
                    ['curve', [1.9, 6.0, 2.3, 5.8, 2.6, 5.6]],
                    ['line', [3.0, 5.1]],
                ];

                foreach ($points as [$command, $coords]) {
                    if ($command === 'move' || $command === 'line') {
                        [$x, $y] = $coords;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = ($command === 'move') ? $path->move($x, $y) : $path->line($x, $y);
                    } elseif ($command === 'curve') {
                        [$x1, $y1, $x2, $y2, $x, $y] = $coords;
                        $x1 = ($x1 - $offset) * $scale;
                        $y1 = ($y1 - $offset) * $scale;
                        $x2 = ($x2 - $offset) * $scale;
                        $y2 = ($y2 - $offset) * $scale;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = $path->curve($x1, $y1, $x2, $y2, $x, $y);
                    }
                }
                return $path->close();
            }
        );
    }

    protected static function getRhombusShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $halfSize = $size / 2;
            $margin = (1 - $size) / 2;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $pathX = $x + $margin;
                    $pathY = $y + $margin;

                    $path = $path
                        ->move($pathX + $size, $pathY + $halfSize)
                        ->ellipticArc(0, 0, 0, false, true, $pathX + $halfSize, $pathY + $size)
                        ->ellipticArc(0, 0, 0, false, true, $pathX, $pathY + $halfSize)
                        ->ellipticArc(0, 0, 0, false, true, $pathX + $halfSize, $pathY)
                        ->ellipticArc(0, 0, 0, false, true, $pathX + $size, $pathY + $halfSize)
                        ->close();
                }
            }
            return $path;
        });
    }

    protected static function getRhombusEye()
    {
        return new DynamicEye(
            function () {
                return new Path(); // Empty external
            },
            function () {
                return (new Path())
                    ->move(1.5, 0)
                    ->ellipticArc(0., 0., 0., false, true, 0., 1.5)
                    ->ellipticArc(0., 0., 0., false, true, -1.5, 0.)
                    ->ellipticArc(0., 0., 0., false, true, 0., -1.5)
                    ->ellipticArc(0., 0., 0., false, true, 1.5, 0.)
                    ->close();
            }
        );
    }

    // -- Batch 3 --

    protected static function getSquareElasticShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $scale = $size / 6.0;
            $margin = (1 - $size) / 2;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $ox = $x + $margin;
                    $oy = $y + $margin;

                    $path = $path->move($ox + 6.0 * $scale, $oy + 6.0 * $scale)
                        ->curve($ox + 4.1 * $scale, $oy + 5.4 * $scale, $ox + 1.9 * $scale, $oy + 5.4 * $scale, $ox + 0.0 * $scale, $oy + 6.0 * $scale)
                        ->curve($ox + 0.6 * $scale, $oy + 4.1 * $scale, $ox + 0.6 * $scale, $oy + 1.9 * $scale, $ox + 0.0 * $scale, $oy + 0.0 * $scale)
                        ->curve($ox + 1.9 * $scale, $oy + 0.6 * $scale, $ox + 4.1 * $scale, $oy + 0.6 * $scale, $ox + 6.0 * $scale, $oy + 0.0 * $scale)
                        ->curve($ox + 5.4 * $scale, $oy + 1.9 * $scale, $ox + 5.4 * $scale, $oy + 4.1 * $scale, $ox + 6.0 * $scale, $oy + 6.0 * $scale)
                        ->close();
                }
            }
            return $path;
        });
    }

    protected static function getSquareElasticEye()
    {
        return new DynamicEye(
            function () {
                return new Path(); // Empty external
            },
            function () {
                $path = new Path();
                $scale = 0.5;
                $offset = 3.0;
                $commands = [
                    ['move', [6.0, 6.0]],
                    ['curve', [4.1, 5.4, 1.9, 5.4, 0.0, 6.0]],
                    ['curve', [0.6, 4.1, 0.6, 1.9, 0.0, 0.0]],
                    ['curve', [1.9, 0.6, 4.1, 0.6, 6.0, 0.0]],
                    ['curve', [5.4, 1.9, 5.4, 4.1, 6.0, 6.0]]
                ];

                foreach ($commands as [$command, $coords]) {
                    if ($command === 'move') {
                        [$x, $y] = $coords;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = $path->move($x, $y);
                    } elseif ($command === 'curve') {
                        [$x1, $y1, $x2, $y2, $x, $y] = $coords;
                        $x1 = ($x1 - $offset) * $scale;
                        $y1 = ($y1 - $offset) * $scale;
                        $x2 = ($x2 - $offset) * $scale;
                        $y2 = ($y2 - $offset) * $scale;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = $path->curve($x1, $y1, $x2, $y2, $x, $y);
                    }
                }
                return $path->close();
            }
        );
    }

    protected static function getBlossomEye()
    {
        return new DynamicEye(
            function () {
                return (new Path())
                    ->move(-3.5, 0.)
                    ->curve(-3.5, -3.5, -3.5, -3.5, 0., -3.5)
                    ->move(-3.5, 0.)
                    ->curve(-3.5, 3.5, -3.5, 3.5, 0, 3.5)
                    ->move(3.5, 0.)
                    ->curve(3.5, -3.5, 3.5, -3.5, 0, -3.5)
                    ->move(3.5, 0.)
                    ->line(3.5, 0.)
                    ->line(0, 3.5)
                    ->line(3.5, 3.5)
                    ->close()
                    ->ellipticArc(0., 0., 0., false, true, 0., 3.5)
                    ->ellipticArc(0., 0., 0., false, true, -3.5, 0.)
                    ->ellipticArc(0., 0., 0., false, true, 0., -3.5)
                    ->ellipticArc(0., 0., 0., false, true, 3.5, 0.)
                    ->move(-2.5, 0.)
                    ->curve(-2.5, -2.5, -2.5, -2.5, 0., -2.5)
                    ->move(-2.5, 0.)
                    ->curve(-2.5, 2.5, -2.5, 2.5, 0, 2.5)
                    ->move(2.5, 0.)
                    ->curve(2.5, -2.5, 2.5, -2.5, 0, -2.5)
                    ->move(2.5, 0.)
                    ->line(2.5, 0.)
                    ->line(0, 2.5)
                    ->line(2.5, 2.5)
                    ->close()
                    ->ellipticArc(0., 0., 0., false, true, 0., 2.5)
                    ->ellipticArc(0., 0., 0., false, true, -2.5, 0.)
                    ->ellipticArc(0., 0., 0., false, true, 0., -2.5)
                    ->ellipticArc(0., 0., 0., false, true, 2.5, 0.);
            },
            function () {
                return (new Path())
                    ->move(-1.5, 0.)
                    ->curve(-1.5, -1.5, -1.5, -1.5, 0., -1.5)
                    ->move(-1.5, 0.)
                    ->curve(-1.5, 1.5, -1.5, 1.5, 0, 1.5)
                    ->move(1.5, 0.)
                    ->curve(1.5, -1.5, 1.5, -1.5, 0, -1.5)
                    ->move(1.5, 0.)
                    ->line(1.5, 0.)
                    ->line(0, 1.5)
                    ->line(1.5, 1.5)
                    ->close()
                    ->ellipticArc(0., 0., 0., false, true, 0., 1.5)
                    ->ellipticArc(0., 0., 0., false, true, -1.5, 0.)
                    ->ellipticArc(0., 0., 0., false, true, 0., -1.5)
                    ->ellipticArc(0., 0., 0., false, true, 1.5, 0.);
            }
        );
    }

    protected static function getAmourShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $halfSize = $size / 2;
            $margin = (1 - $size) / 2;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $pathX = $x + $margin;
                    $pathY = $y + $margin;

                    $path = $path
                        ->move($pathX + $size, $pathY + $halfSize)
                        ->ellipticArc(0, 0, 0, false, true, $pathX + $halfSize, $pathY + $size)
                        ->ellipticArc(0, 0, 0, false, true, $pathX, $pathY + $halfSize)
                        ->ellipticArc(0.1, 0.1, 0, false, true, $pathX + $halfSize, $pathY)
                        ->ellipticArc(0.1, 0.1, 0, false, true, $pathX + $size, $pathY + $halfSize)
                        ->close();
                }
            }
            return $path;
        });
    }

    protected static function getAmourEye()
    {
        return new DynamicEye(
            function () {
                return new Path(); // Empty external
            },
            function () {
                $path = new Path();
                $scale = 0.5;
                $offset = 3.0;
                $commands = [
                    ['move', [6.0, 1.8]],
                    ['curve', [5.9, 1.0, 5.3, 0.4, 4.5, 0.3]],
                    ['curve', [3.9, 0.2, 3.4, 0.5, 3.0, 0.9]],
                    ['curve', [2.6, 0.5, 2.1, 0.3, 1.6, 0.3]],
                    ['curve', [0.8, 0.4, 0.1, 1.0, 0.0, 1.8]],
                    ['curve', [0.0, 2.3, 0.1, 2.7, 0.3, 3.0]],
                    ['line', [0.3, 3.0]],
                    ['line', [0.6, 3.3]],
                    ['line', [2.5, 5.5]],
                    ['curve', [2.8, 5.8, 3.2, 5.8, 3.4, 5.5]],
                    ['line', [5.2, 3.6]],
                    ['curve', [5.3, 3.5, 5.5, 3.3, 5.6, 3.0]],
                    ['curve', [5.9, 2.8, 6.1, 2.3, 6.0, 1.8]],
                ];

                foreach ($commands as [$command, $coords]) {
                    if ($command === 'move' || $command === 'line') {
                        [$x, $y] = $coords;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = ($command === 'move') ? $path->move($x, $y) : $path->line($x, $y);
                    } elseif ($command === 'curve') {
                        [$x1, $y1, $x2, $y2, $x, $y] = $coords;
                        $x1 = ($x1 - $offset) * $scale;
                        $y1 = ($y1 - $offset) * $scale;
                        $x2 = ($x2 - $offset) * $scale;
                        $y2 = ($y2 - $offset) * $scale;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = $path->curve($x1, $y1, $x2, $y2, $x, $y);
                    }
                }
                return $path->close();
            }
        );
    }

    protected static function getHexShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $half_size = $size / 2;
            $margin = (1 - $size) / 2;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $cx = $x + $margin + $half_size;
                    $cy = $y + $margin + $half_size;
                    $radius = $half_size;
                    $points = [];
                    for ($i = 0; $i < 6; $i++) {
                        $angle = deg2rad(60 * $i - 30);
                        $points[] = [$cx + cos($angle) * $radius, $cy + sin($angle) * $radius];
                    }
                    $path = $path->move($points[0][0], $points[0][1]);
                    for ($i = 1; $i < 6; $i++) {
                        $path = $path->line($points[$i][0], $points[$i][1]);
                    }
                    $path = $path->close();
                }
            }
            return $path;
        });
    }

    protected static function getHexEye()
    {
        return new DynamicEye(
            function () {
                $path = new Path();
                $outer_radius = 3.5;
                $sides = 6;
                for ($i = 0; $i < $sides; $i++) {
                    $angle = deg2rad(60 * $i - 30);
                    $x = cos($angle) * $outer_radius;
                    $y = sin($angle) * $outer_radius;
                    if ($i === 0)
                        $path = $path->move($x, $y);
                    else
                        $path = $path->line($x, $y);
                }
                $path = $path->close();

                $inner_radius = 2.5;
                for ($i = 0; $i < $sides; $i++) {
                    $angle = deg2rad(60 * $i - 30);
                    $x = cos($angle) * $inner_radius;
                    $y = sin($angle) * $inner_radius;
                    if ($i === 0)
                        $path = $path->move($x, $y);
                    else
                        $path = $path->line($x, $y);
                }
                return $path->close();
            },
            function () {
                $path = new Path();
                $radius = 1.5;
                $sides = 6;
                for ($i = 0; $i < $sides; $i++) {
                    $angle = deg2rad(60 * $i - 30);
                    $x = cos($angle) * $radius;
                    $y = sin($angle) * $radius;
                    if ($i === 0)
                        $path = $path->move($x, $y);
                    else
                        $path = $path->line($x, $y);
                }
                return $path->close();
            }
        );
    }

    // -- Batch 4 --

    protected static function getSquircleInvertedEye()
    {
        return new DynamicEye(
            function () {
                return new Path(); // Empty external
            },
            function () {
                $path = new Path();
                $scale = 0.5;
                $offset = 3.0;
                $commands = [
                    ['move', [1.5, 6.0]],
                    ['curve', [1.2, 5.3, 0.7, 4.8, 0.0, 4.5]],
                    ['line', [0.0, 1.5]],
                    ['curve', [0.0, 0.7, 0.7, 0.0, 1.5, 0.0]],
                    ['line', [4.5, 0.0]],
                    ['curve', [4.8, 0.7, 5.3, 1.2, 6.0, 1.5]],
                    ['line', [6.0, 4.5]],
                    ['curve', [6.0, 5.3, 5.3, 6.0, 4.5, 6.0]],
                    ['line', [1.5, 6.0]]
                ];

                foreach ($commands as [$command, $coords]) {
                    if ($command === 'move' || $command === 'line') {
                        [$x, $y] = $coords;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = ($command === 'move') ? $path->move($x, $y) : $path->line($x, $y);
                    } elseif ($command === 'curve') {
                        [$x1, $y1, $x2, $y2, $x, $y] = $coords;
                        $x1 = ($x1 - $offset) * $scale;
                        $y1 = ($y1 - $offset) * $scale;
                        $x2 = ($x2 - $offset) * $scale;
                        $y2 = ($y2 - $offset) * $scale;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = $path->curve($x1, $y1, $x2, $y2, $x, $y);
                    }
                }
                return $path->close();
            }
        );
    }

    protected static function getFoliageEye()
    {
        return new DynamicEye(
            function () {
                return (new Path())
                    ->move(-3.5, 3.5)
                    ->curve(-3.5, -3.5, -3.5, -3.5, 3.5, -3.5)
                    ->move(3.5, -3.5)
                    ->curve(3.5, 3.5, 3.5, 3.5, -3.5, 3.5)
                    ->close()
                    ->move(-2.5, 2.5)
                    ->curve(-2.5, -2.5, -2.5, -2.5, 2.5, -2.5)
                    ->move(2.5, -2.5)
                    ->curve(2.5, 2.5, 2.5, 2.5, -2.5, 2.5);
            },
            function () {
                return (new Path())
                    ->move(-1.5, 1.5)
                    ->curve(-1.5, -1.5, -1.5, -1.5, 1.5, -1.5)
                    ->move(1.5, -1.5)
                    ->curve(1.5, 1.5, 1.5, 1.5, -1.5, 1.5);
            }
        );
    }

    protected static function getShurikenShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $scale = $size / 6.0;
            $margin = (1 - $size) / 2;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $ox = $x + $margin;
                    $oy = $y + $margin;

                    $path = $path->move($ox + 3.5 * $scale, $oy + 6.0 * $scale)
                        ->curve($ox + 0.7 * $scale, $oy + 4.7 * $scale, $ox + 1.7 * $scale, $oy + 3.0 * $scale, $ox + 0.0 * $scale, $oy + 3.5 * $scale)
                        ->curve($ox + 1.3 * $scale, $oy + 0.7 * $scale, $ox + 3.0 * $scale, $oy + 1.7 * $scale, $ox + 2.5 * $scale, $oy + 0.0 * $scale)
                        ->curve($ox + 5.3 * $scale, $oy + 1.3 * $scale, $ox + 4.3 * $scale, $oy + 3.0 * $scale, $ox + 6.0 * $scale, $oy + 2.5 * $scale)
                        ->curve($ox + 4.7 * $scale, $oy + 5.3 * $scale, $ox + 3.0 * $scale, $oy + 4.3 * $scale, $ox + 3.5 * $scale, $oy + 6.0 * $scale)
                        ->close();
                }
            }
            return $path;
        });
    }

    protected static function getShurikenEye()
    {
        return new DynamicEye(
            function () {
                $path = new Path();
                $scale = 0.5;
                $offset = 7.0;
                $commands = [
                    ['move', [13.8, 1.1]],
                    ['curve', [10.3, 5.0, 10.0, -1.0, 1.0, 0.1]],
                    ['curve', [4.9, 3.6, -1.0, 4.0, 0.1, 13.0]],
                    ['curve', [3.6, 9.1, 4.0, 15.0, 13.0, 13.9]],
                    ['curve', [9.1, 10.4, 15.0, 10.0, 13.8, 1.1]],
                    ['close', []],
                    ['move', [9.4, 13.2]],
                    ['curve', [2.9, 11.6, 4.2, 7.6, 0.8, 9.4]],
                    ['curve', [2.4, 2.9, 7.6, 4.2, 5.8, 0.8]],
                    ['curve', [12.3, 2.4, 11.0, 7.4, 14.4, 5.6]],
                    ['curve', [11.7, 11.1, 7.6, 9.8, 9.4, 13.2]],
                    ['close', []],
                ];

                foreach ($commands as [$cmd, $coords]) {
                    if ($cmd === 'move') {
                        [$x, $y] = $coords;
                        $path = $path->move(($x - $offset) * $scale, ($y - $offset) * $scale);
                    } elseif ($cmd === 'curve') {
                        [$x1, $y1, $x2, $y2, $x, $y] = $coords;
                        $path = $path->curve(
                            ($x1 - $offset) * $scale,
                            ($y1 - $offset) * $scale,
                            ($x2 - $offset) * $scale,
                            ($y2 - $offset) * $scale,
                            ($x - $offset) * $scale,
                            ($y - $offset) * $scale
                        );
                    } elseif ($cmd === 'close') {
                        $path = $path->close();
                    }
                }
                return $path;
            },
            function () {
                $path = new Path();
                $scale = 0.5;
                $offset = 3.0;
                $commands = [
                    ['move', [3.5, 6.0]],
                    ['curve', [0.7, 4.7, 1.7, 3.0, 0.0, 3.5]],
                    ['curve', [1.3, 0.7, 3.0, 1.7, 2.5, 0.0]],
                    ['curve', [5.3, 1.3, 4.3, 3.0, 6.0, 2.5]],
                    ['curve', [4.7, 5.3, 3.0, 4.3, 3.5, 6.0]],
                ];

                foreach ($commands as [$command, $coords]) {
                    if ($command === 'move') {
                        [$x, $y] = $coords;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = $path->move($x, $y);
                    } elseif ($command === 'curve') {
                        [$x1, $y1, $x2, $y2, $x, $y] = $coords;
                        $x1 = ($x1 - $offset) * $scale;
                        $y1 = ($y1 - $offset) * $scale;
                        $x2 = ($x2 - $offset) * $scale;
                        $y2 = ($y2 - $offset) * $scale;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = $path->curve($x1, $y1, $x2, $y2, $x, $y);
                    }
                }
                return $path->close();
            }
        );
    }

    protected static function getOctoShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $half_size = $size / 2;
            $margin = (1 - $size) / 2;
            $radius = $half_size / cos(pi() / 8);

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $center_x = $x + $margin + $half_size;
                    $center_y = $y + $margin + $half_size;
                    $points = [];
                    for ($i = 0; $i < 8; $i++) {
                        $angle = pi() / 4 * $i + pi() / 8;
                        $points[] = [$center_x + $radius * cos($angle), $center_y + $radius * sin($angle)];
                    }
                    $path = $path->move($points[0][0], $points[0][1]);
                    for ($i = 1; $i < count($points); $i++) {
                        $path = $path->line($points[$i][0], $points[$i][1]);
                    }
                    $path = $path->close();
                }
            }
            return $path;
        });
    }

    protected static function getOctoEye()
    {
        return new DynamicEye(
            function () {
                $path = new Path();
                $outer_radius = 3.5;
                $sides = 8;
                for ($i = 0; $i < $sides; $i++) {
                    $angle = deg2rad(45 * $i - 22.5);
                    $x = cos($angle) * $outer_radius;
                    $y = sin($angle) * $outer_radius;
                    if ($i === 0)
                        $path = $path->move($x, $y);
                    else
                        $path = $path->line($x, $y);
                }
                $path = $path->close();

                $inner_radius = 2.5;
                for ($i = 0; $i < $sides; $i++) {
                    $angle = deg2rad(45 * $i - 22.5);
                    $x = cos($angle) * $inner_radius;
                    $y = sin($angle) * $inner_radius;
                    if ($i === 0)
                        $path = $path->move($x, $y);
                    else
                        $path = $path->line($x, $y);
                }
                return $path->close();
            },
            function () {
                $path = new Path();
                $radius = 1.5;
                $sides = 8;
                for ($i = 0; $i < $sides; $i++) {
                    $angle = deg2rad(45 * $i - 22.5);
                    $x = cos($angle) * $radius;
                    $y = sin($angle) * $radius;
                    if ($i === 0)
                        $path = $path->move($x, $y);
                    else
                        $path = $path->line($x, $y);
                }
                return $path->close();
            }
        );
    }

    // -- Batch 5 --

    protected static function getSquareRandomShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $max_offset = (1 - $size) * 0.4;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }

                    $seed = crc32("{$x}_{$y}");
                    mt_srand($seed);
                    $offset_x = ((mt_rand() / mt_getrandmax()) * 2 - 1) * $max_offset;
                    $offset_y = ((mt_rand() / mt_getrandmax()) * 2 - 1) * $max_offset;

                    $origin_x = $x + ((1 - $size) / 2) + $offset_x;
                    $origin_y = $y + ((1 - $size) / 2) + $offset_y;

                    $center_x = $origin_x + $size / 2;
                    $center_y = $origin_y + $size / 2;

                    $should_rotate = (mt_rand() / mt_getrandmax()) < 0.3;
                    $rotation_angle = 0;
                    if ($should_rotate) {
                        $rotation_angle = deg2rad(((mt_rand() / mt_getrandmax()) * 10) - 5);
                    }

                    $corners = [
                        [$origin_x, $origin_y],
                        [$origin_x + $size, $origin_y],
                        [$origin_x + $size, $origin_y + $size],
                        [$origin_x, $origin_y + $size],
                    ];

                    if ($should_rotate) {
                        foreach ($corners as &$point) {
                            $dx = $point[0] - $center_x;
                            $dy = $point[1] - $center_y;
                            $point[0] = $center_x + ($dx * cos($rotation_angle) - $dy * sin($rotation_angle));
                            $point[1] = $center_y + ($dx * sin($rotation_angle) + $dy * cos($rotation_angle));
                        }
                    }

                    $path = $path->move($corners[0][0], $corners[0][1]);
                    for ($i = 1; $i < 4; $i++) {
                        $path = $path->line($corners[$i][0], $corners[$i][1]);
                    }
                    $path = $path->close();
                }
            }
            return $path;
        });
    }

    protected static function getCrossRoundedShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $scale = $size / 6.0;
            $offset = (1 - $size) / 2;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $ox = $x + $offset;
                    $oy = $y + $offset;

                    $path = $path->move($ox + 4.5 * $scale, $oy + 1.5 * $scale)
                        ->curve($ox + 4.5 * $scale, $oy + 0.7 * $scale, $ox + 3.8 * $scale, $oy + 0.0 * $scale, $ox + 3.0 * $scale, $oy + 0.0 * $scale)
                        ->curve($ox + 2.2 * $scale, $oy + 0.0 * $scale, $ox + 1.5 * $scale, $oy + 0.7 * $scale, $ox + 1.5 * $scale, $oy + 1.5 * $scale)
                        ->curve($ox + 0.7 * $scale, $oy + 1.5 * $scale, $ox + 0.0 * $scale, $oy + 2.2 * $scale, $ox + 0.0 * $scale, $oy + 3.0 * $scale)
                        ->curve($ox + 0.0 * $scale, $oy + 3.8 * $scale, $ox + 0.7 * $scale, $oy + 4.5 * $scale, $ox + 1.5 * $scale, $oy + 4.5 * $scale)
                        ->curve($ox + 1.5 * $scale, $oy + 5.3 * $scale, $ox + 2.2 * $scale, $oy + 6.0 * $scale, $ox + 3.0 * $scale, $oy + 6.0 * $scale)
                        ->curve($ox + 3.8 * $scale, $oy + 6.0 * $scale, $ox + 4.5 * $scale, $oy + 5.3 * $scale, $ox + 4.5 * $scale, $oy + 4.5 * $scale)
                        ->curve($ox + 5.3 * $scale, $oy + 4.5 * $scale, $ox + 6.0 * $scale, $oy + 3.8 * $scale, $ox + 6.0 * $scale, $oy + 3.0 * $scale)
                        ->curve($ox + 6.0 * $scale, $oy + 2.2 * $scale, $ox + 5.3 * $scale, $oy + 1.5 * $scale, $ox + 4.5 * $scale, $oy + 1.5 * $scale)
                        ->close();
                }
            }
            return $path;
        });
    }

    protected static function getCrossRoundedEye()
    {
        return new DynamicEye(
            function () {
                return new Path(); // Empty external
            },
            function () {
                $path = new Path();
                $scale = 0.5;
                $offset = 3.0;
                $points = [
                    ['move', [3.0, 5.1]],
                    ['curve', [3.4, 5.5, 3.7, 5.8, 4.5, 6.0]],
                    ['curve', [5.3, 6.0, 6.0, 5.3, 6.0, 4.5]],
                    ['curve', [6.0, 4.1, 5.8, 3.7, 5.6, 3.4]],
                    ['line', [5.1, 3.0]],
                    ['line', [5.6, 2.6]],
                    ['curve', [5.8, 2.3, 6.0, 1.9, 6.0, 1.5]],
                    ['curve', [6.0, 0.7, 5.3, 0.0, 4.5, 0.0]],
                    ['curve', [4.1, 0.0, 3.7, 0.2, 3.4, 0.4]],
                    ['line', [3.0, 0.9]],
                    ['line', [2.6, 0.4]],
                    ['curve', [2.3, 0.2, 1.9, 0.0, 1.5, 0.0]],
                    ['curve', [0.7, 0.0, 0.0, 0.7, 0.0, 1.5]],
                    ['curve', [0.0, 1.9, 0.2, 2.3, 0.4, 2.6]],
                    ['line', [0.9, 3.0]],
                    ['line', [0.4, 3.4]],
                    ['curve', [0.2, 3.7, 0.0, 4.1, 0.0, 4.5]],
                    ['curve', [0.0, 5.3, 0.7, 6.0, 1.5, 6.0]],
                    ['curve', [1.9, 6.0, 2.3, 5.8, 2.6, 5.6]],
                    ['line', [3.0, 5.1]],
                ];

                foreach ($points as [$command, $coords]) {
                    if ($command === 'move' || $command === 'line') {
                        [$x, $y] = $coords;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = ($command === 'move') ? $path->move($x, $y) : $path->line($x, $y);
                    } elseif ($command === 'curve') {
                        [$x1, $y1, $x2, $y2, $x, $y] = $coords;
                        $x1 = ($x1 - $offset) * $scale;
                        $y1 = ($y1 - $offset) * $scale;
                        $x2 = ($x2 - $offset) * $scale;
                        $y2 = ($y2 - $offset) * $scale;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = $path->curve($x1, $y1, $x2, $y2, $x, $y);
                    }
                }
                return $path->close();
            }
        );
    }

    protected static function getSoftShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $unit = $size;
            $margin = (1 - $unit) / 2;
            $corner_radius = $unit * 0.25;
            $segments = 5;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $x0 = $x + $margin;
                    $y0 = $y + $margin;
                    $x1 = $x0 + $unit;
                    $y1 = $y0 + $unit;

                    $path = $path->move($x0 + $corner_radius, $y0)
                        ->line($x1 - $corner_radius, $y0);

                    for ($i = 0; $i <= $segments; $i++) {
                        $angle = deg2rad(270 + ($i * 90 / $segments));
                        $path = $path->line(
                            $x1 - $corner_radius + cos($angle) * $corner_radius,
                            $y0 + $corner_radius + sin($angle) * $corner_radius
                        );
                    }

                    $path = $path->line($x1, $y1 - $corner_radius);

                    for ($i = 0; $i <= $segments; $i++) {
                        $angle = deg2rad(0 + ($i * 90 / $segments));
                        $path = $path->line(
                            $x1 - $corner_radius + cos($angle) * $corner_radius,
                            $y1 - $corner_radius + sin($angle) * $corner_radius
                        );
                    }

                    $path = $path->line($x0 + $corner_radius, $y1);

                    for ($i = 0; $i <= $segments; $i++) {
                        $angle = deg2rad(90 + ($i * 90 / $segments));
                        $path = $path->line(
                            $x0 + $corner_radius + cos($angle) * $corner_radius,
                            $y1 - $corner_radius + sin($angle) * $corner_radius
                        );
                    }

                    $path = $path->line($x0, $y0 + $corner_radius);

                    for ($i = 0; $i <= $segments; $i++) {
                        $angle = deg2rad(180 + ($i * 90 / $segments));
                        $path = $path->line(
                            $x0 + $corner_radius + cos($angle) * $corner_radius,
                            $y0 + $corner_radius + sin($angle) * $corner_radius
                        );
                    }
                    $path = $path->close();
                }
            }
            return $path;
        });
    }

    protected static function getSoftEye()
    {
        return new DynamicEye(
            function () {
                return (new Path())
                    ->move(-3.5, 0.)
                    ->curve(-3.5, -3.5, -3.5, -3.5, 0., -3.5)
                    ->move(-3.5, 0.)
                    ->curve(-3.5, 3.5, -3.5, 3.5, 0, 3.5)
                    ->move(0, 3.5)
                    ->curve(3.5, 3.5, 3.5, 3.5, 3.5, 0)
                    ->move(3.5, 0.)
                    ->curve(3.5, -3.5, 3.5, -3.5, 0, -3.5)
                    ->move(3.5, 0)
                    ->ellipticArc(0., 0., 0., false, true, 0., 3.5)
                    ->ellipticArc(0., 0., 0., false, true, -3.5, 0.)
                    ->ellipticArc(0., 0., 0., false, true, 0., -3.5)
                    ->ellipticArc(0., 0., 0., false, true, 3.5, 0.)
                    ->close()
                    ->move(-2.5, 0.)
                    ->curve(-2.5, -2.5, -2.5, -2.5, 0., -2.5)
                    ->move(-2.5, 0.)
                    ->curve(-2.5, 2.5, -2.5, 2.5, 0, 2.5)
                    ->move(0, 2.5)
                    ->curve(2.5, 2.5, 2.5, 2.5, 2.5, 0)
                    ->move(2.5, 0.)
                    ->curve(2.5, -2.5, 2.5, -2.5, 0, -2.5)
                    ->move(2.5, 0)
                    ->ellipticArc(0., 0., 0., false, true, 0., 2.5)
                    ->ellipticArc(0., 0., 0., false, true, -2.5, 0.)
                    ->ellipticArc(0., 0., 0., false, true, 0., -2.5)
                    ->ellipticArc(0., 0., 0., false, true, 2.5, 0.)
                    ->close();
            },
            function () {
                return (new Path())
                    ->move(-1.5, 0.)
                    ->curve(-1.5, -1.5, -1.5, -1.5, 0., -1.5)
                    ->move(-1.5, 0.)
                    ->curve(-1.5, 1.5, -1.5, 1.5, 0, 1.5)
                    ->move(0, 1.5)
                    ->curve(1.5, 1.5, 1.5, 1.5, 1.5, 0)
                    ->move(1.5, 0.)
                    ->curve(1.5, -1.5, 1.5, -1.5, 0, -1.5)
                    ->move(1.5, 0)
                    ->ellipticArc(0., 0., 0., false, true, 0., 1.5)
                    ->ellipticArc(0., 0., 0., false, true, -1.5, 0.)
                    ->ellipticArc(0., 0., 0., false, true, 0., -1.5)
                    ->ellipticArc(0., 0., 0., false, true, 1.5, 0.)
                    ->close();
            }
        );
    }

    // -- Batch 6 --

    protected static function getDefenderEye()
    {
        return new DynamicEye(
            function () {
                return new Path(); // Empty external
            },
            function () {
                $path = new Path();
                $scale_factor = 0.6;
                $center_offset = 3.0;

                $convert = function (float $x, float $y) use ($scale_factor, $center_offset) {
                    return [
                        ($x - $center_offset) * $scale_factor,
                        ($y - $center_offset) * $scale_factor,
                    ];
                };

                [$start_x, $start_y] = $convert(1.0, 0.8);
                $path = $path->move($start_x, $start_y);

                [$ctrl1_x, $ctrl1_y] = $convert(2.3, 0.3);
                [$ctrl2_x, $ctrl2_y] = $convert(3.7, 0.3);
                [$top_right_x, $top_right_y] = $convert(5.0, 0.8);
                $path = $path->curve($ctrl1_x, $ctrl1_y, $ctrl2_x, $ctrl2_y, $top_right_x, $top_right_y);

                [$right_mid_x, $right_mid_y] = $convert(5.4, 3.1);
                $path = $path->line($right_mid_x, $right_mid_y);

                [$bottom_tip_x, $bottom_tip_y] = $convert(3.0, 5.4);
                $path = $path->line($bottom_tip_x, $bottom_tip_y);

                [$left_mid_x, $left_mid_y] = $convert(0.6, 3.1);
                $path = $path->line($left_mid_x, $left_mid_y);

                return $path->line($start_x, $start_y)->close();
            }
        );
    }

    protected static function getSparkleShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $scale = $size / 6.0;
            $margin = (1 - $size) / 2;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $ox = $x + $margin;
                    $oy = $y + $margin;

                    $path = $path->move($ox + 3.0 * $scale, $oy - 1.0 * $scale)
                        ->curve($ox + 3.0 * $scale, $oy + 1.2 * $scale, $ox + 4.8 * $scale, $oy + 3.0 * $scale, $ox + 7.0 * $scale, $oy + 3.0 * $scale)
                        ->curve($ox + 4.8 * $scale, $oy + 3.0 * $scale, $ox + 3.0 * $scale, $oy + 4.8 * $scale, $ox + 3.0 * $scale, $oy + 7.0 * $scale)
                        ->curve($ox + 3.0 * $scale, $oy + 4.8 * $scale, $ox + 1.2 * $scale, $oy + 3.0 * $scale, $ox - 1.0 * $scale, $oy + 3.0 * $scale)
                        ->curve($ox + 1.2 * $scale, $oy + 3.0 * $scale, $ox + 3.0 * $scale, $oy + 1.2 * $scale, $ox + 3.0 * $scale, $oy - 1.0 * $scale)
                        ->close();
                }
            }
            return $path;
        });
    }

    protected static function getSparkleEye()
    {
        return new DynamicEye(
            function () {
                return new Path(); // Empty external
            },
            function () {
                $path = new Path();
                $scale = 0.5;
                $offset = 3.0;
                $commands = [
                    ['move', [3.0, -1.0]],
                    ['curve', [3.0, 1.2, 4.8, 3.0, 7.0, 3.0]],
                    ['curve', [4.8, 3.0, 3.0, 4.8, 3.0, 7.0]],
                    ['curve', [3.0, 4.8, 1.2, 3.0, -1.0, 3.0]],
                    ['curve', [1.2, 3.0, 3.0, 1.2, 3.0, -1.0]],
                ];

                foreach ($commands as [$command, $coords]) {
                    if ($command === 'move') {
                        [$x, $y] = $coords;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = $path->move($x, $y);
                    } elseif ($command === 'curve') {
                        [$x1, $y1, $x2, $y2, $x, $y] = $coords;
                        $x1 = ($x1 - $offset) * $scale;
                        $y1 = ($y1 - $offset) * $scale;
                        $x2 = ($x2 - $offset) * $scale;
                        $y2 = ($y2 - $offset) * $scale;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = $path->curve($x1, $y1, $x2, $y2, $x, $y);
                    }
                }
                return $path->close();
            }
        );
    }

    protected static function getSquareSpacedShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $offset = (1 - $size) / 2;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $x_start = $x + $offset;
                    $y_start = $y + $offset;
                    $path = $path->move($x_start, $y_start)
                        ->line($x_start + $size, $y_start)
                        ->line($x_start + $size, $y_start + $size)
                        ->line($x_start, $y_start + $size)
                        ->close();
                }
            }
            return $path;
        });
    }

    protected static function getStellarShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $half_size = $size / 2;
            $margin = (1 - $size) / 2;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $path_x = $x + $margin;
                    $path_y = $y + $margin;
                    $cx = $path_x + $half_size;
                    $cy = $path_y + $half_size;
                    $radius = $half_size;
                    $angle = deg2rad(-90);
                    $points = [];

                    for ($i = 0; $i < 5; $i++) {
                        $outer_x = $cx + cos($angle) * $radius;
                        $outer_y = $cy + sin($angle) * $radius;
                        $angle += deg2rad(72);
                        $inner_x = $cx + cos($angle - deg2rad(36)) * ($radius * 0.5);
                        $inner_y = $cy + sin($angle - deg2rad(36)) * ($radius * 0.5);
                        $points[] = [$outer_x, $outer_y];
                        $points[] = [$inner_x, $inner_y];
                    }

                    $path = $path->move($points[0][0], $points[0][1]);
                    for ($i = 1; $i < count($points); $i++) {
                        $path = $path->line($points[$i][0], $points[$i][1]);
                    }
                    $path = $path->close();
                }
            }
            return $path;
        });
    }

    protected static function getStellarEye()
    {
        return new DynamicEye(
            function () {
                return new Path(); // Empty external
            },
            function () {
                $path = new Path();
                $scale = 0.5;
                $offset = 3.0;
                $commands = [
                    ['move', [3.2, 0.3]],
                    ['line', [3.8, 1.6]],
                    ['curve', [4.0, 1.8, 4.1, 1.9, 4.3, 1.9]],
                    ['line', [5.7, 2.1]],
                    ['curve', [5.9, 2.1, 6.0, 2.4, 5.9, 2.6]],
                    ['line', [4.9, 3.6]],
                    ['curve', [4.7, 3.8, 4.7, 4.0, 4.7, 4.2]],
                    ['line', [5.0, 5.5]],
                    ['curve', [5.0, 5.7, 4.8, 5.9, 4.6, 5.8]],
                    ['line', [3.3, 5.2]],
                    ['curve', [3.1, 5.1, 2.9, 5.1, 2.7, 5.2]],
                    ['line', [1.4, 5.8]],
                    ['curve', [1.2, 5.9, 1.0, 5.8, 1.0, 5.5]],
                    ['line', [1.2, 4.1]],
                    ['curve', [1.2, 3.9, 1.2, 3.7, 1.1, 3.6]],
                    ['line', [0.1, 2.6]],
                    ['curve', [-0.1, 2.4, 0.0, 2.2, 0.2, 2.1]],
                    ['line', [1.6, 1.9]],
                    ['curve', [1.8, 1.9, 2.0, 1.8, 2.1, 1.6]],
                    ['line', [2.7, 0.3]],
                    ['curve', [2.9, 0.1, 3.1, 0.1, 3.2, 0.3]],
                ];

                foreach ($commands as [$command, $coords]) {
                    if ($command === 'move') {
                        [$x, $y] = $coords;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = $path->move($x, $y);
                    } elseif ($command === 'line') {
                        [$x, $y] = $coords;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = $path->line($x, $y);
                    } elseif ($command === 'curve') {
                        [$x1, $y1, $x2, $y2, $x, $y] = $coords;
                        $x1 = ($x1 - $offset) * $scale;
                        $y1 = ($y1 - $offset) * $scale;
                        $x2 = ($x2 - $offset) * $scale;
                        $y2 = ($y2 - $offset) * $scale;
                        $x = ($x - $offset) * $scale;
                        $y = ($y - $offset) * $scale;
                        $path = $path->curve($x1, $y1, $x2, $y2, $x, $y);
                    }
                }
                return $path->close();
            }
        );
    }

    // -- Batch 7 --

    protected static function getSolarShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $scale = $size / 6.0;

            $points = [
                [3, 0],
                [3.4, 0.7],
                [4, 0.2],
                [4.1, 0.9],
                [4.9, 0.7],
                [4.8, 1.5],
                [5.6, 1.5],
                [5.2, 2.2],
                [5.9, 2.5],
                [5.3, 3],
                [5.9, 3.5],
                [5.2, 3.8],
                [5.6, 4.5],
                [4.8, 4.5],
                [4.9, 5.3],
                [4.1, 5.1],
                [4, 5.8],
                [3.4, 5.3],
                [3, 6],
                [2.5, 5.3],
                [1.9, 5.8],
                [1.8, 5.1],
                [1, 5.3],
                [1.1, 4.5],
                [0.4, 4.5],
                [0.7, 3.8],
                [0, 3.5],
                [0.6, 3],
                [0, 2.5],
                [0.7, 2.2],
                [0.4, 1.5],
                [1.1, 1.5],
                [1, 0.7],
                [1.8, 0.9],
                [1.9, 0.2],
                [2.5, 0.7]
            ];

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $offset_x = $x + (1 - $size) / 2;
                    $offset_y = $y + (1 - $size) / 2;

                    $first = true;
                    foreach ($points as [$px, $py]) {
                        $fx = $offset_x + $px * $scale;
                        $fy = $offset_y + $py * $scale;
                        if ($first) {
                            $path = $path->move($fx, $fy);
                            $first = false;
                        } else {
                            $path = $path->line($fx, $fy);
                        }
                    }
                    $path = $path->close();
                }
            }
            return $path;
        });
    }

    protected static function getSolarEye()
    {
        return new DynamicEye(
            function () {
                return new Path(); // Empty external
            },
            function () {
                $path = new Path();
                $scale = 0.5;
                $offset = 3.0;
                $points = [
                    [3, 0],
                    [3.4, 0.7],
                    [4, 0.2],
                    [4.1, 0.9],
                    [4.9, 0.7],
                    [4.8, 1.5],
                    [5.6, 1.5],
                    [5.2, 2.2],
                    [5.9, 2.5],
                    [5.3, 3],
                    [5.9, 3.5],
                    [5.2, 3.8],
                    [5.6, 4.5],
                    [4.8, 4.5],
                    [4.9, 5.3],
                    [4.1, 5.1],
                    [4, 5.8],
                    [3.4, 5.3],
                    [3, 6],
                    [2.5, 5.3],
                    [1.9, 5.8],
                    [1.8, 5.1],
                    [1, 5.3],
                    [1.1, 4.5],
                    [0.4, 4.5],
                    [0.7, 3.8],
                    [0, 3.5],
                    [0.6, 3],
                    [0, 2.5],
                    [0.7, 2.2],
                    [0.4, 1.5],
                    [1.1, 1.5],
                    [1, 0.7],
                    [1.8, 0.9],
                    [1.9, 0.2],
                    [2.5, 0.7]
                ];

                $first = true;
                foreach ($points as [$x, $y]) {
                    $x = ($x - $offset) * $scale;
                    $y = ($y - $offset) * $scale;
                    if ($first) {
                        $path = $path->move($x, $y);
                        $first = false;
                    } else {
                        $path = $path->line($x, $y);
                    }
                }
                return $path->close();
            }
        );
    }

    protected static function getDropShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $half_size = $size / 2;
            $margin = (1 - $size) / 2;
            $segments = 8;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $center_x = $x + $margin + $half_size;
                    $center_y = $y + $margin + $half_size;
                    $radius = $half_size;
                    $points = [];

                    for ($i = 0; $i <= $segments; $i++) {
                        $angle = ($i / $segments) * M_PI;
                        $points[] = [$center_x + cos($angle) * $radius, $center_y + sin($angle) * $radius];
                    }
                    $points[] = [$center_x, $center_y - $radius * 1.4];

                    $path = $path->move($points[0][0], $points[0][1]);
                    for ($i = 1; $i < count($points); $i++) {
                        $path = $path->line($points[$i][0], $points[$i][1]);
                    }
                    $path = $path->close();
                }
            }
            return $path;
        });
    }

    protected static function getStellarFatEye()
    {
        return new DynamicEye(
            function () {
                $path = new Path();
                $outer_radius = 3.5;
                $sides = 6;
                for ($i = 0; $i < $sides; $i++) {
                    $angle = deg2rad(60 * $i - 30);
                    $x = cos($angle) * $outer_radius;
                    $y = sin($angle) * $outer_radius;
                    if ($i === 0)
                        $path = $path->move($x, $y);
                    else
                        $path = $path->line($x, $y);
                }
                $path = $path->close();

                $inner_radius = 2.5;
                for ($i = 0; $i < $sides; $i++) {
                    $angle = deg2rad(60 * $i - 30);
                    $x = cos($angle) * $inner_radius;
                    $y = sin($angle) * $inner_radius;
                    if ($i === 0)
                        $path = $path->move($x, $y);
                    else
                        $path = $path->line($x, $y);
                }
                return $path->close();
            },
            function () {
                $path = new Path();
                $scale_factor = 0.6;
                $center_offset = 3.0;
                $convert = function (float $x, float $y) use ($scale_factor, $center_offset) {
                    return [
                        ($x - $center_offset) * $scale_factor,
                        ($y - $center_offset) * $scale_factor,
                    ];
                };

                $raw_points = [
                    [5.5, 3.0],
                    [4.56, 3.90],
                    [4.25, 5.17],
                    [3.00, 4.80],
                    [1.75, 5.17],
                    [1.44, 3.90],
                    [0.50, 3.00],
                    [1.44, 2.10],
                    [1.75, 0.83],
                    [3.00, 1.20],
                    [4.25, 0.83],
                    [4.56, 2.10],
                ];

                $is_first = true;
                foreach ($raw_points as [$x, $y]) {
                    [$nx, $ny] = $convert($x, $y);
                    if ($is_first) {
                        $path = $path->move($nx, $ny);
                        $is_first = false;
                    } else {
                        $path = $path->line($nx, $ny);
                    }
                }
                return $path->close();
            }
        );
    }

    protected static function getPyramidShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $half_size = $size / 2;
            $margin = (1 - $size) / 2;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $cx = $x + $margin + $half_size;
                    $cy = $y + $margin + $half_size;
                    $height_equilateral = sqrt(3) * $half_size;

                    $x1 = $cx;
                    $y1 = $cy - (2 / 3) * $height_equilateral;
                    $x2 = $cx - $half_size;
                    $y2 = $cy + (1 / 3) * $height_equilateral;
                    $x3 = $cx + $half_size;
                    $y3 = $cy + (1 / 3) * $height_equilateral;

                    $path = $path->move($x1, $y1)->line($x2, $y2)->line($x3, $y3)->close();
                }
            }
            return $path;
        });
    }

    // -- New Shapes --

    protected static function getCloudShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $scale = $size / 6.0;
            $margin = (1 - $size) / 2;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $ox = $x + $margin;
                    $oy = $y + $margin;

                    // Main circle
                    $path = $path->move($ox + 3.0 * $scale, $oy + 3.0 * $scale)
                        ->ellipticArc(2.0 * $scale, 2.0 * $scale, 0, false, true, $ox + 5.0 * $scale, $oy + 3.0 * $scale)
                        ->ellipticArc(2.0 * $scale, 2.0 * $scale, 0, false, true, $ox + 3.0 * $scale, $oy + 3.0 * $scale)
                        ->close();

                    // Left circle
                    $path = $path->move($ox + 2.0 * $scale, $oy + 4.0 * $scale)
                        ->ellipticArc(1.5 * $scale, 1.5 * $scale, 0, false, true, $ox + 3.5 * $scale, $oy + 4.0 * $scale)
                        ->ellipticArc(1.5 * $scale, 1.5 * $scale, 0, false, true, $ox + 2.0 * $scale, $oy + 4.0 * $scale)
                        ->close();

                    // Right circle
                    $path = $path->move($ox + 4.0 * $scale, $oy + 4.0 * $scale)
                        ->ellipticArc(1.5 * $scale, 1.5 * $scale, 0, false, true, $ox + 5.5 * $scale, $oy + 4.0 * $scale)
                        ->ellipticArc(1.5 * $scale, 1.5 * $scale, 0, false, true, $ox + 4.0 * $scale, $oy + 4.0 * $scale)
                        ->close();

                    // Top circle
                    $path = $path->move($ox + 3.0 * $scale, $oy + 1.5 * $scale)
                        ->ellipticArc(1.8 * $scale, 1.8 * $scale, 0, false, true, $ox + 4.8 * $scale, $oy + 1.5 * $scale)
                        ->ellipticArc(1.8 * $scale, 1.8 * $scale, 0, false, true, $ox + 3.0 * $scale, $oy + 1.5 * $scale)
                        ->close();
                }
            }
            return $path;
        });
    }

    protected static function getPillShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $margin = (1 - $size) / 2;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $ox = $x + $margin;
                    $oy = $y + $margin;

                    // Horizontal pill
                    $radius = $size / 4;
                    $path = $path->move($ox + $radius, $oy + $size / 4)
                        ->line($ox + $size - $radius, $oy + $size / 4)
                        ->ellipticArc($radius, $radius, 0, false, true, $ox + $size - $radius, $oy + $size * 0.75)
                        ->line($ox + $radius, $oy + $size * 0.75)
                        ->ellipticArc($radius, $radius, 0, false, true, $ox + $radius, $oy + $size / 4)
                        ->close();
                }
            }
            return $path;
        });
    }

    protected static function getBurstShape(float $size)
    {
        return new DynamicShape(function (ByteMatrix $matrix) use ($size) {
            $path = new Path();
            $width = $matrix->getWidth();
            $height = $matrix->getHeight();
            $half_size = $size / 2;
            $margin = (1 - $size) / 2;

            for ($y = 0; $y < $height; ++$y) {
                for ($x = 0; $x < $width; ++$x) {
                    if (!$matrix->get($x, $y)) {
                        continue;
                    }
                    $cx = $x + $margin + $half_size;
                    $cy = $y + $margin + $half_size;
                    $outer_radius = $half_size;
                    $inner_radius = $half_size * 0.6;
                    $points = 12;
                    $angle_step = deg2rad(360 / ($points * 2));

                    $start_angle = deg2rad(-90);
                    $path = $path->move($cx + cos($start_angle) * $outer_radius, $cy + sin($start_angle) * $outer_radius);

                    for ($i = 1; $i < $points * 2; $i++) {
                        $angle = $start_angle + $i * $angle_step;
                        $radius = ($i % 2 === 0) ? $outer_radius : $inner_radius;
                        $path = $path->line($cx + cos($angle) * $radius, $cy + sin($angle) * $radius);
                    }
                    $path = $path->close();
                }
            }
            return $path;
        });
    }
}
