<?php

namespace TwigExtensions\Hub;

use TwigExtensions\BaseExtension;

class IsHeroImage extends BaseExtension
{
  protected $extensionName = 'isHeroImage';

  public function ext($object)
  {
    return ($object['format'] === 'Impact Image Emphasis' || $object['hero_type'] === 'image') && !empty($object['_embedded']['image_impact']);
  }
}
