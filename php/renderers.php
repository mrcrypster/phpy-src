<?php


/* <html> */

function phpy_post_render_html(&$html, &$attrs) {
  $pub_events = [];
  if ( phpy::$events ) foreach ( phpy::$events as $event => $data ) {
    $pub_events[] = "pub(" . json_encode($event) . ", " . json_encode($data) . ");";
  }

  return '<html>' .
         '<head>' .
           '<title>' . akey($attrs, ':title') . '</title>' .
           '<link href="/css.css?' . akey($attrs, ':v') . '" rel="stylesheet">' .
            akey($attrs, ':head') .
         '</head>' .
         '<body>' . $html . '</body>' .
         '<script src="/js.js?' . akey($attrs, ':v') . '"></script>'.
         ($pub_events ? ('<script>' . implode(';', $pub_events) . '</script>') : '') .
         '</html>';
}



/* <a> */

function phpy_post_render_a(&$html, &$attrs) {
  if ( isset($attrs['default'][0]) ) {
    if ( strpos($attrs['default'][0], '(') ) {
      $attrs['href'] = 'javascript:' . $attrs['default'][0];
    }
    else {
      $attrs['href'] = $attrs['default'][0] ?: 'javascript:;';
    }
  }
}



/* <form> */

function phpy_post_render_form(&$html, &$attrs) {
  $after_callback = '';

  if ( isset($attrs['default'][1]) ) {
    $after_callback = ', ' . $attrs['default'][1];
  }

  if ( isset($attrs['default'][0]) ) {
    $attrs['action'] = $attrs['default'][0];
    $attrs['onsubmit'] = 'phpy.apply(this, [\'' . $attrs['action'] . '\', this' . $after_callback . ']); return false;';
  }
}



/* <select> */

function phpy_pre_render_select(&$key, &$tpl, $phpy) {
  $keys = explode(':', $key);
  $tpl = array_map(
    fn($v, $k) => ['option' => array_merge([':value' => $k, $v], $keys[2] == $k ? [':selected' => 'on'] : [])],
    array_values($tpl), array_keys($tpl)
  );
}

function phpy_post_render_select(&$html, &$attrs) {
  if ( isset($attrs['default'][0]) ) {
    $attrs['name'] = $attrs['default'][0];
  }
}



/* <datalist> */

function phpy_pre_render_datalist(&$key, &$tpl, $phpy) {
  $tpl = array_map(
    fn($v) => ['option' => [':value' => $v]],
    $tpl
  );
}

function phpy_post_render_datalist(&$html, &$attrs) {
  if ( isset($attrs['default'][0]) ) {
    $attrs['id'] = $attrs['default'][0];
  }
}



/* <dl> */

function phpy_pre_render_dl(&$key, &$tpl, $phpy) {
  $tpl = array_map(
    fn($v, $k) => ['dt' => $k, 'dd' => $v],
    array_values($tpl), array_keys($tpl)
  );
}




/* <button> */

function phpy_post_render_button(&$html, &$attrs) {
  $after_callback = '';

  if ( isset($attrs['default'][1]) ) {
    $after_callback = ', {}, ' . $attrs['default'][1];
  }
  
  $confirm = '';
  if ( isset($attrs['default'][2]) ) {
    $confirm = 'if ( confirm(\'' . e($attrs['default'][2]) . '\') ) ';
  }

  if ( isset($attrs['default'][0]) ) {
    if ( strpos($attrs['default'][0], '(') ) {
      $attrs['onclick'] = $attrs['default'][0];
    }
    else {
      $attrs['onclick'] = $confirm . 'phpy.apply(this, [\'' . $attrs['default'][0] . '\'' . $after_callback . '])';
    }
  }
  
  $attrs['type'] = isset($attrs['type']) ? $attrs['type'] : 'button';
}



/* <button type="submit"> */

function phpy_post_render_submit(&$html, &$attrs, $phpy) {
  $attrs['type'] = 'submit';
  $attrs_html = $phpy->tag_attrs($attrs);
  return "<button {$attrs_html}>{$html}</button>";
}



/* <input> */

