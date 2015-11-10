<?php
/**
 * @package Imager
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager;

class ImageFactory
{

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
}
