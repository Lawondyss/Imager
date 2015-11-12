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

}
