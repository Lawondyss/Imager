<?php
/**
 * @package Imager\Latte
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager\Latte;

use Latte\Macros\MacroSet;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\PhpWriter;

class Macro extends MacroSet
{

  /** @var string */
  public static $baseUrl;


  public static function install(Compiler $parser, $baseUrl = null)
  {
    self::$baseUrl = $baseUrl;

    $me = new static($parser);
    $me->addMacro('src',
        function (MacroNode $node, PhpWriter $writer) use ($me) {
          return $me->macroSrc($writer);
        }, null,
        function (MacroNode $node, PhpWriter $writer) use ($me) {
          return ' ?> src="<?php ' . $me->macroSrc($writer) . ' ?>" data-imager="index"<?php ';
        }
    );
  }


  public function macroSrc(PhpWriter $writer)
  {
    $code = self::getCode('%node.array');

    // in macro must go result on output
    $code[] = 'echo %escape(%modify($link));';

    return $writer->write(implode('', $code));
  }


  /**
   * Returns PHP code for generate link to image
   *
   * @param string $parametersCode PHP code with parameters for destination for Presenter::link()
   * @return array
   */
  public static function getCode($parametersCode)
  {
    $code = [
      '$imgBaseUrl = !empty("' . self::$baseUrl . '") ? "' . self::$baseUrl . '" : (isset($baseUrl) ? $baseUrl : "");',
      '$url = $presenter->link(":Nette:Micro:", Imager\Helpers::prepareArguments(' . $parametersCode . '));',
      '$link = rtrim($imgBaseUrl, "/") . "/" . ltrim($url, "/");',
    ];

    return $code;
  }
}
