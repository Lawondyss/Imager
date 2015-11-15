<?php
/**
 * @package Imager
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager;

class ImageFactory
{

  /** @var bool */
  public static $showErrorImage;


  /**
   * Returns instance of \Imager\Image
   *
   * @param \Imager\ImageInfo $image
   * @return \Imager\Image
   */
  public function create(ImageInfo $image)
  {
    return new Image($image);
  }


  /**
   * Send error image to output
   *
   * @param int $width
   * @param int $height
   */
  public function sendErrorImage($width, $height)
  {
    if (self::$showErrorImage) {
      Image::errorImage($width, $height);
    }
  }
}
