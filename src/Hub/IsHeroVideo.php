<?php

namespace TwigExtensions\Hub;

use TwigExtensions\BaseExtension;

class IsHeroVideo extends BaseExtension
{
  protected $extensionName = 'isHeroVideo';

  public function ext($object)
  {
    if ($object['type'] !== 'event') {
      return ($object['format'] === 'Video Emphasis' || $object['hero_type'] === 'video') && !empty($object['_embedded']['videos']);
    } else {
      return false;
    }
  }
}
