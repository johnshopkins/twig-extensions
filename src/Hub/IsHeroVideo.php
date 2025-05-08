<?php

namespace TwigExtensions\Hub;

use TwigExtensions\BaseExtension;

class IsHeroVideo extends BaseExtension
{
  protected $extensionName = 'isHeroVideo';

  public function ext($object)
  {
    return $object['hero_type'] === 'video' && !empty($object['_embedded']['videos']);
  }
}
