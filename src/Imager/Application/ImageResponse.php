<?php
/**
 * @package Imager\Application
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager\Application;

use Imager\ImageFactory;
use Imager\Repository;
use Nette\Application\IResponse;
use Nette\Http\IRequest as HttpRequest;
use Nette\Http\IResponse as HttpResponse;
use Nette\Utils\Strings;

class ImageResponse implements IResponse
{

  /** @var \Imager\Repository */
  private $repository;

  /** @var \Imager\ImageFactory */
  private $factory;


  public function __construct(Repository $repository, ImageFactory $factory)
  {
    $this->repository = $repository;
    $this->factory = $factory;
  }


  public function send(HttpRequest $request, HttpResponse $response)
  {
    $width = null;
    $height = null;
    $quality = null;

    try {

      $url = $request->getUrl();

      $id = $url->getQueryParameter('id');
      $width = $url->getQueryParameter('width');
      $height = $url->getQueryParameter('height');
      $quality = $url->getQueryParameter('quality');

      $source = $this->repository->fetch($id);

      $thumb = $this->factory->create($source)->resize($width, $height, $quality);
      $thumb = $this->repository->save($thumb);

      $response->setContentType($thumb->getMime());
      $response->setHeader('Content-Length', $thumb->getSize());
      echo $thumb->getContent();

    } catch (\Exception $e) {
      $width = $width ?: 200;
      $height = $height ?: 200;
      $this->sendError($response, $e, $width, $height);
    }
  }


  /**
   * Send error to image
   *
   * @param \Nette\Http\IResponse $response
   * @param string|\Exception $error
   */
  private function sendError(HttpResponse $response, $error, $width, $height)
  {
    $response->setCode(HttpResponse::S500_INTERNAL_SERVER_ERROR);
    $response->setContentType('image/gif');

    if (!($error instanceof \Exception)) {
      $response->setHeader('X-Imager-Error-Message', $error);

    } else {
      $response->setHeader('X-Imager-Error-Message', get_class($error) . ': ' . $error->getMessage());

      // detailed information only in debug mode
      if ($this->factory->getDebugger()) {
        $response->setHeader('X-Imager-Error-File', $error->getFile() . ' (' . $error->getLine() . ')');

        $trace = $error->getTraceAsString();
        $trace = Strings::replace($trace, '~[\n|\n\r]~', '>>>');
        $response->setHeader('X-Imager-Error-Trace', $trace);
      }
    }

    $this->factory->sendErrorImage($width, $height);
  }
}
