<?php

namespace TwigExtensions;

class getHubImage extends BaseExtension
{
  protected $extensionName = 'getHubImage';

  protected $classes = ['image', 'column', 'force'];

  protected $defaults = [
    // image to place in src attribute
    'defaultSize' => 'thumbnail',

    // additional classes to add to the div
    'classes' => [],

    // show caption TRUE/FALSE
    'showCaption' => true,

    // allow paragraphs in captions TRUE/FALSE
    'allowCaptionParagraphs' => false
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
    
    $options = $this->compileOptions($image, $options);

    $html = '<div class="' . implode(' ', $options['classes']) . '">';

    $html .= $this->getImgTag($image, $options);
    $html .= $this->getCaptionAndCredit($image, $options);

    $html .= '</div>';

    return $html;
  }

  protected function compileClasses($image, $classes)
  {
    $classes = parent::compileClasses($image, $classes);

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
      $attributes['sizes'] = $this->responsiveImageHelper->getImageSizes($options['responsiveSizes']);
      $srcset = $options['srcset'] ?? 'scaled';
      $attributes['srcset'] = $image['srcsets'][$srcset];
    }

    $compiled = array_map(function ($key) use ($attributes) {
      return $key . '="' . $attributes[$key] . '"';
    }, array_keys($attributes));

    if (!isset($options['placeholder'])) {
      return '<div class="image-container"><img ' . implode(' ', $compiled) . '/></div>';
    } else {
      $ratio = ($image['height'] / $image['width']) * 100;
      return '<div class="image-container"><script type="application/json">' . json_encode($attributes) . '</script><div class="placeholder" style="padding-bottom:' . $ratio . '%;"></div></div>';
    }
  }

  protected function getCaptionAndCredit($image, $options)
  {
    if (!$options['showCaption']) {
      return '';
    }

    $classes = ['caption', 'column'];

    $caption = $this->getCaption($image, $options);
    $credit = $this->getCredit($image);

    if (!$caption && !$credit) {
      return '';
    }

    if ($caption) {
      $classes[] = 'has-caption';
    }

    if ($credit) {
      $classes[] = 'has-credit';
    }

    return '<div class="' . implode(' ', $classes) . '">' . $caption . $credit . '</div>';
  }

  protected function getCaption($image, $options)
  {
    if (empty($image['caption'])) {
      return '';
    }

    if (!$options['allowCaptionParagraphs']) {
      return '<p><span class="visuallyhidden">Image caption: </span>' . strip_tags($image['caption'], '<i><b><strong><em><a>') . '</p>';
    } else {
      return '<span class="visuallyhidden">Image caption: </span>' . strip_tags($image['caption'], '<i><b><strong><em><a><p>');
    }
  }

  protected function getCredit($image)
  {
    if (empty($image['credit'])) {
      return '';
    }

    return '<p class="credit"><span class="visuallyhidden">Credit: </span>' . $image['credit'] . '</p>';
  }
}
