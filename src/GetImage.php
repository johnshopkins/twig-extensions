<?php

namespace TwigExtensions;

class GetImage
{
  protected $extension;

  protected $defaults = [
    // display size
    'size' => 'medium_thumb',

    // media conditions and the image size required by each condition (image sizes attribute)
    'mediaConditions' => [],

    'attr' => []
  ];

  public function __construct()
  {
    $this->extension = new \Twig_SimpleFunction('getImage', [$this, 'getImage']);
  }

  public function get()
  {
    return $this->extension;
  }

  public function getImage($image, $options = [])
  {
    if (empty($image)) {
      return '';
    }

    $options = array_merge($this->defaults, $options);

    $id = is_object($image) ? $image->ID : $image['ID'];

    // add sizes
    if (!empty($options['mediaConditions'])) {
      $options['attr']['sizes'] = implode(', ', $options['mediaConditions']);
    }

    return wp_get_attachment_image($id, $options['size'], false, $options['attr']);
  }
}
