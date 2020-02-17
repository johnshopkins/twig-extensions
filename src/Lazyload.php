<?php

namespace TwigExtensions;

class Lazyload extends BaseExtension
{
  protected $extensionName = 'lazyload';

  protected $classes = ['bbload', 'content-collection', 'force'];

  protected $defaults = [
    'additionalData' => [], // additional data to send to JS
    'classes' => [],        // additional classes to add to the container tag
    'post' => null,         // if the lazyload is on a post (prevents this post from showing up in lazyload content)
    'tag' => 'div',         // container tag
    'title' => '',          // title of the lazyload container
    'titleTag' => 'h6'      // tag the title should be contained in
  ];

  public function ext($data, $options = [])
  {
    if (isset($data['id'])) {
      return $this->singleItem($data);
    } else {
      return $this->collection($data, $options);
    }
  }

  protected function singleItem($itemData)
  {
    $attributes = $this->compileAttributes([
      'data-endpoint' => $itemData['collection'],
      'data-ids' => $itemData['id'],
      'data-source' => 'all',
      'data-type' => 'recent'
    ]);

    return "<div class=\"bbload content-item\" {$attributes}></div>";
  }

  protected function collection($collectionData, $options)
  {
    $collection = $collectionData['collection'];
    $options = $this->compileOptions($collectionData, $options);

    $type = $collectionData['type'] ?? $collection['meta']['type'];

    if ($type === 'default') {
      $type = 'recent';
    }

    $attributes = [
      'class' => implode(' ', $options['classes']),
      'data-per_page' => $collectionData['count'] ?? 5,
      'data-type' => $type
    ];

    if ($type === 'explicit') {
      // limit number of items to per_page
      $order = array_slice($collection['meta']['order'], 0, $attributes['data-per_page']);
      $attributes['data-endpoints'] = $this->compileEndpoints($collection['meta']['endpoints'], $order);
      $attributes['data-order'] = implode(',', $order);
      $attributes['data-source'] = 'all';
    } else {
      $attributes['data-endpoint'] = $collection['meta']['endpoint'];
    }

    // exclude ID of current post
    if (!empty($options['post'])) {
      $id = $options['post']['id'];
      $hasInstances = strpos($id, '.');
      $attributes['data-excluded_ids'] = $hasInstances ? substr($id, 0, $hasInstances) : $id; // substr for events (their ID includes event instance ID)
    }

    // query parameter key/values on the collection
    if (!empty($collection['meta']['query'])) {
      foreach ($collection['meta']['query'] as $query) {
        $key = 'data-' . $query['key'];
        $attributes[$key] = $query['value'];
      }
    }
  
    // merge in additional data passed in twig
    $attributes = array_merge($attributes, $options['additionalData']);

    $attributes = $this->compileAttributes($attributes);
    $output = "<{$options['tag']} {$attributes}>";

    // add data needed for related collection
    if ($type === 'related' and !empty($options['post'])) {
      $relatedData = [
        'tags' => $options['post']['_embedded']['tags'],
        'topics' => $options['post']['_embedded']['topics'],
        'related_content' => $options['post']['_links']['related_content'] ?? null
      ];
      $output .= '<script type="application/json">' . json_encode($relatedData) . '</script>';
    }

    if (!empty($options['title'])) {
      $output .= "<{$options['titleTag']}>{$options['title']}</{$options['titleTag']}>";
    }

    $output .= "</{$options['tag']}>";

    return $output;
  }

  protected function compileAttributes($attributes)
  {
    return implode(' ', array_map(function ($key) use ($attributes) {
      $value = $attributes[$key];
      if (is_array($value)) {
        echo '<pre>'; print_r([
          $key, $value
        ]); echo '</pre>'; die();
      }
      return "{$key}=\"{$value}\"";
    }, array_keys($attributes)));
  }

  protected function compileEndpoints($endpoints, $order)
  {
    $endpoints = array_map(function ($endpoint) use ($endpoints, $order) {
      return $endpoint . '=' . $this->compileIds($endpoints[$endpoint], $order);
    }, array_keys($endpoints));

    return implode('&', $endpoints);
  }

  protected function compileIds($ids, $order)
  {
    $ids = array_map(function ($id) use ($order) {
      return in_array($id, $order) ? $id : null;
    }, $ids);

    return implode(',', array_filter($ids));
  }

  protected function compileClasses($data, $classes)
  {
    $classes = parent::compileClasses($data, $classes);

    if (isset($data['importance'])) {
      // flex content
      $classes[] = 'importance-' . $data['importance'];
    }

    if (isset($data['layout'])) {
      // flex content
      $classes[] = 'layout-' . $data['layout'];
    }

    return $classes;
  }
}
