<?php
/**
 * @author Ladislav Vondráček <lad.von@gmail.com>
 * @package Tests
 */

namespace Imager;

require_once __DIR__ . '/bootstrap.php';

use Imager\Application\Route;
use Nette;
use Tester;
use Tester\Assert;

class HelpersTest extends Tester\TestCase
{

  /**
   * @dataProvider dataPrepareArguments
   */
  public function testPrepareArguments($arguments, $expected)
  {
    Assert::same($expected, Helpers::prepareArguments($arguments));
  }


  public function dataPrepareArguments()
  {
    return [
        [[], []],
        [['id', 'width', 'height', 'quality'], ['id' => 'id', 'width' => 'width', 'height' => 'height', 'quality' => 'quality']],
        [['id' => 'id', 'width' => 'width', 'height' => 'height', 'quality' => 'quality'], ['id' => 'id', 'width' => 'width', 'height' => 'height', 'quality' => 'quality']],
        [['quality' => 'quality', 'id', 'width', 'height'], ['quality' => 'quality', 'id' => 'id', 'width' => 'width', 'height' => 'height']],
    ];
  }


  public function testPrependRouter()
  {
    $router = new Nette\Application\Routers\RouteList;
    $router[] = new Nette\Application\Routers\SimpleRouter;
    $router[] = new Nette\Application\Routers\SimpleRouter;

    $route = new Nette\Application\Routers\Route('');

    Helpers::prependRouter($router, $route);

    Assert::same($route, $router[0]);
  }


  /**
   * @dataProvider dataGetSubPath
   */
  public function testGetSubPath($name, $expected)
  {
    Assert::same($expected, Helpers::getSubPath($name));
  }


  public function dataGetSubPath()
  {
    return [
        ['lipsum', 'li/'],
        [123, '12/'],
        [null, '/'],
        [2.3, '2./'],
        [true, '1/'],
        [false, '/'],
    ];
  }


  public function testCreateName()
  {
    $imageInfo = new ImageInfo(ASSETS . IMAGE);
    Assert::contains('.jpg', Helpers::createName($imageInfo));

    $imageInfo->setParameter('width', 100);
    Assert::contains('_100.jpg', Helpers::createName($imageInfo));

    $imageInfo->setParameter('height', 100);
    Assert::contains('_100x100.jpg', Helpers::createName($imageInfo));

    $imageInfo->setParameter('quality', '20');
    Assert::contains('_100x100-20.jpg', Helpers::createName($imageInfo));

    $imageInfo->setParameter('id', 'name.jpg');
    Assert::same('name_100x100-20.jpg', Helpers::createName($imageInfo));
  }


  /**
   * @dataProvider dataCreateDimensionName
   */
  public function testCreateDimensionName($width, $height, $quality, $expected)
  {
    Assert::same($expected, Helpers::createDimensionName($width, $height, $quality));
  }


  public function dataCreateDimensionName()
  {
    return [
        [null, null, null, ''],
        [null, null, 20, '_x-20'],
        [null, 100, 20, '_x100-20'],
        [120, 100, 20, '_120x100-20'],
        [120, 100, null, '_120x100'],
        [120, null, null, '_120'],
    ];
  }


  /**
   * @dataProvider dataParseName
   */
  public function testParseName($name, $expected)
  {
    Assert::same($expected, Helpers::parseName($name));
  }


  public function dataParseName()
  {
    return [
        [null, ['id' => '', 'width' => null, 'height' => null, 'quality' => null]],
        ['', ['id' => '', 'width' => null, 'height' => null, 'quality' => null]],
        ['name.jpg', ['id' => 'name.jpg', 'width' => null, 'height' => null, 'quality' => null]],
        ['name_120.jpg', ['id' => 'name.jpg', 'width' => '120', 'height' => null, 'quality' => null]],
        ['name_120x100.jpg', ['id' => 'name.jpg', 'width' => '120', 'height' => '100', 'quality' => null]],
        ['name_120x100-20.jpg', ['id' => 'name.jpg', 'width' => '120', 'height' => '100', 'quality' => '20']],
        ['name_x100-20.jpg', ['id' => 'name.jpg', 'width' => null, 'height' => '100', 'quality' => '20']],
        ['name_x-20.jpg', ['id' => 'name.jpg', 'width' => null, 'height' => null, 'quality' => '20']],
        ['name-x_20.jpg', ['id' => 'name-x.jpg', 'width' => '20', 'height' => null, 'quality' => null]],
    ];
  }

}

(new HelpersTest())->run();
