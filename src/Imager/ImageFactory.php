<?php
/**
 * @package Imager
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager;

class ImageFactory
{

  /** @var bool */
  private $showErrorImage;

  /** @var bool */
  private $debugger;


  /**
   * @param bool $showErrorImage
   */
  public function setShowErrorImage($showErrorImage)
  {
    $this->showErrorImage = $showErrorImage;
  }


  /**
   * @param bool $debugger
   */
  public function setDebugger($debugger)
  {
    $this->debugger = (bool)$debugger;
  }


  /**
   * @return bool
   */
  public function getDebugger()
  {
    return $this->debugger;
  }



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
   * Returns instance of \Imager\ImageInfo
   *
   * @param string $imageName
   * @return \Imager\ImageInfo
   */
  public function createInfo($imageName)
  {
    return new ImageInfo($imageName);
  }


  /**
   * Send error image to output
   *
   * @param int $width
   * @param int $height
   */
  public function sendErrorImage($width, $height)
  {
    if ($this->showErrorImage) {
      Image::errorImage($width, $height);
    }
  }
}
