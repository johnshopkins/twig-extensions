<?php

namespace TwigExtensions;

class GetImageAltText extends BaseExtension
{
  protected $extensionName = 'getImageAltText';

  public function ext($image)
  {
    $id = null;

    if (is_object($image)) {
      $id = $image->ID;
    } elseif (is_array($image) && isset($image['ID'])) {
      $id = $image['ID'];
    }

    if ($id) {
      return get_post_meta($id, '_wp_attachment_image_alt', TRUE);
    }

    return '';
  }
}
