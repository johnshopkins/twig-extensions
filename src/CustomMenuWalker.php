<?php

namespace TwigExtensions;

class CustomMenuWalker extends \Walker_Nav_Menu
{
  protected $options = [];

  public function __construct($options = [])
  {
    $this->options = $options;
  }

  /**
   * Starts the element output.
   * JHU changes denoted by: "start modified by JHU" and "end modified by JHU"
   *
   * @since 3.0.0
   * @since 4.4.0 The {@see 'nav_menu_item_args'} filter was added.
   * @since 5.9.0 Renamed `$item` to `$data_object` and `$id` to `$current_object_id`
   *              to match parent class for PHP 8 named parameter support.
   *
   * @see Walker::start_el()
   *
   * @param string   $output            Used to append additional content (passed by reference).
   * @param WP_Post  $data_object       Menu item data object.
   * @param int      $depth             Depth of menu item. Used for padding.
   * @param stdClass $args              An object of wp_nav_menu() arguments.
   * @param int      $current_object_id Optional. ID of the current menu item. Default 0.
   */
  public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
    // Restores the more descriptive, specific name for use within this method.
    $menu_item = $data_object;

    if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
      $t = '';
      $n = '';
    } else {
      $t = "\t";
      $n = "\n";
    }
    $indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

    $classes   = empty( $menu_item->classes ) ? array() : (array) $menu_item->classes;
    $classes[] = 'menu-item-' . $menu_item->ID;

    /**
     * Filters the arguments for a single nav menu item.
     *
     * @since 4.4.0
     *
     * @param stdClass $args      An object of wp_nav_menu() arguments.
     * @param WP_Post  $menu_item Menu item data object.
     * @param int      $depth     Depth of menu item. Used for padding.
     */
    $args = apply_filters( 'nav_menu_item_args', $args, $menu_item, $depth );

    /* start modified by JHU */
    $classes = $this->maybeAddMoreClasses($classes, $menu_item);
    /* end modified by JHU */

    /**
     * Filters the CSS classes applied to a menu item's list item element.
     *
     * @since 3.0.0
     * @since 4.1.0 The `$depth` parameter was added.
     *
     * @param string[] $classes   Array of the CSS classes that are applied to the menu item's `<li>` element.
     * @param WP_Post  $menu_item The current menu item object.
     * @param stdClass $args      An object of wp_nav_menu() arguments.
     * @param int      $depth     Depth of menu item. Used for padding.
     */
    /* start modified by JHU */
    // $class_names = implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $menu_item, $args, $depth ) ); // wp original
    $class_names = implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $menu_item, $args, $depth, $this->options ) );
    /* end modified by JHU */

    $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

    /**
     * Filters the ID applied to a menu item's list item element.
     *
     * @since 3.0.1
     * @since 4.1.0 The `$depth` parameter was added.
     *
     * @param string   $menu_id   The ID that is applied to the menu item's `<li>` element.
     * @param WP_Post  $menu_item The current menu item.
     * @param stdClass $args      An object of wp_nav_menu() arguments.
     * @param int      $depth     Depth of menu item. Used for padding.
     */
    $id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $menu_item->ID, $menu_item, $args, $depth );
    $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

    /* start modified by JHU */
    // $output .= $indent . '<li' . $id . $class_names . '>'; // wp original
    $output .= $indent . '<li' . $id . $class_names;
    if ($this->options['dropdown'] && in_array('menu-item-has-children', $classes)) {
      $output .= ' aria-haspopup="true"';
    }
    $output .= '>';
    /* end modified by JHU */

    $atts           = array();
    $atts['title']  = ! empty( $menu_item->attr_title ) ? $menu_item->attr_title : '';
    $atts['target'] = ! empty( $menu_item->target ) ? $menu_item->target : '';
    if ( '_blank' === $menu_item->target && empty( $menu_item->xfn ) ) {
      $atts['rel'] = 'noopener';
    } else {
      $atts['rel'] = $menu_item->xfn;
    }
    $atts['href']         = ! empty( $menu_item->url ) ? $menu_item->url : '';
    $atts['aria-current'] = $menu_item->current ? 'page' : '';


    /* start modified by JHU */
    $atts = $this->maybeAddTrackingAttributes($atts, $menu_item);
    /* end modified by JHU */

    /**
     * Filters the HTML attributes applied to a menu item's anchor element.
     *
     * @since 3.6.0
     * @since 4.1.0 The `$depth` parameter was added.
     *
     * @param array $atts {
     *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
     *
     *     @type string $title        Title attribute.
     *     @type string $target       Target attribute.
     *     @type string $rel          The rel attribute.
     *     @type string $href         The href attribute.
     *     @type string $aria-current The aria-current attribute.
     * }
     * @param WP_Post  $menu_item The current menu item object.
     * @param stdClass $args      An object of wp_nav_menu() arguments.
     * @param int      $depth     Depth of menu item. Used for padding.
     */
    $atts = apply_filters( 'nav_menu_link_attributes', $atts, $menu_item, $args, $depth );

    $attributes = '';
    foreach ( $atts as $attr => $value ) {
      if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
        $value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
        $attributes .= ' ' . $attr . '="' . $value . '"';
      }
    }

    /** This filter is documented in wp-includes/post-template.php */
    $title = apply_filters( 'the_title', $menu_item->title, $menu_item->ID );

    /**
     * Filters a menu item's title.
     *
     * @since 4.4.0
     *
     * @param string   $title     The menu item's title.
     * @param WP_Post  $menu_item The current menu item object.
     * @param stdClass $args      An object of wp_nav_menu() arguments.
     * @param int      $depth     Depth of menu item. Used for padding.
     */
    $title = apply_filters( 'nav_menu_item_title', $title, $menu_item, $args, $depth );

    $item_output  = $args->before;

    /* start modified by JHU */
    /* wp original
    $item_output .= '<a' . $attributes . '>';
    $item_output .= $args->link_before . $title . $args->link_after;
    $item_output .= '</a>';
    */
    if ($menu_item->type !== 'custom_header') {
      $item_output .= '<a' . $attributes . '>';
      $item_output .= $args->link_before . $title . $args->link_after;
      $item_output .= '</a>';
    } else {
      $item_output .= '<h6><span>'. $title .'</span></h6>';
    }
    $item_output .=$this->maybeAddToggle($classes, $title, $depth);
    /* end modified by JHU */

    /**
     * Filters a menu item's starting output.
     *
     * The menu item's starting output only includes `$args->before`, the opening `<a>`,
     * the menu item's title, the closing `</a>`, and `$args->after`. Currently, there is
     * no filter for modifying the opening and closing `<li>` for a menu item.
     *
     * @since 3.0.0
     *
     * @param string   $item_output The menu item's starting HTML output.
     * @param WP_Post  $menu_item   Menu item data object.
     * @param int      $depth       Depth of menu item. Used for padding.
     * @param stdClass $args        An object of wp_nav_menu() arguments.
     */
    $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $menu_item, $depth, $args );
  }

  protected function maybeAddMoreClasses($classes, $menu_item)
  {
    $activeSection = [
      'current-'. $menu_item->object .'-ancestor',
      'current-menu-item',
      'current-menu-ancestor'
    ];

    if (array_intersect($activeSection, $classes)) {
      $classes[] = 'active-section';

      if ($this->options['toggle'] && !$this->options['dropdown']) {
        // only set if menu can be toggled and it isn't a dropdown. prevents dropdown from auto opening
        $classes[] = 'open';
      }
    }

    if (in_array('current-menu-item', $classes)) {
      $classes[] = 'active-page';
    }

    return $classes;
  }

  protected function maybeAddTrackingAttributes($atts, $menu_item)
  {
    if (!empty($this->options['name'])) {
      $atts['data-action'] = 'Click';
      $atts['data-category'] = $this->options['name'];
      $atts['data-label'] = apply_filters( 'the_title', $menu_item->title, $menu_item->ID );
    }

    return $atts;
  }

  protected function maybeAddToggle($classes, $title, $depth)
  {
    $output = '';

    if (!$this->options['toggle'] || $depth + 1 === $this->options['depth'] || !in_array('menu-item-has-children', $classes)) {
      // if we're already at the last depth we're printing (wordpress counts 0 as the top-leveL)
      // or this menu item doesn't have any children
      return $output;
    }

    if (in_array('open', $classes) && !$this->options['dropdown']) {
      $action = 'Close';
      $icon = 'fa-minus-square-o';
    } else {
      $action = 'Open';
      $icon = 'fa-plus-square-o';
    }

    $output .= '<button class="toggle-section"';
    if ($this->options['dropdown']) {
      $output .= ' tabindex="-1"';
    }
    $output .= '>';
    $output .= '<i class="fa '. $icon .'" aria-hidden="true"></i>';
    $output .= '<span class="visuallyhidden">'. $action .' '. $title .'</span>';
    $output .= '</button>';

    return $output;
  }
}
