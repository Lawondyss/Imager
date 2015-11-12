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

  /** @var string */
  private $id;

  /** @var null|int */
  private $width;

  /** @var null|int */
  private $height;


  public function __construct(Repository $repository, ImageFactory $factory, $id, $width, $height)
  {
    $this->repository = $repository;
    $this->factory = $factory;
    $this->id = $id;
    $this->width = $width;
    $this->height = $height;
  }


  public function send(HttpRequest $request, HttpResponse $response)
  {
    try {
      $source = $this->repository->fetch($this->id);

      $thumb = $this->factory->create($source)->resize($this->width, $this->height);
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
