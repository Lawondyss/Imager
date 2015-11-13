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

  /** @var array */
  private $extentions = [
      IMAGETYPE_GIF => '.gif',
      IMAGETYPE_JPEG => '.jpg',
      IMAGETYPE_PNG => '.png',
      IMAGETYPE_BMP => '.bmp',
  ];


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

    return $this->moveImage($image, $target);
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

    return $this->moveImage($image, $target);
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

    if (!is_writable($sourcesDirectory)) {
      $msg = sprintf('Directory "%s" with sources is not writable.', $sourcesDirectory);
      throw new BadPermissionException($msg);
    }

    $this->sourcesDirectory = $sourcesDirectory . DIRECTORY_SEPARATOR;
  }


  /**
   * Sets directory with thumbnails
   *
   * @param string $thumbnailsDirectory
   * @throws \Imager\NotExistsException
   * @throws \Imager\BadPermissionException
   */
  private function setThumbnailsDirectory($thumbnailsDirectory)
  {
    $thumbnailsDirectory = Strings::trim($thumbnailsDirectory);
    $thumbnailsDirectory = rtrim($thumbnailsDirectory, '\\/');

    if (!is_dir($thumbnailsDirectory)) {
      $msg = sprintf('Directory "%s" with thumbnails not exists.', $thumbnailsDirectory);
      throw new NotExistsException($msg);
    }

    if (!is_writable($thumbnailsDirectory)) {
      $msg = sprintf('Directory "%s" with thumbnails is not writable.', $thumbnailsDirectory);
      throw new BadPermissionException($msg);
    }

    $this->thumbnailsDirectory = $thumbnailsDirectory . DIRECTORY_SEPARATOR;
  }


  /**
   * Moves image to target
   *
   * @param \Imager\ImageInfo $image
   * @param string $target
   * @return \Imager\ImageInfo
   */
  private function moveImage(ImageInfo $image, $target)
  {
    FileSystem::rename($image->getPathname(), $target);

    return new ImageInfo($target);
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

    // resolution of image only for thumbnail (has source)
    $res = !$image->hasSource() ? '' : ('_' . $image->getWidth() . 'x' . $image->getHeight());

    if ($source->getExtension() !== '') {
      $ext = '.' . $source->getExtension();

    } else {
      if (!array_key_exists($source->getType(), $this->extentions)) {
        $msg = sprintf('Image "%s" is unsupported type.', $source->getFilename());
        throw new InvalidStateException($msg);
      }

      $ext = $this->extentions[$source->getType()];
    }

    $fileName = $name . $res . $ext;

    return $fileName;
  }

}
