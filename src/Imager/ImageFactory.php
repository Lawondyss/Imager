<?php
/**
 * @package Imager
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager;

use Imager\Latte\Macro;
use Nette\Application;

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
   * Returns URL for thumbnail of image
   *
   * @param \Nette\Application\IPresenter $presenter
   * @param string $imageName
   * @param null|int $width
   * @param null|int $height
   * @return string
   * @throws \Imager\RuntimeException
   */
  public function createLink(Application\IPresenter $presenter, $imageName, $width = null, $height = null)
  {
    // Parameters $presenter and $baseUrl it's in code for eval
    // Their presence here enables functionality

    $baseUrl = $presenter->context->getService('http.request')->getUrl()->baseUrl;

    // code with parameters for eval
    $parameters = sprintf('["%s", %s, %s]', $imageName, $width ?: 'null', $height ?: 'null');

    $code = Macro::getCode($parameters);

    // for get link must be result returns
    $code[] = 'return $link;';

    $link = @eval(implode('', $code)); // @ escalates to exception
    if (!isset($link) || $link === false) {
      $msg = sprintf('Error on code for eval(): %s', implode(' ', $code));
      throw new RuntimeException($msg);
    }

    return $link;
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
