<?php

namespace RSSoftBD\QrCode\Shapes;

use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Path\Path;

class DynamicEye implements EyeInterface
{
    protected $externalCallback;
    protected $internalCallback;

    public function __construct(callable $externalCallback, callable $internalCallback)
    {
        $this->externalCallback = $externalCallback;
        $this->internalCallback = $internalCallback;
    }

    public function getExternalPath(): Path
    {
        return ($this->externalCallback)();
    }

    public function getInternalPath(): Path
    {
        return ($this->internalCallback)();
    }
}
