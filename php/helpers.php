<?php

/* PHPy components */

# Universal components renderer
function phpy($data_or_com = null, $args = []) {
  static $app_loaded = false;

  if ( !$app_loaded ) {
    $app_loaded = true;
    return phpy::instance($data_or_com)->app();
  }

  if ( is_array($data_or_com) ) {
    return phpy::instance()->render($data_or_com, $args)[0];
  }

  return phpy::instance()->com($data_or_com, $args);
}

# files collector
function collect_files($extensions, $dir = null) {
  $content = '';
  if ( !$dir ) {
    $content .= collect_files($extensions, __DIR__);
  }

  $dir = isset($dir) ? $dir : dirname(phpy::config('/'));
  $dir .= '/*';

  foreach ( glob($dir) as $f ) {
    if ( is_dir($f) ) {
      $content .= collect_files($extensions, $f);
    }
    else if ( in_array(pathinfo($f, PATHINFO_EXTENSION), $extensions) ) {
      $content .= file_get_contents($f) . "\n";
    }
  }

  return $content;
}

# get current endpoint
function endpoint() {
  return phpy::endpoint();
}

# pub/sub -> publish event
function pub($event, $data = true) {
  return phpy::pub($event, $data);
}



/* HTTP */

# redirect to specified URL (PHPy AJAX support included)
function redirect($url) {
  if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
    header('Xlocation: ' . $url);
    exit;
  }
  else {
    header('Location: ' . $url);
    exit;
  }
}



/* Utilities */

# escape string for safe output in html
function e($text) {
  return htmlspecialchars($text);
}

# safely returns specified $array $key or $default value
function akey($array, $key, $default = null) {
  return isset($array[$key]) ? $array[$key] : $default;
}

# returns number incremented by 1 for each new call
function nums($namespace = 'default') {
  static $counters = [];
  if ( !isset($counters[$namespace]) ) {
    return $counters[$namespace] = 1;
  }
  else {
    return ++$counters[$namespace];
  }
}