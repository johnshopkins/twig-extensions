<?php

namespace TwigExtensions;

class WpHead extends BaseExtension
{
  protected $extensionName = 'wp_head';

  public function ext()
  {
    wp_head();
  }
}
