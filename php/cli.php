<?php

if ( php_sapi_name() == "cli" ) {
  if ( isset($argv[1]) && $argv[1] == 'init' ) {
    $dir = isset($argv[2]) ? $argv[2] : getcwd();

    foreach ( ['/web', '/app'] as $d ) {
      mkdir($dir . $d, 0755, true) ? print "Created {$dir}{$d} dir\n" : die("Failed to create {$dir}{$d} \n");
    }

    file_put_contents(
      $dir . '/web/index.php',
      '<' . '?php' . "\n\n" .
      'require_once \'' . __FILE__ . '\';' . "\n" .
      'phpy::on(\'/css.css\', fn() => phpy::css());' . "\n" .
      'phpy::on(\'/js.js\', fn() => phpy::js());' . "\n\n" .
      'echo phpy([\'/\' => __DIR__]);' . "\n"
    );

    file_put_contents(
      $dir . '/app/layout.php',
      '<' . '?php return [\'html\' => [' . "\n" .
      '  \':v\' => 1,' . "\n" .
      '  \':title\' => \'PHPy2 App\',' . "\n" .
      '  \'div\' => phpy()' . "\n" .
      ']];'
    );

    file_put_contents(
      $dir . '/app/default.php',
      '<' . '?php return [\'h1\' => \'I am the PHPy2 app\'];'
    );

    echo "\n";
    echo 'App files created, configure your Nginx now:' . "\n\n";
    echo '------' . "\n";
    echo 'server {' . "\n" .
         '  root ' . $dir . '/web;' . "\n" .
         '  index index.php;' . "\n" .
         '  ' . "\n" .
         '  server_name myapp;' . "\n" .
         '  location / {' . "\n" .
         '    try_files $uri /index.php?$args /index.php?$args;' . "\n" .
         '  }' . "\n" .
         '  ' . "\n" .
         '  location ~ \.php$ {' . "\n" .
         '    include snippets/fastcgi-php.conf;' . "\n" .
         '    fastcgi_pass unix:/run/php/php-fpm.sock;' . "\n" .
         '  }' . "\n" . '}';
    echo "\n" . '------' . "\n\n";
  }
}
