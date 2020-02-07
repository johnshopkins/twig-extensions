<?php

namespace TwigExtensions;

class GetImage
{
  protected $extension;

  protected $defaults = [
    'size' => 'thumbnail',
    'responsiveSizes' => [],
    'attr' => []
  ];

  protected $responsiveWidths = [
    '(min-width: 1680px)', // above 1680px
    '(min-width: 1366px)', // 1366-1680px
    '(min-width: 1280px)', // 1280-1366px
    '(min-width: 1024px)', // 1024-1280px
    '(min-width: 800px)',  // 800-1024px
    '(min-width: 412px)',  // 412-800px
    '(min-width: 375px)',  // 375-414px
    ''                     // below 375px
  ];

  public function __construct($responsiveWidths = [])
  {
    if (!empty($responsiveWidths)) {
      $this->responsiveWidths = $responsiveWidths;
    }
    
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

    if (!empty($options['responsiveSizes'])) {

      $options['attr']['sizes'] = [];

      for ($i = 0; $i < count($this->responsiveWidths); $i++) {
        $width = $this->responsiveWidths[$i];
        $size = $options['responsiveSizes'][$i];
        $options['attr']['sizes'][] = !empty($width) ? "({$width}) {$size}px": "{$size}px";
      }
      $options['attr']['sizes'] = implode(', ', $options['attr']['sizes']);

    }

    return wp_get_attachment_image($id, $options['size'], false, $options['attr']);
  }
}
