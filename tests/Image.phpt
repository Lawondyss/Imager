<?php
/**
 * @author Ladislav Vondráček <lad.von@gmail.com>
 * @package Tests
 */

namespace Tests;

require_once __DIR__ . '/bootstrap.php';

use Imager\Image;
use Imager\ImageInfo;
use Nette;
use Tester;
use Tester\Assert;

class ImageTest extends Tester\TestCase
{

  /** @var \Imager\Image */
  private $image;


  /**/
  protected function setUp()
  {
    $imageInfo = new ImageInfo(ASSETS . IMAGE);
    $this->image = new Image($imageInfo);
  }


  public function testExceptions()
  {
    Assert::exception(function() {
      $this->image->resize(-100, null);
    }, \Imager\InvalidArgumentException::class);
  }


  public function testResizeWidth()
  {
    $result = $this->image->resize(null, 100);
    Assert::type(\Imager\ImageInfo::class, $result);
    Assert::same(75, $result->getWidth());

    $result = $this->image->resize(100, 100);
    Assert::type(\Imager\ImageInfo::class, $result);
    Assert::same(100, $result->getWidth());

    $result = $this->image->resize(100);
    Assert::type(\Imager\ImageInfo::class, $result);
    Assert::same(100, $result->getWidth());

    $result = $this->image->resize(0);
    Assert::type(\Imager\ImageInfo::class, $result);
    Assert::same(960, $result->getWidth());

    $result = $this->image->resize('100%');
    Assert::type(\Imager\ImageInfo::class, $result);
    Assert::same(960, $result->getWidth());
  }


  public function testResizeHeight()
  {
    $result = $this->image->resize(100);
    Assert::type(\Imager\ImageInfo::class, $result);
    Assert::same(133, $result->getHeight());

    $result = $this->image->resize(null, 100);
    Assert::type(\Imager\ImageInfo::class, $result);
    Assert::same(100, $result->getHeight());

    $result = $this->image->resize(100, 100);
    Assert::type(\Imager\ImageInfo::class, $result);
    Assert::same(100, $result->getHeight());

    $result = $this->image->resize(null, 0);
    Assert::type(\Imager\ImageInfo::class, $result);
    Assert::same(1280, $result->getHeight());

    $result = $this->image->resize(null, '100%');
    Assert::type(\Imager\ImageInfo::class, $result);
    Assert::same(1280, $result->getHeight());
  }
}

(new ImageTest())->run();
