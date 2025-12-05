<?php

namespace RSSoftBD\QrCode\Support;

class Image
{
    /**
     * Holds the image resource.
     *
     * @var \GdImage|resource
     */
    protected $image;

    /**
     * Creates a new Image object.
     *
     * @param string $image An image string
     */
    public function __construct(string $image)
    {
        $this->image = imagecreatefromstring($image);
    }

    /**
     * Returns the width of an image
     *
     * @return int
     */
    public function getWidth()
    {
        return imagesx($this->image);
    }

    /**
     * Returns the height of an image
     *
     * @return int
     */
    public function getHeight()
    {
        return imagesy($this->image);
    }

    /**
     * Returns the image string.
     *
     * @return \GdImage|resource
     */
    public function getImageResource()
    {
        return $this->image;
    }

    /**
     * Sets the image string.
     *
     * @param \GdImage|resource $image
     */
    public function setImageResource($image)
    {
        $this->image = $image;
    }
}
