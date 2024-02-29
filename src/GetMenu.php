<?php

namespace TwigExtensions;

class GetMenu extends BaseExtension
{
  protected $extensionName = 'getMenu';

  protected $defaults = [
    // Additional classes to add to the div
    'classes' => [],

    // Content to dump before the top-level </ul> (used by Hub)
    'before' => '',

    // Content to dump after the top-level <ul> (used by Hub)
    'after' => '',

    // Number of menu levels to print (formally tiersToPrint)
    'depth' => 4,

    // use dropdown accessible markup
    'dropdown' => false,

    // force the page with this ID to be the active page
    // usage: force "university leadership" page to be active when a leadership
    // profile is being viewed
    'forceActivePage' => null,

    // show toggle
    'toggle' => false,

    // for link tracking
    'name' => null
  ];

  public function __construct()
  {
    parent::__construct();

    add_filter('nav_menu_submenu_css_class', function ($classes, $args, $depth) {

      $classes[] = 'tier-' . ((int) $depth + 2);
      $classes[] = 'force';

      return $classes;

    }, 10, 3);
  }

  public function ext($menu, $options = [])
  {
    if (!$menu) {
      // no menu set to this location
      return;
    }

    $options = array_merge($this->defaults, $options);

    // always show toggle if drowndown nav (for touch devices)
    if ($options['dropdown']) {
      $options['toggle'] = true;
    }

    return wp_nav_menu([
      'container' => false,
      'echo' => false,
      'depth' => $options['depth'],
      'fallback_cb' => false,
      'items_wrap' => $this->getItemsWrap($options),
      'menu' => $menu,
      'walker' => new CustomMenuWalker($options)
    ]);
  }

  protected function getItemsWrap($options)
  {
    $classes = array_merge(['tier-1', 'force'], $options['classes']);

    return '<ul class="'. implode(' ', $classes) .'">'. $options['before'] .'%3$s'. $options['after'] .'</ul>';
  }
}
