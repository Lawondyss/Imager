<?php
/**
 * @package Imager
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager;

class ImageInfo extends \SplFileInfo
{

  /** @var null|\Imager\ImageInfo */
  private $source;

  /** @var int */
  private $width;

  /** @var int */
  private $height;

  /** @var int */
  private $type;

  /** @var string */
  private $mime;


  /**
   * @param string $imagePath
   */
  public function __construct($imagePath, ImageInfo $sourceImage = null)
  {
    parent::__construct($imagePath);

    $info = @\getimagesize($imagePath); // @ because error throw as exception
    if ($info === false) {
      $msg = sprintf('Something is wrong with image "%s".', $imagePath);
      throw new RuntimeException($msg);
    }

    $this->width = $info[0];
    $this->height = $info[1];
    $this->type = $info[2];
    $this->mime = $info['mime'];
    $this->source = $sourceImage;
  }


  /**
   * Returns width of image
   *
   * @return int
   */
  public function getWidth()
  {
    return $this->width;
  }


  /**
   * Returns height of image
   *
   * @return int
   */
  public function getHeight()
  {
    return $this->height;
  }


  /**
   * Returns type of image as a constants IMAGETYPE_...
   *
   * @return int
   */
  public function getType()
  {
    return $this->type;
  }


  /**
   * Returns MIME of image
   *
   * @return string
   */
  public function getMime()
  {
    return $this->mime;
  }


  /**
   * Returns source image
   *
   * @return null|\Imager\ImageInfo
   */
  public function getSource()
  {
    return $this->source;
  }


  /**
   * Returns is exists source
   *
   * @return bool
   */
  public function hasSource()
  {
    return isset($this->source);
  }

}
