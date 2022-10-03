<?php

# Save built files to this dir
$dst = realpath(__DIR__ . '/../phpy');



# Assemble utility
function assemble($dir) {
  $code = '';
  foreach ( glob(__DIR__ . '/' . $dir . '/*') as $f ) {
    $code .= file_get_contents($f) . "\n";
  }
  return $code;
}



# Assemble files
$php = '<?php' . str_replace('<?php', '', assemble('php'));
$js = assemble('js');
$css = assemble('css');



# Save files
file_put_contents($dst . '/phpy.php', $php);
file_put_contents($dst . '/phpy.js',  $js);
file_put_contents($dst . '/phpy.css', $css);



echo 'Files built and saved to ' . $dst . "\n\n";