<?php


# Load all PHP components
foreach ( glob(__DIR__ . '/php/*') as $f ) {
  require_once $f;
}



# Test utility
$stats = ['good' => 0, 'bad' => 0];
function check_contains($title, $string, $rules = []) {
  global $stats;
  echo "\n" . $title;
  foreach ( $rules as $check => $msg ) {
    $ok = strpos($string, $check) !== false;
    if ( $ok ) {
      $stats['good']++;
      echo "\033[32m.\033[0m";
    }
    else {
      $stats['bad']++;
      echo "\n";
      echo "\033[31m{$msg}\033[0m";
      echo "\n";
    }
  }
}



# Configure PHPy
$config = [
  '/' => __DIR__ . '/tests/app/web',
];



check_contains(
  'Checking app loader & layout',
  phpy($config), [
    '<html>'      => 'HTML block not rendered',
    'css.css'     => 'CSS script not included',
    'js.js'       => 'JS script not included',
    '12345'       => 'Static version not found',
    'demo page'   => 'Default action not rendered',
]);



check_contains(
  'Checking single component render',
  phpy('default'), [
    'demo page'   => 'Default action not rendered',
    '<h1>'        => 'Default action title not found',
]);



# Summary
echo "\n\n";
echo 'Done, ' . $stats['good'] . ' checks are ok and ' . $stats['bad'] . ' bad results';
echo "\n\n";