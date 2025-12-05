<?php

namespace RSSoftBD\QrCode;

use RSSoftBD\QrCode\Payloads\PayloadInterface;
use RSSoftBD\QrCode\Support\Image;
use RSSoftBD\QrCode\Support\ImageHandler;
use RSSoftBD\QrCode\Support\PayloadRegistry;
use RSSoftBD\QrCode\Support\StyleRegistry;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Exception\WriterException;
use BaconQrCode\Renderer\Color\Alpha;
use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Eye\EyeInterface;
use BaconQrCode\Renderer\Image\EpsImageBackEnd;
use BaconQrCode\Renderer\Image\ImageBackEndInterface;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Module\ModuleInterface;
use BaconQrCode\Renderer\RendererStyle\EyeFill;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\Gradient;
use BaconQrCode\Renderer\RendererStyle\GradientType;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use BadMethodCallException;
use InvalidArgumentException;

class QrGenerator
{
    /**
     * The output format (svg, png, eps).
     *
     * @var string
     */
    protected string $outputFormat = 'svg';

    /**
     * The size of the QR code in pixels.
     *
     * @var int
     */
    protected int $sizeInPixels = 100;

    /**
     * The margin around the QR code.
     *
     * @var int
     */
    protected int $margin = 0;

    /**
     * The error correction level.
     *
     * @var ErrorCorrectionLevel|null
     */
    protected ?ErrorCorrectionLevel $errorCorrectionLevel = null;

    /**
     * The encoding mode.
     *
     * @var string
     */
    protected string $encoding = Encoder::DEFAULT_BYTE_MODE_ECODING;

    /**
     * The module (block) style.
     *
     * @var string
     */
    protected string $moduleStyle = 'square';

    /**
     * The size of the module style (0.0 to 1.0).
     *
     * @var float
     */
    protected float $moduleStyleSize = 0.5;

    /**
     * The eye style.
     *
     * @var EyeInterface|string|null
     */
    protected $eyeStyle = null;

    /**
     * The foreground color.
     *
     * @var ColorInterface|null
     */
    protected ?ColorInterface $foregroundColor = null;

    /**
     * The background color.
     *
     * @var ColorInterface|null
     */
    protected ?ColorInterface $backgroundColor = null;

    /**
     * The eye colors.
     *
     * @var array
     */
    protected array $eyeColors = [];

    /**
     * The gradient configuration.
     *
     * @var Gradient|null
     */
    protected ?Gradient $gradient = null;

    /**
     * The image to overlay on the QR code.
     *
     * @var string|null
     */
    protected ?string $overlayImageContent = null;

    /**
     * The scale of the overlay image (0.0 to 1.0).
     *
     * @var float
     */
    protected float $overlayImageScale = 0.2;

    /**
     * Magic method to handle payload creation calls.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        $payload = $this->resolvePayload($method);
        $payload->create($arguments);

        return $this->render((string) $payload);
    }

    /**
     * Render the QR code.
     *
     * @param string $content
     * @param string|null $filename
     * @return mixed
     * @throws WriterException
     */
    public function render(string $content, ?string $filename = null)
    {
        $renderer = new ImageRenderer(
            $this->buildRendererStyle(),
            $this->buildImageBackend()
        );

        $writer = new Writer($renderer);
        $qrCodeData = $writer->writeString($content, $this->encoding, $this->errorCorrectionLevel);

        if ($this->overlayImageContent !== null && $this->outputFormat === 'png') {
            $merger = new ImageHandler(new Image($qrCodeData), new Image($this->overlayImageContent));
            $qrCodeData = $merger->merge($this->overlayImageScale);
        }

        if ($filename) {
            file_put_contents($filename, $qrCodeData);
            return null;
        }

        if (class_exists(\Illuminate\Support\HtmlString::class)) {
            return new \Illuminate\Support\HtmlString($qrCodeData);
        }

        return $qrCodeData;
    }

    /**
     * Set the dimensions (size) of the QR code.
     *
     * @param int $pixels
     * @return self
     */
    public function setDimensions(int $pixels): self
    {
        $this->sizeInPixels = $pixels;
        return $this;
    }

