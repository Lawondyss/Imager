<?php
/**
 * @author Ladislav Vondráček <lad.von@gmail.com>
 * @package Tests
 */

namespace Tests;

require_once __DIR__ . '/bootstrap.php';

use Imager\ImageInfo;
use Nette;
use Tester;
use Tester\Assert;

class ImageInfoTest extends Tester\TestCase
{

  public function testCreate()
  {
    Assert::exception(function () {
      new ImageInfo('non-exists-image.jpg');
    }, \Imager\RuntimeException::class);

    Assert::exception(function () {
      new ImageInfo(ASSETS . NON_IMAGE);
    }, \Imager\RuntimeException::class);

    Assert::type(\Imager\ImageInfo::class, new ImageInfo(ASSETS . IMAGE));
  }


  public function testProperties()
  {
    $result = new ImageInfo(ASSETS . IMAGE);

    Assert::same(960, $result->getWidth());
    Assert::same(1280, $result->getHeight());
    Assert::same(2, $result->getType());
    Assert::same('image/jpeg', $result->getMime());
    Assert::same(IMAGE, $result->getFilename());
    Assert::null($result->getSource());
  }


  public function testSource()
  {
    $source = new ImageInfo(ASSETS . IMAGE);
    $result = new ImageInfo(ASSETS . IMAGE, $source);

    Assert::type(\Imager\ImageInfo::class, $result->getSource());
    Assert::same(960, $result->getSource()->getWidth());
    Assert::same(1280, $result->getSource()->getHeight());
    Assert::same(2, $result->getSource()->getType());
    Assert::same('image/jpeg', $result->getSource()->getMime());
    Assert::same(IMAGE, $result->getSource()->getFilename());
    Assert::null($result->getSource()->getSource());
  }

}

(new ImageInfoTest())->run();
