<?php

namespace TwigExtensions;

class GetImage extends BaseExtension
{
  protected $extensionName = 'getImage';

  protected $defaults = [
    'size' => 'thumbnail',
    'responsiveSizes' => [],
    'attr' => []
  ];

  protected $imageBreakpoints = [
    'min-width: 1680px',  // desktop
    'min-width: 1280px',  // desktop
    'min-width: 1024px',  // table landscape
    'min-width: 863px',   // drastic breakpoint
    'min-width: 768px',   // table portrait
    'min-width: 640px',   // mobile landscape
    'min-width: 412px',   // large module portrait
    'min-width: 375px',   // regular modern iPhone portrait
    ''                    // below 375px
  ];

  public function __construct($imageBreakpoints = [])
  {
    parent::__construct();

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

    if (!empty($options['responsiveSizes'])) {

      $options['attr']['sizes'] = [];

      for ($i = 0; $i < count($this->imageBreakpoints); $i++) {
        $width = $this->imageBreakpoints[$i];
        $size = $options['responsiveSizes'][$i];
        $options['attr']['sizes'][] = !empty($width) ? "({$width}) {$size}px": "{$size}px";
      }
      $options['attr']['sizes'] = implode(', ', $options['attr']['sizes']);

    }

    return wp_get_attachment_image($id, $options['size'], false, $options['attr']);
  }
}
