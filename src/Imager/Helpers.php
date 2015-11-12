<?php
/**
 * @package Imager
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager;

use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

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
      }
    }

    if (!isset($arguments['width'])) {
      $arguments['width'] = 0;
    }
    if (!isset($arguments['height'])) {
      $arguments['height'] = 0;
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

    $tmpRouter = [$route];
    foreach ($router as $mask) {
      $tmpRouter[] = $mask;
    }
    $router = $tmpRouter;
  }

}
