<?php

namespace TwigExtensions;

class GetManifestAsset extends BaseExtension
{
  protected $extensionName = 'getAsset';
  protected $assets = [];

  public function __construct(protected int $year)
  {
    $manifest = APP_ROOT . '/public/assets/2024/manifest.json';

    if (!file_exists($manifest)) {
      throw new \Exception('Manifest file does not exist', 500);
    }

    try {
      $this->assets = json_decode(file_get_contents($manifest), true);
    } catch (\Throwable $e) {
      throw new \Exception('Manifest file failed to parse', 500);
    }

    parent::__construct();
  }

  public function ext($asset)
  {
    if (!array_key_exists($asset, $this->assets)) {
      print_r([$this->assets, $asset]); die();
      throw new \Exception('Asset does not exist in the manifest file.');
    }

    

    return $this->assets[$asset];
  }
}
