<?php
/**
 * @package Imager
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager;

use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\Utils\Strings;

class Helpers
{

  /**
   * @param array $arguments
   * @return array
   */
  public static function prepareArguments(array $arguments)
  {
    foreach ($arguments as $key => $value) {
      if ($key === 0 && !isset($arguments['id'])) {
        $arguments['id'] = $value ?: null;
        unset($arguments[$key]);

      } elseif ($key === 1 && !isset($arguments['width'])) {
        $arguments['width'] = $value;
        unset($arguments[$key]);

      } elseif ($key === 2 && !isset($arguments['height'])) {
        $arguments['height'] = $value;
        unset($arguments[$key]);
      } elseif ($key === 3 && !isset($arguments['quality'])) {
        $arguments['quality'] = $value;
        unset($arguments[$key]);
      }
    }

    return $arguments;
  }


  /**
   * @param \Nette\Application\IRouter $router
   * @param \Nette\Application\Routers\Route $route
   * @throws \Imager\InvalidArgumentException
   */
  public static function prependRouter(IRouter &$router, Route $route)
  {
    if (!$router instanceof RouteList) {
      $msg = sprintf('Router must be an instance of Nette\Application\Routers\RouterList, "%s" given.', get_class($router));
      throw new InvalidArgumentException($msg);
    }

    // adds route for correct number of routes
    $router[] = $route;
    $lastIndex = count($router) - 1;

    foreach ($router as $i => $mask) {
      // last route must be first
      if ($i == $lastIndex) {
        break;
      }
      $router[$i + 1] = $mask;
    }

    // adds route to first position
    $router[0] = $route;
  }


  /**
   * Returns sub-path for greater segmentation
   *
   * @param string $name
   * @return string
   */
  public static function getSubPath($name)
  {
    return Strings::substring($name, 0, 2) . DIRECTORY_SEPARATOR;
  }


  /**
   * Returns generated name for a image
   *
   * @param \Imager\ImageInfo $image
   * @return string
   */
  public static function createName(ImageInfo $image)
  {
    $source = $image->getSource() ?: $image;

    $id = $image->getParameter('id');

    if (!isset($id)) {
      $name = md5($source->getPathname());
    } else {
      $name = Strings::before($id, '.', -1);
    }

    // dimensions of image only for thumbnail (has source)
    $width = $image->getParameter('width');
    $height = $image->getParameter('height');
    $quality = $image->getParameter('quality');
    $dimensionName = self::createDimensionName($width, $height, $quality);

    if ($source->getExtension() !== '') {
      $ext = '.' . $source->getExtension();

    } else {
      $extensions = [
          IMAGETYPE_GIF => '.gif',
          IMAGETYPE_JPEG => '.jpg',
          IMAGETYPE_PNG => '.png',
          IMAGETYPE_BMP => '.bmp',
      ];

      if (!array_key_exists($source->getType(), $extensions)) {
        $msg = sprintf('Image "%s" is unsupported type.', $source->getFilename());
        throw new InvalidStateException($msg);
      }

      $ext = $extensions[$source->getType()];
    }

    $fileName = $name . $dimensionName . $ext;

    return $fileName;
  }


  /**
   * Returns part of name for dimension
   *
   * @param null|int $width
   * @param null|int $height
   * @param null|int $quality
   * @return string
   */
  public static function createDimensionName($width, $height, $quality)
  {
    $dimension = [
        (isset($width) || isset($height) || isset($quality)) ? '_' : '',
        $width,
        (isset($height) || isset($quality)) ? 'x' : '',
        $height,
        isset($quality) ? '-' : '',
        $quality,
    ];

    return implode('', $dimension);
  }


  /**
   * Returns parts of name
   *
   * @param string $name
   * @return array
   */
  public static function parseName($name)
  {
    $matches = Strings::match($name, '~^([^_]+)_?(\d*)x?(\d*)-?(\d*)(\.[a-z]+)$~i');

    if (isset($matches)) {
      list(, $name, $width, $height, $quality, $ext) = $matches;
    } else {
      $name = $width = $height = $quality = $ext = null;
    }

    return [
        'id' => $name . $ext,
        'width' => $width !== '' ? $width : null,
        'height' => $height !== '' ? $height : null,
        'quality' => $quality !== '' ? $quality : null,
    ];
  }
}
