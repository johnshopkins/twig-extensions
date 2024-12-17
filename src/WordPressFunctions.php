<?php

namespace TwigExtensions;

class WordPressFunctions extends BaseExtension
{
  protected $extensionName = 'wp';

  public function ext($function, ...$args)
  {
    // print_r([...$args]); die();
    call_user_func($function, ...$args);
  }
}
