<?php
/**
 * @package Imager\Tracy
 * @author Ladislav Vondráček <lad.von@gmail.com>
 */

namespace Imager\Tracy;

use Imager;
use Tracy;

class Panel implements Tracy\IBarPanel
{

  public function getTab()
  {
    $html = <<<TAB
      <span title="Generated images"><img width="16" height="16" src="data:image/png;base64,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">
        <span id="imager-count"></span> <span id="imager-size"></span>
      </span>
      <script type="text/javascript">
        var count = $("img[data-imager='index']").length;
        $("#imager-count").text(count);
        var totalSize = 0;
        var images = $('img[data-imager="index"]');
        $.each(images, function (index, image) {
            $.ajax(image.src, {
                method: 'HEAD',
                async: false,
                success: function (res, status, xhr) {
                    var size = parseInt(xhr.getResponseHeader('Content-Length'));
                    console.log(size, totalSize);
                    totalSize += size;
                }
            });
        });
        var i = 0;
        while (totalSize >= 1024) {
            totalSize /= 1024;
            i++;
        }
        var units = ['B', 'KB', 'MB', 'GB', 'TG'];
        var size = Math.round(totalSize * 100) / 100 + ' ' + units[i]
        $("#imager-size").text('(' + size + ')')
      </script>
TAB;

    return $html;
  }


  public function getPanel()
  {
    return '';
  }


  public function register()
  {
    Tracy\Debugger::getBar()->addPanel($this, 'imager');
  }
}
