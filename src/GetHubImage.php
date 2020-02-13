<?php

namespace TwigExtensions;

class getHubImage extends BaseExtension
{
  protected $extensionName = 'getHubImage';

  protected $defaults = [
    // image to place in src attribute
    'defaultSize' => 'thumbnail',

    // media conditions and the image size required by each condition (image sizes attribute)
    'mediaConditions' => [],

    // additional classes to add to the div
    'classes' => [],

    // show caption TRUE/FALSE
    'showCaption' => true
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
    
    $options = $this->setOptions($image, $options);

    $html = '<div class="' . implode(' ', $options['classes']) . '">';

    $html .= $this->getImgTag($image, $options);
    $html .= $this->getCaptionAndCredit($image, $options);

    $html .= '</div>';

    return $html;
  }

  protected function setOptions($image, $options)
  {
    $options = array_merge($this->defaults, $options);
    $options['classes'] = $this->compileClasses($image, $options['classes']);

    return $options;
  }

  protected function compileClasses($image, $classes)
  {
    // add default classes
    $classes = array_merge(['image', 'column', 'force'], $classes);

    // add orientation class
    if (!empty($image['orientation'])) {
      $classes[] = 'image-' . $image['orientation'];
    }

    return $classes;
  }

  protected function getImgTag($image, $options)
  {
    $attributes = [
      'src' => $image['sizes'][$options['defaultSize']],
      'alt' => $image['alt_text'] ?? '',
      'class' => 'column'
    ];

    if (!empty($options['responsiveSizes'])) {
      $attributes['sizes'] = [];

      for ($i = 0; $i < count($this->imageBreakpoints); $i++) {
        $width = $this->imageBreakpoints[$i];
        $size = $options['responsiveSizes'][$i];
        $attributes['sizes'][] = !empty($width) ? "({$width}) {$size}px": "{$size}px";
      }

      $attributes['sizes'] = implode(', ', $attributes['sizes']);

      $srcset = $options['srcset'] ?? 'scaled';
      $attributes['srcset'] = $image['srcsets'][$srcset];
    }

    $attributes = array_map(function ($key) use ($attributes) {
      return $key . '="' . $attributes[$key] . '"';
    }, array_keys($attributes));

    return '<img ' . implode(' ', $attributes) . '/>';
  }

  protected function getCaptionAndCredit($image, $options)
  {
    if (!$options['showCaption']) {
      return '';
    }

    $caption = $this->getCaption($image);
    $credit = $this->getCredit($image);

    if (!$caption && !$credit) {
      return '';
    }

    return '<div class="caption column">' . $caption . $credit . '</div>';
  }

  protected function getCaption($image)
  {
    if (empty($image['caption'])) {
      return '';
    }

    return '<p><span class="visuallyhidden">Image caption: </span>' . strip_tags($image['caption'], '<i><b><strong><em><a>') . '</p>';
  }

  protected function getCredit($image)
  {
    if (empty($image['credit'])) {
      return '';
    }

    return '<p><span class="visuallyhidden">Credit: </span>' . $image['credit'] . '</p>';
  }
}
