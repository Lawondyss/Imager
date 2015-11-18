<?php
/**
 * @package Imager
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager;

use Nette\Utils\Strings;
use Nette\Utils\Validators;

class Image
{

  /** @var \Imager\ImageInfo */
  private $image;


  /**
   * @param \Imager\ImageInfo $image
   */
  public function __construct(ImageInfo $image)
  {
    $this->image = $image;
  }


  /**
   * Resize image and save to target file
   *
   * @param null|int|string $with NULL: original width; integer: width in pixel; string: others specific (50%)
   * @param null|int|string $height NULL: calculating by ratio; integer: height in pixel; string: others specific (50%)
   * @param null|int $quality
   * @return \Imager\ImageInfo
   * @throws \Imager\InvalidArgumentException
   * @throws \Imager\InvalidStateException
   */
  public function resize($with = null, $height = null, $quality = null)
  {
    if (!isset($with) && !isset($height)) {
      throw new InvalidArgumentException('At least one dimension must be defined.');
    }

    $this->checkDimension($with, 'width');
    $this->checkDimension($height, 'height');

    $with = $with === 0 ? $this->image->getWidth() . '!' : $with;
    $height = $height === 0 ? $this->image->getHeight() . '!' : $height;
    $quality = $quality ?: 0;

    $source = $this->image->getPathname();
    $options = $this->getCommandOptions($with, $height, $quality);
    $target = $this->createTempFile();

    $command = sprintf('convert %s %s %s', $source, $options, $target);
    $this->run($command);

    $img = new ImageInfo($target, $this->image);
    $img->setQuality($quality);

    return $img;
  }


  /**
   * Generate image with error and send to output
   *
   * @param int $width
   * @param int $height
   */
  public static function errorImage($width, $height)
  {
    $command = [
      'convert',
      sprintf('-size %dx%d', $width, $height),
      '-background red',
      '-gravity center',
      '-fill black',
      'label:" Error image generation. \n\n More information \n in image headers. "',
      'gif:-'
    ];
    $command = implode(' ', $command);
    passthru($command);
  }


  /**
   * Check value of dimension
   *
   * @param int|string $dimension
   * @param string $type
   */
  private function checkDimension($dimension, $type)
  {
    if (Validators::is($dimension, 'string') && !Validators::isNumeric($dimension) && Strings::substring($dimension, -1) !== '%') {
      $msg = sprintf('Dimension of %s has unexpected format, "%s" given.', $type, $dimension);
      throw new InvalidArgumentException($msg);
    }

    if ((int)$dimension < 0) {
      $msg = sprintf('Dimension of %s must be greater than 0, "%s" given.', $type, $dimension);
      throw new InvalidArgumentException($msg);
    }
  }


  /**
   * Returns options part of command
   *
   * @param mixed $width
   * @param mixed $height
   * @param null|int $quality
   * @return string
   */
  private function getCommandOptions($width, $height, $quality)
  {
    $options = [];

    if (isset($width) && (int)$width === 0) {
      $width = $this->image->getWidth() . '!';
    }

    if (isset($height) && (int)$height === 0) {
      $height = $this->image->getHeight() . '!';
    }

    if (!isset($height)) {
      $options['resize'] = '"' . $width . '"';
    } elseif (!isset($width)) {
      $options['resize'] = '"x' . $height . '"';
    } else {
      $options['resize'] = '"' . Strings::trim($width, '!') . 'x' . Strings::trim($height, '!') . '^"';
    }

    if (isset($width) && isset($height)) {
      $options['gravity'] = 'center';
      $options['crop'] = '"' . $width . 'x' . $height . '+0+0"';
    }

    if (isset($quality) && $quality !== 0) {
      $options['quality'] = $quality;
    }

    $command = [];
    foreach ($options as $opt => $value) {
      $command[] = '-' . $opt . ' ' . $value;
    }

    return implode(' ', $command);
  }


  /**
   * Returns path to temporary file
   *
   * @return string
   */
  private function createTempFile()
  {
    return tempnam(sys_get_temp_dir(), 'imager_');
  }


  /**
   * Runs command
   *
   * @param string $command
   * @throws \Imager\InvalidStateException
   */
  private function run($command)
  {
    // remove newlines and convert single quotes to double to prevent errors
    $command = str_replace(["\n", "'"], ['', '"'], $command);

    exec($command . ' 2>&1', $output); // @ because error throw as exception

    if (count($output) > 0) {
      $msg = sprintf('Unexpected output for command `%s`. Error: %s', $command, implode('; ', $output));
      throw new InvalidStateException($msg);
    }
  }
}
