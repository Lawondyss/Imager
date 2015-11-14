<?php
/**
 * @package Imager
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager;

class ImageFactory
{

  /** @var \Imager\Tracy\Panel */
  private $panel;


  /**
   * Returns instance of \Imager\Image
   *
   * @param \Imager\ImageInfo $image
   * @return \Imager\Image
   */
  public function create(ImageInfo $image)
  {
    $image = new Image($image);

    if (isset($this->panel)) {
      $image->injectPanel($this->panel);
    }

    return $image;
  }


  /**
   * @internal
   * @param \Imager\Tracy\Panel $panel
   */
  public function setPanel(Tracy\Panel $panel)
  {
    $this->panel = $panel;
  }
}
