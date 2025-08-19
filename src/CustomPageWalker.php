<?php

namespace TwigExtensions;

class CustomPageWalker extends \Walker_Page
{
  protected $options = [];

  public function __construct($options = [])
  {
    $this->options = $options;
  }

  /**
   * Outputs the beginning of the current level in the tree before elements are output.
   * JHU changes denoted by: "start modified by JHU" and "end modified by JHU"
   *
   * @since 2.1.0
   *
   * @see Walker::start_lvl()
   *
   * @param string $output Used to append additional content (passed by reference).
   * @param int    $depth  Optional. Depth of page. Used for padding. Default 0.
   * @param array  $args   Optional. Arguments for outputting the next level.
   *                       Default empty array.
   */
  public function start_lvl( &$output, $depth = 0, $args = array() ) {
    if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) {
      $t = "\t";
      $n = "\n";
    } else {
      $t = '';
      $n = '';
    }

    /* start modified by JHU */
    // Default class.
    $classes = array( 'sub-menu', 'children' );

    $class_names = implode( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );

    $atts          = array();
    $atts['class'] = ! empty( $class_names ) ? $class_names : '';

    /**
     * Filters the HTML attributes applied to a menu list element.
     *
     * @since 6.3.0
     *
     * @param array $atts {
     *     The HTML attributes applied to the `<ul>` element, empty strings are ignored.
     *
     *     @type string $class    HTML CSS class attribute.
     * }
     * @param stdClass $args      An object of `wp_nav_menu()` arguments.
     * @param int      $depth     Depth of menu item. Used for padding.
     */
    $atts       = apply_filters( 'nav_menu_submenu_attributes', $atts, $args, $depth );
    $attributes = $this->build_atts( $atts );

    $indent  = str_repeat( $t, $depth );
    $output .= "{$n}{$indent}<ul{$attributes}>{$n}";
    /* end modified by JHU */

