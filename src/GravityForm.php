<?php

namespace TwigExtensions;

class GravityForm extends BaseExtension
{
  protected $extensionName = 'gravityForm';

  public function ext($id, $values = false)
  {
    /**
     * https://docs.gravityforms.com/adding-a-form-to-the-theme-file/
     */
    gravity_form($id, false, false, false, $values, true);
  }
}
