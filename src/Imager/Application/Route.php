<?php
/**
 * @package Imager\Application
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager\Application;

use Imager;
use Nette\Application;
use Nette\Http;
use Nette\Utils\Strings;

class Route extends Application\Routers\Route
{

  /** @var \Imager\Repository */
  private $repository;

  /** @var \Imager\ImageFactory */
  private $imageFactory;

  /** @var string */
  private $basePath;


  public function __construct(Imager\Repository $repository, Imager\ImageFactory $imageFactory, $basePath)
  {
    $this->repository = $repository;
    $this->imageFactory = $imageFactory;
    $this->basePath = rtrim($basePath, '/') . '/';
  }


  public function match(Http\IRequest $request)
  {
    $url = $request->getUrl();

    if (Strings::contains($url->path, $this->basePath) === false) {
      return;
    }

    $imgUrl = Strings::after($url->path, $this->basePath);
    $imgUrl = Strings::after($imgUrl, '/', -1);
    $matches = Strings::match($imgUrl, '~^([^_]+)_?(\d*)x?(\d*)-?(\d*)(\.[a-z]+)$~i');

    if (!isset($matches)) {
      return;
    }
    list(, $name, $width, $height, $quality, $ext) = $matches;

    $id = $name . $ext;
    $height = $height !== '' ? $height : null;
    // if not defined width and height, then default size is original
    $width = $width !== '' ? $width : (!isset($height) ? 0 : null);
    $quality = $quality !== '' ? $quality : null;

    $url->setQueryParameter('id', $id)
        ->setQueryParameter('width', $width)
        ->setQueryParameter('height', $height)
        ->setQueryParameter('quality', $quality);

    $response = new ImageResponse($this->repository, $this->imageFactory);
    $response->send(new Http\Request($url), new Http\Response);
  }


  public function constructUrl(Application\Request $request, Http\Url $url)
  {
    if ($request->getPresenterName() !== 'Nette:Micro') {
      return;
    }

    // parameter "id" will be name in string or instance of ImageInfo
    $id = $request->getParameter('id');
    if ($id instanceof Imager\ImageInfo) {
      $id = $id->getFilename();
    }

    $parts = explode('.', $id);
    $extension = '.' . array_pop($parts);
    $name = implode('.', $parts);
    $width = $request->getParameter('width');
    $height = $request->getParameter('height');
    $quality = $request->getParameter('quality');

    $dimension = [
        (isset($width) || isset($height) || isset($quality)) ? '_' : '',
      $width,
        (isset($height) || isset($quality)) ? 'x' : '',
      $height,
        (isset($quality)) ? '-' : '',
      $quality,
    ];

    $dimension = implode('', $dimension);

    return $this->basePath . Imager\Helpers::getSubPath($id) . $name . $dimension . $extension;
  }
}
