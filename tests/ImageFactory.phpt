<?php
/**
 * @author Ladislav Vondráček <lad.von@gmail.com>
 * @package Tests
 */

namespace Tests;

require_once __DIR__ . '/bootstrap.php';

use Imager\ImageFactory;
use Imager\ImageInfo;
use Nette;
use Tester;
use Tester\Assert;

class ImageFactoryTest extends Tester\TestCase
{

  public function testCreate()
  {
    $factory = new ImageFactory;
    Assert::type(\Imager\ImageFactory::class, $factory);

    $imageInfo = new ImageInfo(ASSETS . IMAGE);
    $result = $factory->create($imageInfo);
    Assert::type(\Imager\Image::class, $result);
  }

}

(new ImageFactoryTest())->run();
