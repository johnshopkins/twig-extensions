<?php

namespace TwigExtensions;

abstract class BaseExtension
{
  protected $extension;

  protected $extensionName;

  protected $defaults = [];

  public function __construct()
  {
    $this->extension = new \Twig_SimpleFunction($this->extensionName, [$this, 'ext']);
  }

  public function get()
  {
    return $this->extension;
  }
}
