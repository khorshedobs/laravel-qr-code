<?php

namespace RSSoftBD\QrCode\Shapes;

use BaconQrCode\Encoder\ByteMatrix;
use BaconQrCode\Renderer\Path\Path;
use BaconQrCode\Renderer\Module\ModuleInterface;

class DynamicShape implements ModuleInterface
{
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function createPath(ByteMatrix $matrix): Path
    {
        return ($this->callback)($matrix);
    }
}
