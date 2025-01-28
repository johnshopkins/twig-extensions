<?php

namespace TwigExtensions;

class GetImage extends BaseExtension
{
  protected $extensionName = 'getImage';

  protected $defaults = [
    'size' => 'thumbnail',
    'cropType' => 'soft',
    'responsiveSizes' => [],
    'attr' => []
  ];

  public function __construct($responsiveImageHelper, $imageBreakpoints = [])
  {
    parent::__construct();

    $this->responsiveImageHelper = $responsiveImageHelper;

    if (!empty($imageBreakpoints)) {
      $this->imageBreakpoints = $imageBreakpoints;
    }
  }

  public function ext($image, $options = [])
  {
    if (empty($image)) {
      return '';
    }

    $options = array_merge($this->defaults, $options);

    $id = null;

    if (is_object($image)) {
      $id = $image->ID;
    } elseif (is_array($image)) {
      $id = $image['ID'];
    } else {
      $id = (int) $image;
    }

    // default wordpress srcset
    // ensures that even thumbnails have them (by default, they don't)
    $options['attr']['srcset'] = wp_get_attachment_image_srcset($id, $options['size']);

    if (!empty($options['responsiveSizes'])) {
      $options['attr']['sizes'] = $this->responsiveImageHelper->getImageSizes($options['responsiveSizes']);
    }
    
    return wp_get_attachment_image($id, $options['size'], false, $options['attr']);
  }
}
