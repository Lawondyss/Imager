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
  private $required = ['sourcesDir', 'basePath'];

  /** @var array */
  private $defaults = [
      'sourcesDir' => null,
      'thumbsDir' => null,
      'baseUrl' => null,
      'basePath' => null,
      'debugger' => '%debugMode%',
  ];


  public function loadConfiguration()
  {
    $config = $this->getConfig();
    $this->configValidation($config);

    $config = array_merge($this->defaults, $config);

    $builder = $this->getContainerBuilder();

    $builder->addDefinition($this->prefix('repository'))
        ->setClass(\Imager\Repository::class, [
            $config['sourcesDir'],
            $config['thumbsDir'],
        ]);

    $imageFactory = $builder->addDefinition($this->prefix('imageFactory'))
        ->setClass(\Imager\ImageFactory::class);

    if ($config['debugger'] && interface_exists(\Tracy\IBarPanel::class)) {
      $builder->addDefinition($this->prefix('panel'))
        ->setClass(\Imager\Tracy\Panel::class);

      $imageFactory->addSetup('?->register(?)', [$this->prefix('@panel'), '@self']);
    }
  }


  public function beforeCompile()
  {
    $config = $this->getConfig($this->defaults);
    $builder = $this->getContainerBuilder();

    $builder->addDefinition($this->prefix('route'))
        ->setClass(\Imager\Application\Route::class, [$this->prefix('@repository'), $this->prefix('@imageFactory'), $config['basePath']])
        ->setAutowired(false);

    $router = $builder->getDefinition('router');
    $router->addSetup('Imager\Helpers::prependRouter', ['@self', $this->prefix('@route')]);

    $latteName = $builder->hasDefinition('latte.latteFactory') ? 'latte.latteFactory' : 'latte.latte';
    $latte = $builder->getDefinition($latteName);
    $latte->addSetup('\Imager\Latte\Macro::install(?->getCompiler(), ?)', ['@self', $config['baseUrl']]);
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
    $missing = array_diff($this->required, array_keys($config));

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
