<?php

namespace TwigExtensions;

class getHubImage
{
  protected $extension;

  protected $defaults = [
    // display size
    'size' => 'thumbnail',

    // media conditions and the image size required by each condition (image sizes attribute)
    'mediaConditions' => [],

    // additional classes to add to the div
    'classes' => [],

    // show caption TRUE/FALSE
    'showCaption' => true
  ];

  public function __construct()
  {
    $this->extension = new \Twig_SimpleFunction('getHubImage', [$this, 'getHubImage']);
  }

  public function get()
  {
    return $this->extension;
  }

  public function getHubImage($image, $options = [])
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
    // which set to use
    $srcset = 'scaled';
    if (in_array($options['size'], ['thumbnail', 'landscape'])) {
      $srcset = 'cropped-landscape';
    }

    $attributes = [
      'src' => $image['sizes'][$options['size']],
      'alt' => $image['alt_text'],
      'class' => 'column',
      'srcset' => $image['srcsets'][$srcset],
      'sizes' => implode(', ', $options['mediaConditions'])
    ];

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
