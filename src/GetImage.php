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

    $id = is_object($image) ? $image->ID : $image['ID'];

    // default wordpress srcset
    // ensures that even thumbnails have them (by default, they don't)
    $options['attr']['srcset'] = wp_get_attachment_image_srcset($id, $options['size']);

    if (!empty($options['responsiveSizes'])) {

      $options['attr']['sizes'] = $this->responsiveImageHelper->getImageSizes($options['responsiveSizes']);

    } else {
      // default wordpress sizes
      // ensures that even thumbnails have them (by default, they don't)
      $options['attr']['sizes'] = wp_get_attachment_image_srcset($id, $options['size']);
    }


    return wp_get_attachment_image($id, $options['size'], false, $options['attr']);
  }
}
