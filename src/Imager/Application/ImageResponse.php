<?php
/**
 * @package Imager\Application
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager\Application;

use Imager\ImageFactory;
use Imager\NotExistsException;
use Imager\Repository;
use Nette\Application\IResponse;
use Nette\Http\IRequest as HttpRequest;
use Nette\Http\IResponse as HttpResponse;

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
    try {
      $url = $request->getUrl();

      $id = $url->getQueryParameter('id');
      $width = $url->getQueryParameter('width');
      $height = $url->getQueryParameter('height');

      $source = $this->repository->fetch($id);

      $thumb = $this->factory->create($source)->resize($width, $height);
      $thumb = $this->repository->save($thumb);

      $response->setContentType($thumb->getMime());
      $response->setHeader('Content-Length', $thumb->getSize());
      echo $thumb->getContent();

    } catch (\Exception $e) {
      $this->sendError($response, $e);
    }
  }


  /**
   * Send error to image header
   *
   * @param \Nette\Http\IResponse $response
   * @param string|\Exception $error
   */
  private function sendError(HttpResponse $response, $error)
  {
    $error = ($error instanceof \Exception) ? $error->getMessage() : $error;

    $response->setCode(HttpResponse::S500_INTERNAL_SERVER_ERROR);
    $response->setContentType('image/gif');
    $response->setHeader('Error-Message', $error);
  }
}