function phpy_post_render_input(&$html, &$attrs) {
  if ( $html && !isset($attrs['value']) ) {
    $attrs['value'] = $html;
    $html = '';
  }

  if ( !isset($attrs['type']) ) {
    $attrs['type'] = 'text';
  }

  if ( isset($attrs['default'][0]) ) {
    $attrs['name'] = $attrs['default'][0];
  }

  if ( isset($attrs['default'][1]) ) {
    $attrs['placeholder'] = $attrs['default'][1];
  }
}



/* <input type="hidden"> */

function phpy_post_render_hidden(&$html, &$attrs, $phpy) {
  if ( $html && !isset($attrs['value']) ) {
    $attrs['value'] = $html;
    $html = '';
  }

  $attrs['type'] = 'hidden';

  if ( isset($attrs['default'][0]) ) {
    $attrs['name'] = $attrs['default'][0];
  }

  $attrs_html = $phpy->tag_attrs($attrs);
  return "<input {$attrs_html}/>";
}



/* <input type="file"> */

function phpy_post_render_file(&$html, &$attrs, $phpy) {
  $attrs['name'] = isset($attrs['default'][0]) ? $attrs['default'][0] : (isset($attrs['name']) ? $attrs['name'] : 'file');
  $attrs_html = $phpy->tag_attrs($attrs);
  
  return "<input type=\"file\" {$attrs_html}/>";
}



/* <input type="checkbox"> */

function phpy_post_render_check(&$html, &$attrs, $phpy) {
  $attrs['name'] = isset($attrs['default'][0]) ? $attrs['default'][0] : (isset($attrs['name']) ? $attrs['name'] : 'check');
  if ( $html || isset($attrs['default'][1]) ) {
    $attrs['checked'] = 1;
  }
  
  $attrs_html = $phpy->tag_attrs($attrs);
  
  return "<input type=\"checkbox\" {$attrs_html}/>";
}



/* <input type="radio"> */

function phpy_post_render_radio(&$html, &$attrs, $phpy) {
  $attrs['name'] = isset($attrs['default'][0]) ? $attrs['default'][0] : (isset($attrs['name']) ? $attrs['name'] : 'check');
  if ( $html || isset($attrs['default'][1]) ) {
    $attrs['checked'] = 1;
  }
  
  $attrs_html = $phpy->tag_attrs($attrs);
  
  return "<input type=\"radio\" {$attrs_html}/>";
}



/* <img> */

function phpy_post_render_img(&$html, &$attrs, $phpy) {
  $attrs['src'] = $html ?: (isset($attrs['default'][0]) ? $attrs['default'][0] : (isset($attrs['src']) ? $attrs['src'] : ''));
  $attrs_html = $phpy->tag_attrs($attrs);
  
  return "<img {$attrs_html}/>";
}



/* <video> */

function phpy_post_render_video(&$html, &$attrs, $phpy) {
  if ( !is_array($html) && !isset($attrs['src']) ) {
    $html = '<source src="' . $html . '">';
  }

  $attrs_html = $phpy->tag_attrs($attrs);
  
  return "<video {$attrs_html}>{$html}</video>";
}



/* <iframe> */

function phpy_post_render_iframe(&$html, &$attrs, $phpy) {
  $attrs['src'] = $html ?: (isset($attrs['default'][0]) ? $attrs['default'][0] : (isset($attrs['src']) ? $attrs['src'] : ''));
  $attrs_html = $phpy->tag_attrs($attrs);
  
  return "<iframe {$attrs_html}/>";
}



/* <progress> */

function phpy_post_render_progress(&$html, &$attrs, $phpy) {
  if ( isset($attrs['default'][0]) ) {
    $attrs['value'] = $attrs['default'][0];
  }
  
  $attrs['max'] = isset($attrs['default'][1]) ? $attrs['default'][1] : 100;
  $attrs_html = $phpy->tag_attrs($attrs);
  
  return "<progress {$attrs_html}>{$html}</progress>";
}



/* <textarea> */

function phpy_post_render_textarea(&$html, &$attrs) {
  if ( isset($attrs['default'][0]) ) {
    $attrs['name'] = $attrs['default'][0];
  }

  if ( isset($attrs['default'][1]) ) {
    $attrs['placeholder'] = $attrs['default'][1];
  }
}
