<?php

namespace TwigExtensions\Hub;

use TwigExtensions\BaseExtension;

class IsHeroGallery extends BaseExtension
{
  protected $extensionName = 'isHeroGallery';

  public function ext($object)
  {
    return $object['hero_type'] === 'gallery' && !empty($object['_links']['galleries']);
  }
}
