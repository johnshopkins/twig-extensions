<?php

namespace TwigExtensions;

class Lazyload extends BaseExtension
{
  protected $extensionName = 'lazyload';

  protected $classes = ['bbload', 'force'];

  protected $defaults = [
    'additionalData' => [], // additional data to send to JS
    'classes' => [],        // additional classes to add to the container tag
    'imageSizes' => [],     // used in JavaScript for responsive image sizes
    'post' => null,         // if the lazyload is on a post (prevents this post from showing up in lazyload content)
    'tag' => 'div',         // container tag
    'title' => '',          // title of the lazyload container
    'titleTag' => 'h6'      // tag the title should be contained in
  ];

  public function ext($data, $options = [])
  {
    if (isset($data['post_type']) && $data['post_type'] === 'collection') {
      // collection post
      return $this->collectionPost($data, $options);
    } else if (isset($data['id'])) {
      // single item set by hub api content picker
      return $this->singleItem($data, $options);
    } else if (isset($data['endpoint']) || isset($data['endpoints'])) {
      // parameters set in twig templates
      return $this->manualCollection($data, $options);
    }
  }

  protected function collectionPost($collection, $options)
  {
    // normalize collection object data for $this->collection
    $options['classes'][] = 'content-collection';

    $data = [
      'type' => $collection['meta']['type'],
      'params' => ['per_page' => $collection['count'] ?? 5]
    ];

    if ($data['type'] === 'explicit') {
      // limit number of items to specified count
      $order = array_slice($collection['meta']['order'], 0, $data['params']['per_page']);
      $data['endpoints'] = $this->compileEndpoints($collection['meta']['endpoints'], $order);
      $data['params']['order'] = implode(',', $order);
      $data['params']['order_by'] = 'list';
      $data['params']['source'] = 'all';
    } else {
      $data['endpoint'] = $collection['meta']['endpoint'];
    }

    // query parameter key/values on the collection
    if (!empty($collection['meta']['query'])) {
      foreach ($collection['meta']['query'] as $query) {
        $data['params'][$query['key']] = $query['value'];
      }
    }

    return $this->printLazyload($data, $options);
  }

  protected function singleItem($item, $options)
  {
    $data = [
      'endpoints' => $item['collection'] . '=' . $item['id'],
      'params' => [
        'source' => 'all',
        'per_page' => 1
      ]
    ];

    $options['classes'][] = 'content-item';

    return $this->printLazyload($data, $options);
  }

  protected function manualCollection($collectionData, $options)
  {
    // normalize manual collection data for $this->collection
    $options['classes'][] = 'content-collection';
    return $this->printLazyload($collectionData, $options);
  }

  /**
   * Collection data: endpoint, endpoints, params, type
   * @param [type] $collectionData
   * @param [type] $options
   * @return void
   */
  protected function printLazyload($collectionData, $options)
  {
    $type = $collectionData['type'] ?? 'default'; // default, explicit, related

    if (isset($collectionData['type']) && $collectionData['type'] === 'related' && empty($options['post']['_embedded']['topics']) && empty($options['post']['_embedded']['tags'])) {
      $type = 'related';
    } else {
      $type = 'default';
    }

    $options = $this->compileOptions($collectionData, $options);

    $attributes = [
      'class' => implode(' ', $options['classes']),
      'data-per_page' => $collectionData['params']['per_page'] ?? 5,
      'data-type' => $type
    ];

    if (isset($collectionData['endpoints'])) {
      $attributes['data-endpoints'] = $collectionData['endpoints'];
    } else if (isset($collectionData['endpoint'])) {
      $attributes['data-endpoint'] = $collectionData['endpoint'];
    }

    // exclude ID of current post
    if (!empty($options['post'])) {
      $id = $options['post']['id'];
      $hasInstances = strpos($id, '.');
      $attributes['data-excluded_ids'] = $hasInstances ? substr($id, 0, $hasInstances) : $id; // substr for events (their ID includes event instance ID)
    }

    // passed in query data
    if (!empty($collection['params'])) {
      foreach ($collection['params'] as $key => $value) {
        $key = 'data-' . $key;
        $attributes[$key] = $value;
      }
    }
  
    // merge in additional data passed in twig
    $attributes = array_merge($attributes, $options['additionalData']);

    $attributes = $this->compileAttributes($attributes);
    $output = "<{$options['tag']} {$attributes}>";

    $outputData = [
      'imageSizes' => $options['imageSizes']
    ];

    // add data needed for related collection
    if ($type === 'related' and !empty($options['post'])) {
      $outputData['tags'] = $options['post']['_embedded']['tags'];
      $outputData['topics'] = $options['post']['_embedded']['topics'];
      $outputData['related_content'] = $options['post']['_links']['related_content'] ?? null;
    }

    $output .= '<script type="application/json">' . json_encode($outputData) . '</script>';

    if (!empty($options['title'])) {
      $output .= "<{$options['titleTag']}>{$options['title']}</{$options['titleTag']}>";
    }

    $output .= "</{$options['tag']}>";

    return $output;
  }

  /**
   * Combine array of attributes into a string that can be placed
   * into a div.
   * @param array $attributes Ex: ["data-{attribute}" => "value"]
   * @return void
   */
  protected function compileAttributes($attributes)
  {
    return implode(' ', array_map(function ($key) use ($attributes) {
      $value = $attributes[$key];
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
