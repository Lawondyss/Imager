<?php
/**
 * @package Imager
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;

class Repository
{

  /** @var string */
  private $sourcesDirectory;

  /** @var null|string */
  private $thumbnailsDirectory;


  /**
   * @param string $sourcesDirectory
   * @param null|string $thumbnailsDirectory
   * @throws \Imager\NotExistsException
   * @throws \Imager\BadPermissionException
   */
  public function __construct($sourcesDirectory, $thumbnailsDirectory = null)
  {
    $this->setSourcesDirectory($sourcesDirectory);

    // if not thumbnails directory defined, then directory of sources is together thumbnails directory
    $thumbnailsDirectory = $thumbnailsDirectory ?: $sourcesDirectory;
    $this->setThumbnailsDirectory($thumbnailsDirectory);
  }


  /**
   * Returns instance of ImageInfo about fetched image
   *
   * @param string $name
   * @return \Imager\ImageInfo
   * @throws \Imager\InvalidArgumentException
   * @throws \Imager\NotExistsException
   */
  public function fetch($name)
  {
    if (!isset($name) || Strings::length($name) === 0) {
      throw new InvalidArgumentException('Name of fetched file cannot be empty.');
    }

    $source = $this->getSourcePath($name);

    if (!file_exists($source)) {
      $msg = sprintf('Source image "%s" not exists.', $source);
      throw new NotExistsException($msg);
    }

    return new ImageInfo($source);
  }


  /**
   * Save (copy and remove) image to new image
   *
   * @param \Imager\ImageInfo $image
   * @param null|string $name
   * @return \Imager\ImageInfo
   */
  public function save(ImageInfo $image, $name = null)
  {
    // thumbnail has source
    if ($image->hasSource()) {
      $imageInfo = $this->saveThumbnail($image, $name);
    } else {
      $imageInfo = $this->saveSource($image, $name);
    }

    return $imageInfo;
  }


  /**
   * Save (copy and remove) image to new image in source directory
   *
   * @param \Imager\ImageInfo $image
   * @param null|string $name
   * @return \Imager\ImageInfo
   */
  public function saveSource(ImageInfo $image, $name = null)
  {
    $name = $name ?: $this->makeName($image);
    $target = $this->getSourcePath($name);

    FileSystem::rename($image->getPathname(), $target);

    return new ImageInfo($target);
  }


  /**
   * Save (copy and remove) image to new image in target directory
   *
   * @param \Imager\ImageInfo $image
   * @param null|string $name
   * @return \Imager\ImageInfo
   */
  public function saveThumbnail(ImageInfo $image, $name = null)
  {
    $name = $name ?: $this->makeName($image);
    $target = $this->getThumbnailPath($name);

    FileSystem::rename($image->getPathname(), $target);

    return new ImageInfo($target);
  }


  /**
   * Sets directory with sources images
   *
   * @param string $sourcesDirectory
   * @throws \Imager\NotExistsException
   */
  private function setSourcesDirectory($sourcesDirectory)
  {
    $sourcesDirectory = Strings::trim($sourcesDirectory);
    $sourcesDirectory = rtrim($sourcesDirectory, '\\/');

    if (!is_dir($sourcesDirectory)) {
      $msg = sprintf('Directory "%s" with sources not exists.', $sourcesDirectory);
      throw new NotExistsException($msg);
    }

    $this->sourcesDirectory = $sourcesDirectory . DIRECTORY_SEPARATOR;
  }


  /**
   * Sets directory with thumbnails
   *
   * @param string $targetDirectory
   * @throws \Imager\NotExistsException
   * @throws \Imager\BadPermissionException
   */
  private function setThumbnailsDirectory($targetDirectory)
  {
    $targetDirectory = Strings::trim($targetDirectory);
    $targetDirectory = rtrim($targetDirectory, '\\/');

    if (!is_dir($targetDirectory)) {
      $msg = sprintf('Directory "%s" with thumbnails not exists.', $targetDirectory);
      throw new NotExistsException($msg);
    }

    if (!is_writable($targetDirectory)) {
      $msg = sprintf('Directory "%" with thumbnails is not writable.', $targetDirectory);
      throw new BadPermissionException($msg);
    }

    $this->thumbnailsDirectory = $targetDirectory . DIRECTORY_SEPARATOR;
  }


  /**
   * Returns path for source image
   *
   * @param string $name
   * @return string
   */
  private function getSourcePath($name)
  {
    $subdirectory = $this->getSubdirectory($name);
    $path = $this->sourcesDirectory . $subdirectory . $name;

    return $path;
  }


  /**
   * Returns path for thumbnail of image
   *
   * @param string $name
   * @return string
   */
  private function getThumbnailPath($name)
  {
    $subdirectory = $this->getSubdirectory($name);
    $path = $this->thumbnailsDirectory . $subdirectory . $name;

    return $path;
  }


  /**
   * Returns subdirectory by name for greater segmentation
   *
   * @param string $name
   * @return string
   */
  private function getSubdirectory($name)
  {
    return Strings::substring($name, 0, 2) . DIRECTORY_SEPARATOR;
  }


  /**
   * Returns generated name for a image
   *
   * @param \Imager\ImageInfo $image
   * @return string
   */
  private function makeName(ImageInfo $image)
  {
    $source = $image->getSource() ?: $image;

    $name = md5($source->getPathname());
    $res = '_' . $image->getWidth() . 'x' . $image->getHeight();
    $ext = '.' . $source->getExtension();
    $fileName = $name . $res . $ext;

    return $fileName;
  }

}
