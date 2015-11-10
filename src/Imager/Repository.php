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
  private $sourceDirectory;

  /** @var null|string */
  private $targetDirectory;


  /**
   * @param string $sourceDirectory
   * @param null|string $targetDirectory
   * @throws \Imager\NotExistsException
   * @throws \Imager\BadPermissionException
   */
  public function __construct($sourceDirectory, $targetDirectory = null)
  {
    $this->setSourceDirectory($sourceDirectory);

    // if not target directory defined, then source directory is together target directory
    $targetDirectory = $targetDirectory ?: $sourceDirectory;
    $this->setTargetDirectory($targetDirectory);
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

    $source = $this->getFetchPath($name);

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
    $name = $name ?: $this->makeName($image);
    $target = $this->getSavePath($name);

    FileSystem::rename($image->getPathname(), $target);

    return new ImageInfo($target);
  }


  /**
   * Sets directory for fetch images
   *
   * @param string $sourceDirectory
   */
  private function setSourceDirectory($sourceDirectory)
  {
    $sourceDirectory = Strings::trim($sourceDirectory);
    $sourceDirectory = rtrim($sourceDirectory, '\\/');

    if (!is_dir($sourceDirectory)) {
      $msg = sprintf('Source directory "%s" not exists.', $sourceDirectory);
      throw new NotExistsException($msg);
    }

    $this->sourceDirectory = $sourceDirectory . DIRECTORY_SEPARATOR;
  }


  /**
   * Sets directory for save images
   *
   * @param string $targetDirectory
   */
  private function setTargetDirectory($targetDirectory)
  {
    $targetDirectory = Strings::trim($targetDirectory);
    $targetDirectory = rtrim($targetDirectory, '\\/');

    if (!is_dir($targetDirectory)) {
      $msg = sprintf('Target directory "%s" not exists.', $targetDirectory);
      throw new NotExistsException($msg);
    }

    if (!is_writable($targetDirectory)) {
      $msg = sprintf('Target directory "%" is not writable.', $targetDirectory);
      throw new BadPermissionException($msg);
    }

    $this->targetDirectory = $targetDirectory . DIRECTORY_SEPARATOR;
  }


  /**
   * Returns path for fetch file
   *
   * @param $name
   * @return string
   */
  private function getFetchPath($name)
  {
    $subdirectory = $this->getSubdirectory($name);
    $path = $this->sourceDirectory . $subdirectory . $name;

    return $path;
  }


  /**
   * Returns path for save file
   *
   * @param string $name
   * @return string
   */
  private function getSavePath($name)
  {
    $subdirectory = $this->getSubdirectory($name);
    $path = $this->targetDirectory . $subdirectory . $name;

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
