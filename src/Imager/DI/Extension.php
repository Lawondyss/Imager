<?php
/**
 * @package Imager\DI
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager\DI;

use Imager\InvalidStateException;
use Nette\DI\CompilerExtension;

class Extension extends CompilerExtension
{

  /** @var array */
  private $expected = ['sourcesDir', 'routes'];

  /** @var array */
  private $defaults = [
      'sourcesDir' => null,
      'thumbsDir' => null,
      'routes' => [],
  ];


  public function loadConfiguration()
  {
    $config = $this->getConfig($this->defaults);
    $this->configValidation($config);

    $builder = $this->getContainerBuilder();

    $builder->addDefinition($this->prefix('repository'))
        ->setClass(\Imager\Repository::class, [
            $config['sourcesDir'],
            $config['thumbsDir'],
        ]);

    $builder->addDefinition($this->prefix('imageFactory'))
        ->setClass(\Imager\ImageFactory::class);
  }


  public function beforeCompile()
  {
    $config = $this->getConfig($this->defaults);
    $builder = $this->getContainerBuilder();

    $router = $builder->getDefinition('router');

    foreach ($config['routes'] as $i => $mask) {
      $builder->addDefinition($this->prefix('route.' . $i))
          ->setClass(\Imager\Application\Route::class, [$this->prefix('@repository'), $this->prefix('@imageFactory'), $mask])
          ->setAutowired(false);

      $router->addSetup('Imager\Helpers::prependRouter', ['@self', $this->prefix('@route.' . $i)]);
    }
  }


  /**
   * Checks configurations options
   *
   * @param array $config
   * @throws \Imager\InvalidStateException
   */
  public function configValidation(array $config)
  {
    $config = $config ?: [];

    /* Check required options */
    $missing = array_diff($this->expected, array_keys($config));

    if (count($missing) > 0) {
      array_walk($missing, function (&$item) {
        $item = $this->name . '.' . $item;
      });

      $msg = sprintf('Missing required configuration option(s): %s', implode(', ', $missing));
      throw new InvalidStateException($msg);
    }

    /* Check unexpected options */
    $extra = array_diff_key($config, $this->defaults);

    if (count($extra) > 0) {
      $extra = array_keys($extra);

      array_walk($extra, function (&$item) {
        $item = $this->name . '.' . $item;
      });

      $msg = sprintf('Unknow configuration option(s): %s.', implode(', ', $extra));
      throw new InvalidStateException($msg);
    }
  }
}
