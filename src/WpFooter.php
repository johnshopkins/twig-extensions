<?php

namespace TwigExtensions;

class WpFooter extends BaseExtension
{
  protected $extensionName = 'wp_footer';

  public function ext()
  {
    wp_footer();
  }
}
