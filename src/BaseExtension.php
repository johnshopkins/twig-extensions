<?php

namespace TwigExtensions;

abstract class BaseExtension
{
  protected $extension;

  protected $extensionName;

  protected $defaults = [];

  protected $classes = [];

  public function __construct()
  {
    $this->extension = new \Twig\TwigFunction($this->extensionName, [$this, 'ext']);
  }

  public function get()
  {
    return $this->extension;
  }

  protected function compileOptions($image, $options)
  {
    $options = array_merge($this->defaults, $options);
    
    $options['classes'] = $this->compileClasses($image, $options['classes']);

    return $options;
  }

  protected function compileClasses($image, $classes)
  {
    return array_merge($this->classes, $classes);
  }
}
