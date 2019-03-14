<?php

namespace TwigExtensions;

class GetImage
{
  protected $extension;

  public function __construct()
  {
    $this->extension = new \Twig_SimpleFunction('getImage', [$this, 'getImage']);
  }

  public function get()
  {
    return $this->extension;
  }

  public function getImage($image, $crop = 'thumbnail', $attr = [])
  {
    $id = is_object($image) ? $image->ID : $image['ID'];
    return wp_get_attachment_image($id, $crop, false, $attr);
  }
}