    /**
     * Set the output format.
     *
     * @param string $format
     * @return self
     * @throws InvalidArgumentException
     */
    public function setOutputFormat(string $format): self
    {
        if (!in_array($format, ['svg', 'eps', 'png'])) {
            throw new InvalidArgumentException("Invalid format '{$format}'. Must be svg, eps, or png.");
        }

        $this->outputFormat = $format;
        return $this;
    }

    /**
     * Set the foreground color.
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int|null $alpha
     * @return self
     */
    public function setForegroundColor(int $red, int $green, int $blue, ?int $alpha = null): self
    {
        $this->foregroundColor = $this->makeColor($red, $green, $blue, $alpha);
        return $this;
    }

    /**
     * Set the background color.
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int|null $alpha
     * @return self
     */
    public function setBackgroundColor(int $red, int $green, int $blue, ?int $alpha = null): self
    {
        $this->backgroundColor = $this->makeColor($red, $green, $blue, $alpha);
        return $this;
    }

    /**
     * Set the color for a specific eye.
     *
     * @param int $eyeIndex
     * @param int $innerRed
     * @param int $innerGreen
     * @param int $innerBlue
     * @param int $innerAlpha
     * @param int $outerRed
     * @param int $outerGreen
     * @param int $outerBlue
     * @param int $outerAlpha
     * @return self
     * @throws InvalidArgumentException
     */
    public function setEyeColor(
        int $eyeIndex,
        int $innerRed,
        int $innerGreen,
        int $innerBlue,
        int $innerAlpha = 100,
        int $outerRed = 0,
        int $outerGreen = 0,
        int $outerBlue = 0,
        int $outerAlpha = 100
    ): self {
        if ($eyeIndex < 0 || $eyeIndex > 2) {
            throw new InvalidArgumentException("Eye index must be 0, 1, or 2.");
        }

        $this->eyeColors[$eyeIndex] = new EyeFill(
            $this->makeColor($innerRed, $innerGreen, $innerBlue, $innerAlpha),
            $this->makeColor($outerRed, $outerGreen, $outerBlue, $outerAlpha)
        );

        return $this;
    }

    /**
     * Set a gradient for the foreground.
     *
     * @param int $startRed
     * @param int $startGreen
     * @param int $startBlue
     * @param int $endRed
     * @param int $endGreen
     * @param int $endBlue
     * @param string $type
     * @return self
     */
    public function setGradient(
        int $startRed,
        int $startGreen,
        int $startBlue,
        int $endRed,
        int $endGreen,
        int $endBlue,
        string $type
    ): self {
        $typeMethod = strtoupper($type);

        // Check if GradientType method exists, default to something safe if not?
        // Assuming valid types are passed for now or BaconQrCode throws.

        $this->gradient = new Gradient(
            $this->makeColor($startRed, $startGreen, $startBlue),
            $this->makeColor($endRed, $endGreen, $endBlue),
            GradientType::$typeMethod()
        );

        return $this;
    }

    /**
     * Set the module (block) style.
     *
     * @param string $style
     * @param float $size
     * @return self
     * @throws InvalidArgumentException
     */
    public function setModuleStyle(string $style, float $size = 0.5): self
    {
        if ($size < 0 || $size >= 1) {
            throw new InvalidArgumentException("Style size must be between 0 and 1.");
        }

        $this->moduleStyle = $style;
        $this->moduleStyleSize = $size;
        return $this;
    }

    /**
     * Set the eye style.
     *
     * @param string|EyeInterface $style
     * @return self
     */
    public function setEyeStyle($style): self
    {
        $this->eyeStyle = $style;
        return $this;
    }

    /**
     * Set the encoding.
     *
     * @param string $encoding
     * @return self
     */
    public function setEncoding(string $encoding): self
    {
        $this->encoding = strtoupper($encoding);
        return $this;
    }

