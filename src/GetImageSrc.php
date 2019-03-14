<?php

namespace TwigExtensions;

class GetImageSrc
{
  protected $extension;

  public function __construct()
  {
    $this->extension = new \Twig_SimpleFunction('getImageSrc', [$this, 'getImageSrc']);
  }

  public function get()
  {
    return $this->extension;
  }

  public function getImageSrc($image, $crop = 'thumbnail')
  {
    $id = null;

    if (is_object($image)) {
      $id = $image->ID;
    } elseif (is_array($image) && isset($image['ID'])) {
      $id = $image['ID'];
    }

    if ($id) {
      $src = wp_get_attachment_image_src($id, $crop);
      return $src[0];
    }

    return '';
  }
}
