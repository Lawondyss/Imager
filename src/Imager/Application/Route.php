<?php
/**
 * @package Imager\Application
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager\Application;

use Imager\ImageFactory;
use Imager\InvalidArgumentException;
use Imager\Repository;
use Nette\Application\Routers\Route as NRoute;
use Nette\Application\UI\Presenter;

class Route extends NRoute
{

  public function __construct(Repository $repository, ImageFactory $factory, $mask)
  {
    $metadata = [
        'presenter' => 'Nette:Micro',
        'callback' => function (Presenter $presenter) use ($repository, $factory) {
          $params = $presenter->request->parameters;

          if (!isset($params['id'])) {
            $msg = sprintf('Missing parameter "id", parameters "%s" given.', http_build_query($params, null, ', '));
            throw new InvalidArgumentException($msg);
          }

          $id = $params['id'];
          $width = isset($params['width']) ? $params['width'] : null;
          $height = isset($params['height']) ? $params['height'] : null;

          return new ImageResponse($repository, $factory, $id, $width, $height);
        },
    ];

    parent::__construct($mask, $metadata);
  }
}