    /**
     * Set the error correction level.
     *
     * @param string $level
     * @return self
     */
    public function setErrorCorrectionLevel(string $level): self
    {
        $level = strtoupper($level);
        $this->errorCorrectionLevel = ErrorCorrectionLevel::$level();
        return $this;
    }

    /**
     * Set the margin.
     *
     * @param int $margin
     * @return self
     */
    public function setMargin(int $margin): self
    {
        $this->margin = $margin;
        return $this;
    }

    /**
     * Overlay an image on top of the QR code.
     *
     * @param string $path
     * @param float $scale
     * @param bool $isAbsolute
     * @return self
     */
    public function overlayImage(string $path, float $scale = 0.2, bool $isAbsolute = false): self
    {
        if (function_exists('base_path') && !$isAbsolute) {
            $path = base_path($path);
        }

        $this->overlayImageContent = file_get_contents($path);
        $this->overlayImageScale = $scale;

        return $this;
    }

    /**
     * Overlay raw image string content.
     *
     * @param string $content
     * @param float $scale
     * @return self
     */
    public function overlayImageString(string $content, float $scale = 0.2): self
    {
        $this->overlayImageContent = $content;
        $this->overlayImageScale = $scale;
        return $this;
    }

    /**
     * Build the renderer style object.
     *
     * @return RendererStyle
     */
    protected function buildRendererStyle(): RendererStyle
    {
        return new RendererStyle(
            $this->sizeInPixels,
            $this->margin,
            $this->resolveModule(),
            $this->resolveEye(),
            $this->buildFill()
        );
    }

    /**
     * Build the image backend.
     *
     * @return ImageBackEndInterface
     */
    protected function buildImageBackend(): ImageBackEndInterface
    {
        return match ($this->outputFormat) {
            'png' => new ImagickImageBackEnd('png'),
            'eps' => new EpsImageBackEnd(),
            default => new SvgImageBackEnd(),
        };
    }

    /**
     * Resolve the module implementation.
     *
     * @return ModuleInterface
     */
    protected function resolveModule(): ModuleInterface
    {
        return StyleRegistry::getShape($this->moduleStyle, $this->moduleStyleSize);
    }

    /**
     * Resolve the eye implementation.
     *
     * @return EyeInterface
     */
    protected function resolveEye(): EyeInterface
    {
        if ($this->eyeStyle instanceof EyeInterface) {
            return $this->eyeStyle;
        }

        if (is_string($this->eyeStyle)) {
            return StyleRegistry::getEye($this->eyeStyle);
        }

        return \BaconQrCode\Renderer\Eye\SquareEye::instance();
    }

    /**
     * Build the fill configuration.
     *
     * @return Fill
     */
    protected function buildFill(): Fill
    {
        $foreground = $this->foregroundColor ?? new Rgb(0, 0, 0);
        $background = $this->backgroundColor ?? new Rgb(255, 255, 255);

        $eye0 = $this->eyeColors[0] ?? EyeFill::inherit();
        $eye1 = $this->eyeColors[1] ?? EyeFill::inherit();
        $eye2 = $this->eyeColors[2] ?? EyeFill::inherit();

        if ($this->gradient) {
            return Fill::withForegroundGradient($background, $this->gradient, $eye0, $eye1, $eye2);
        }

        return Fill::withForegroundColor($background, $foreground, $eye0, $eye1, $eye2);
    }

    /**
     * Create a color object.
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int|null $alpha
     * @return ColorInterface
     */
    protected function makeColor(int $red, int $green, int $blue, ?int $alpha = null): ColorInterface
    {
        if ($alpha === null) {
            return new Rgb($red, $green, $blue);
        }

        return new Alpha($alpha, new Rgb($red, $green, $blue));
    }

    /**
     * Resolve a payload class from the registry.
     *
     * @param string $method
     * @return PayloadInterface
     */
    protected function resolvePayload(string $method): PayloadInterface
    {
        $class = PayloadRegistry::getClass($method);

        if (!$class || !class_exists($class)) {
            throw new BadMethodCallException("Payload method '{$method}' not found.");
        }

        return new $class();
    }
}