    // original WP
    // $indent  = str_repeat( $t, $depth );
    // $output .= "{$n}{$indent}<ul class='children'>{$n}";
  }

  /**
   * Outputs the beginning of the current element in the tree.
   * JHU changes denoted by: "start modified by JHU" and "end modified by JHU"
   *
   * @see Walker::start_el()
   * @since 2.1.0
   * @since 5.9.0 Renamed `$page` to `$data_object` and `$current_page` to `$current_object_id`
   *              to match parent class for PHP 8 named parameter support.
   *
   * @param string  $output            Used to append additional content. Passed by reference.
   * @param WP_Post $data_object       Page data object.
   * @param int     $depth             Optional. Depth of page. Used for padding. Default 0.
   * @param array   $args              Optional. Array of arguments. Default empty array.
   * @param int     $current_object_id Optional. ID of the current page. Default 0.
   */
  public function start_el( &$output, $data_object, $depth = 0, $args = array(), $current_object_id = 0 ) {
    // Restores the more descriptive, specific name for use within this method.
    $page = $data_object;

    $current_page_id = $current_object_id;

    if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) {
      $t = "\t";
      $n = "\n";
    } else {
      $t = '';
      $n = '';
    }
    if ( $depth ) {
      $indent = str_repeat( $t, $depth );
    } else {
      $indent = '';
    }

    $css_class = array( 'page_item', 'page-item-' . $page->ID );

    if ( isset( $args['pages_with_children'][ $page->ID ] ) ) {
      $css_class[] = 'page_item_has_children';
    }

    if ( ! empty( $current_page_id ) ) {
      $_current_page = get_post( $current_page_id );

      if ( $_current_page && in_array( $page->ID, $_current_page->ancestors, true ) ) {
        $css_class[] = 'current_page_ancestor';
      }

      if ( $page->ID === (int) $current_page_id ) {
        $css_class[] = 'current_page_item';
      } elseif ( $_current_page && $page->ID === $_current_page->post_parent ) {
        $css_class[] = 'current_page_parent';
      }
    } elseif ( (int) get_option( 'page_for_posts' ) === $page->ID ) {
      $css_class[] = 'current_page_parent';
    }

    /* start modified by JHU */
    $css_class = $this->maybeAddMoreClasses($css_class, $page);
    /* end modified by JHU */

    /**
     * Filters the list of CSS classes to include with each page item in the list.
     *
     * @since 2.8.0
     *
     * @see wp_list_pages()
     *
     * @param string[] $css_class       An array of CSS classes to be applied to each list item.
     * @param WP_Post  $page            Page data object.
     * @param int      $depth           Depth of page, used for padding.
     * @param array    $args            An array of arguments.
     * @param int      $current_page_id ID of the current page.
     */
    $css_classes = implode( ' ', apply_filters( 'page_css_class', $css_class, $page, $depth, $args, $current_page_id ) );
    $css_classes = $css_classes ? ' class="' . esc_attr( $css_classes ) . '"' : '';

    if ( '' === $page->post_title ) {
      /* translators: %d: ID of a post. */
      $page->post_title = sprintf( __( '#%d (no title)' ), $page->ID );
    }

    $args['link_before'] = empty( $args['link_before'] ) ? '' : $args['link_before'];
    $args['link_after']  = empty( $args['link_after'] ) ? '' : $args['link_after'];

    $atts                 = array();
    $atts['href']         = get_permalink( $page->ID );
    $atts['aria-current'] = ( $page->ID === (int) $current_page_id ) ? 'page' : '';

    /* start modified by JHU */
    $atts = $this->maybeAddTrackingAttributes($atts, $page);
    /* end modified by JHU */

    /**
     * Filters the HTML attributes applied to a page menu item's anchor element.
     *
     * @since 4.8.0
     *
     * @param array $atts {
     *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
     *
     *     @type string $href         The href attribute.
     *     @type string $aria-current The aria-current attribute.
     * }
     * @param WP_Post $page            Page data object.
     * @param int     $depth           Depth of page, used for padding.
     * @param array   $args            An array of arguments.
     * @param int     $current_page_id ID of the current page.
     */
    $atts = apply_filters( 'page_menu_link_attributes', $atts, $page, $depth, $args, $current_page_id );

    $attributes = '';
    foreach ( $atts as $attr => $value ) {
      if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
        $value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
        $attributes .= ' ' . $attr . '="' . $value . '"';
      }
    }

    $output .= $indent . sprintf(
        '<li%s><a%s>%s%s%s</a>',
        $css_classes,
        $attributes,
        $args['link_before'],
        /** This filter is documented in wp-includes/post-template.php */
        apply_filters( 'the_title', $page->post_title, $page->ID ),
        $args['link_after']
      );

    if ( ! empty( $args['show_date'] ) ) {
      if ( 'modified' === $args['show_date'] ) {
        $time = $page->post_modified;
      } else {
        $time = $page->post_date;
      }

      $date_format = empty( $args['date_format'] ) ? '' : $args['date_format'];
      $output     .= ' ' . mysql2date( $date_format, $time );
    }
  }

  /**
   * Builds a string of HTML attributes from an array of key/value pairs.
   * Empty values are ignored.
   *
   * From Walker_Nav_Menu
   *
   * @since 6.3.0
   *
   * @param  array $atts Optional. An array of HTML attribute key/value pairs. Default empty array.
   * @return string A string of HTML attributes.
   */
  protected function build_atts( $atts = array() ) {
    $attribute_string = '';
    foreach ( $atts as $attr => $value ) {
      if ( false !== $value && '' !== $value && is_scalar( $value ) ) {
        $value             = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
        $attribute_string .= ' ' . $attr . '="' . $value . '"';
      }
    }
    return $attribute_string;
  }



  protected function maybeAddMoreClasses($classes, $page)
  {
    // print_r($classes);

    // a page (usually other than the actual active page) to force as the active page
    if ($this->options['forceActivePage'] && (int) page->ID === $this->options['forceActivePage']) {
      $classes = array_merge($classes, ['current_page_ancestor', 'current_page_parent']);
    }

    $activeSection = [
      'current_page_ancestor',
      'current_page_parent',
      'current_page_item',
    ];

    if (array_intersect($activeSection, $classes)) {
      $classes[] = 'active-section';

      if ($this->options['toggle'] && !$this->options['dropdown']) {
        // only set if menu can be toggled and it isn't a dropdown. prevents dropdown from auto opening
        $classes[] = 'open';
      }
    }

    if (in_array('current_page_item', $classes)) {
      $classes[] = 'active-page';
    }

    return $classes;
  }

  protected function maybeAddTrackingAttributes($atts, $page)
  {
    if (!empty($this->options['name'])) {
      $atts['data-action'] = 'Click';
      $atts['data-category'] = $this->options['name'];
      $atts['data-label'] = apply_filters( 'the_title', $page->post_title, $page->ID );
    }

    return $atts;
  }
}
