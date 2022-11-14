<?php


# Load all PHP components
foreach ( glob(__DIR__ . '/php/*') as $f ) {
  require_once $f;
}



# Test utility
$stats = ['good' => 0, 'bad' => 0];
function check_contains($title, $string, $rules = []) {
  global $stats;
  echo "\n" . $title . ' ';
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



if ( in_array('readme', $argv) ) {
  ob_start();
}



/* Core tests */

# Configure PHPy
$config = [
  '/' => __DIR__ . '/tests/app/web',
];



$_ENV['test_on'] = '1';
phpy::on('/', function() {
  $_ENV['test_on'] = '2';
  return true;
});



check_contains(
  'Checking app loader & layout',
  phpy($config), [
    '<html>'      => 'HTML block not rendered',
    'css.css'     => 'CSS script not included',
    'js.js'       => 'JS script not included',
    '12345'       => 'Static version not found',
    'demo page'   => 'Default action not rendered',
    '<title>I\'m' => 'Title not rendered',
]);



check_contains(
  'Testing on() handler',
  $_ENV['test_on'], [
    '2' => 'on() handler was not executed',
]);



check_contains(
  'Checking single component render',
  phpy('default'), [
    'demo page'   => 'Default action not rendered',
    '<h1>'        => 'Default action title not found',
]);



check_contains(
  'Checking ID/class attributes',
  phpy(['div#test.class1.class2' => 'sample']), [
    '<div'                  => '<div> element not found',
    'id="test"'             => 'ID not found',
    'class="class1 class2"' => 'Classes not found',
]);



check_contains(
  'Checking custom attributes',
  phpy(['a' => [':onclick' => 'test()', ':rel' => '123']]), [
    '<a'               => '<a> element not found',
    'onclick="test()"' => '"onclick" attr not found',
    'rel="123"'        => '"rel" attr not found',
]);



pub('test', ['msg' => 'hi']);
check_contains(
  'App events',
  phpy('/layout'), [
    "pub(\"test"   => 'Event not fired',
    '{"msg":"hi"}' => 'Event data not found',
  ]
);



/* Renderers */

check_contains(
  'Renderer: <a>',
  phpy(['a:/test'  => ['link', ':rel' => 'smth']]), [
    '<a'           => '<a> element not found',
    'href="/test"' => 'href is not found',
    'rel="smth"'   => '"rel" attr not found',
]);



check_contains(
  'Renderer: <form>',
  phpy(['form:/test:after()'  => []]), [
    '<form'          => '<form> element not found',
    'action="/test"' => 'href is not found',
    ', after()'      => 'post submit() event not fired',
]);



check_contains(
  'Renderer: <select>',
  phpy(['select:tst:2'   => [1 => 'a', 2 => 'b']]), [
    '<select'            => '<select> element not found',
    'name="tst"'         => 'name attr fail',
    '>a</option'         => 'first element text not found',
    '>b</option'         => 'second element text not found',
    '<option value="1"'  => 'first element not found',
    '<option value="2"'  => 'second element not found',
    'value="2" selected' => 'selected element fail',
]);



check_contains(
  'Renderer: <datalist>',
  phpy(['datalist:dl'   => ['a', 'b']]), [
    '<datalist'          => '<datalist> element not found',
    'id="dl"'            => 'ID attr fail',
    '<option value="a"'  => 'first element not found',
    '<option value="b"'  => 'second element not found',
]);



check_contains(
  'Renderer: <button>',
  phpy(['button:/path:after():are you sure'   => 'Click']), [
    '<button'                    => '<button> element not found',
    'onclick="if ( confirm('     => 'confirm condition not found',
    'are you sure'               => 'confirmation text not found',
    'phpy.apply(this, [\'/path\'' => 'phpy component handler not found'
]);



check_contains(
  'Renderer: <submit>',
  phpy(['submit' => 'Click']), [
    '<button'       => '<button> element not found',
    'type="submit"' => 'submit type not defined',
]);



check_contains(
  'Renderer: <input>',
  phpy(['input:email:Enter email' => []]), [
    '<input'       => '<input> element not found',
    'type="text"'  => 'text type not defined',
    'name="email"' => 'name attr failed',
    'placeholder'  => 'placeholder attr not found',
    'Enter email'  => 'placeholder text not found',
]);



check_contains(
  'Renderer: <input type="hidden">',
  phpy(['hidden:id' => '123']), [
    '<input'        => '<input> element not found',
    'type="hidden"' => 'hidden type not defined',
    'name="id"'     => 'name attr failed',
    'value="123"'   => 'value failed',
]);



check_contains(
  'Renderer: <input type="file">',
  phpy(['file:myfile' => []]), [
    '<input'        => '<input> element not found',
    'type="file"'   => 'file type not defined',
    'name="myfile"' => 'name attr failed',
]);



check_contains(
  'Renderer: <input type="checkbox">',
  phpy(['check:test:1' => []]), [
    '<input'          => '<input> element not found',
    'type="checkbox"' => 'checkbox type not defined',
    'name="test"'     => 'name attr failed',
    'checked="1"'     => 'checked flag failed',
]);



check_contains(
  'Renderer: <input type="radio">',
  phpy(['radio:test:1' => '23']), [
    '<input'          => '<input> element not found',
    'type="radio"'    => 'checkbox type not defined',
    'name="test"'     => 'name attr failed',
    'checked="1"'     => 'checked flag failed',
    'value="23"'      => 'value failed'
]);



check_contains(
  'Renderer: <img> / using default',
  phpy(['img:/img.png' => []]), [
    '<img'           => '<img> element not found',
    'src="/img.png"' => 'src attr failed'
]);



check_contains(
  'Renderer: <img> / using html',
  phpy(['img' => 'http://test.com/img.png']), [
    '<img'                          => '<img> element not found',
    'src="http://test.com/img.png"' => 'src attr failed'
]);



check_contains(
  'Renderer: <iframe>',
  phpy(['iframe' => 'http://test.com/path']), [
    '<iframe'                    => '<iframe> element not found',
    'src="http://test.com/path"' => 'src attr failed'
]);



check_contains(
  'Renderer: <video>',
  phpy(['video' => 'http://test.com/clip.mp4']), [
    '<video'                                 => '<video> element not found',
    '<source src="http://test.com/clip.mp4"' => 'src attr failed'
]);



check_contains(
  'Renderer: <progress>',
  phpy(['progress:35:100' => '35%']), [
    '<progress'   => '<progress> element not found',
    'max="100"'   => 'max failed',
    'value="35"'  => 'value failed',
    '>35%<'       => 'html failed'
]);



check_contains(
  'Renderer: <dl><dt><dd>',
  phpy(['dl' => ['name' => 'D', 'age' => '3']]), [
    '<dl'          => '<dl> element not found',
    '<dt>name</dt>' => 'Name key failed',
    '<dt>age</dt>'  => 'Age key failed',
    '<dd>D</dd>'    => 'D value failed',
    '<dd>3</dd>'    => '3 value failed',
]);



/* Custom renderer */

function phpy_pre_render_testlist(&$key, &$tpl, $phpy) {
  $tpl = ['ul' => array_map( fn($v) => ['li' => $v], $tpl['list'] )];
}

function phpy_post_render_testlist(&$html, &$attrs) {
  if ( isset($attrs['default'][0]) ) {
    $attrs['name'] = $attrs['default'][0];
  }
}

check_contains(
  'Custom renderer',
  phpy(['testlist:mylist' => ['list' => ['a', 'b', 'c']]]), [
    'name="mylist"'  => 'Name attr not found',
    '<ul><li>a</li>' => 'UL element not rendered'
  ]
);


function phpy_pre_render_test2(&$key, &$tpl, $phpy) {
  $tpl[':rel'] = $key;
}

function phpy_post_render_test2(&$html, &$attrs, $phpy) {
  $attrs_html = $phpy->tag_attrs($attrs);

  return "<div {$attrs_html}>{$html}</div>";
}

check_contains(
  'Custom renderer',
  phpy(['test2.cls' => ['list' => ['a', 'b', 'c']]]), [
    '<div'        => '<div> not found',
    'class="cls"' => 'Class not found',
    'rel="test2.cls"' => 'Type attr not found'
  ]
);



/* Helpers tests */

check_contains(
  'Endpoint helper',
  endpoint(),
  ['/' => 'Endpoint error']
);



check_contains(
  'Files collector',
  collect_files('php', __DIR__ . '/tests/app/app'), [
    "'html'" => 'layout.php content not found',
    "'h1'"   => 'defauylt.php content not found',
  ]
);



# Summary
echo "\n\n";
echo 'Done, ' .
      "\033[32m" . $stats['good'] . "\033[0m" . ' checks are ok' .
      ( $stats['bad'] ? (' and ' . "\033[31m" . $stats['bad'] . "\033[0m" . ' bad results') : '' );
echo "\n\n";



# Regenerate README.md
if ( in_array('readme', $argv) ) {
  $output = ob_get_clean();
  echo $output;

  $output = htmlspecialchars($output);

  $output = str_replace(
    ["\033[32m", "\033[31m", "\033[0m"],
    ['<b>', '<b>', '</b>'],
    nl2br($output)
  );

  file_put_contents(
    __DIR__ . '/tests.md',
    '# Tests results' . "\n" .
    'Executed on ' . date('Y-m-d H:i:s') . "\n" .
    $output
  );
}
