<?php

/* Core engine */

class phpy {
  private $config = [ 'layout' => 'layout' ];
  public static $listeners = [];
  public static $events = [];

  public function __construct($config = []) {
    $this->config = array_merge($this->config ?: [], $config ?: []);
    if ( !isset($this->config['/']) ) {
      $this->config['/'] = getcwd();
    }
  }

  public function set($param, $value) {
    $this->config[$param] = $value;
  }

  public function get($param) {
    return $this->config[$param];
  }

  public static function instance($data = []) {
    static $phpy;

    if ( !$phpy ) {
      $phpy = new phpy($data);
    }

    return $phpy;
  }



  /* Global context */

  # Custom URI listeners
  public static function on($endpoint, $callback) {
    self::$listeners[$endpoint][] = $callback;
  }

  # Return current endpoint
  public static function endpoint() {
    return parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/')['path'];
  }

  # Publish event to client
  public static function pub($event, $data = true) {
    phpy::$events[$event] = $data;
  }



  # application launcher
  public function app() {
    foreach ( self::$listeners as $pattern => $handlers ) {
      if ( ($pattern == $this->endpoint()) || preg_match($pattern, $this->endpoint()) ) {
        foreach ( $handlers as $cb ) {
          $continue = $cb($this);
        }
        
        if ( !$continue ) {
          return;
        }
      }
    }
    
    if ( $this->endpoint() == '/js.js' ) {
      header('Content-type: application/javascript');
      readfile(__DIR__ . '/phpy.js');
    }
    else if ( $this->endpoint() == '/css.css' ) {
      header('Content-type: text/css');
      readfile(__DIR__ . '/phpy.css');
    }
    else if ( isset($_SERVER['REQUEST_METHOD']) && ($_SERVER['REQUEST_METHOD'] == 'POST') ) {
      $data = $this->com_data( $this->endpoint() );

      foreach ( $data as $container => $tpl ) {
        $data[$container] = $this->render($tpl)[0];
      }

      header('Content-type: text/json');
      header('Xpub: ' . base64_encode(json_encode(phpy::$events)));

      return json_encode($data);
    }
    else {
      return $this->com_render( $this->config['layout'] );
    }
  }

  # com launcher
  public function com($com = null, $args = []) {
    if ( is_null($com) ) {
      $com = $this->endpoint();
    }

    return $this->com_render($com, $args);
  }

  # get com file path
  public function com_file($endpoint) {
    $file = dirname($this->config['/']) . '/' .
           (isset($this->config['app']) ?: 'app') . '/' .
           $endpoint . '.php';

    if ( is_file($file) ) {
      return $file;
    }

    $file = dirname($this->config['/']) . '/' .
           (isset($this->config['app']) ?: 'app') . '/' .
           $endpoint . '/default.php';

    if ( is_file($file) ) {
      return $file;
    }
  }

  # get com data by endpoint
  public function com_data($endpoint, $args = []) {
    $file = $this->com_file($endpoint);
    if ( $file ) {
      foreach ( $args as $k => $v ) {
        $$k = $v;
      }
      return include $file;
    }
    else {
      return [];
    }
  }

  # render com by endpoint
  public function com_render($endpoint, $args = []) {
    $tpl = $this->com_data($endpoint, $args);

    # by default - render html
    if ( true ) {
      return $this->render($tpl)[0];
    }
  }



  # render tag from params
  public function tag($tag, $html, $attrs = []) {
    if ( is_numeric($tag) ) {
      return $html;
    }

    if ( strpos($tag, ':') ) {
      $params = explode(':', $tag);
      $tag = array_shift($params);
      foreach ( $params as $param ) {
        $attrs['default'][] = $param;
      }
    }

    if ( preg_match_all('/\.([^:# ]+)/', $tag, $mm) ) {
      foreach ( $mm[1] as $class ) {
        $classes[] = str_replace('.', ' ', $class);
        $tag = str_replace('.' . $class, '', $tag);
      }

      isset($attrs['class']) ? $attrs['class'] .= ' ' : $attrs['class'] = '';
      $attrs['class'] .= implode(' ', $classes);
    }

    if ( preg_match_all('/\#([^:. ]+)/', $tag, $mm) ) {
      foreach ( $mm[1] as $id ) {
        $tag = str_replace('#' . $id, '', $tag);
        $attrs['id'] = $id;
      }
    }

    if ( !$tag ) {
      $tag = 'span';
    }

    if ( function_exists("phpy_post_render_{$tag}") ) {
      $custom_html = call_user_func_array("phpy_post_render_{$tag}", [&$html, &$attrs, $this]);
    }

    $attrs_html = $this->tag_attrs($attrs);

    return isset($custom_html) ? $custom_html :
           "<{$tag}{$attrs_html}>{$html}</{$tag}>";
  }

  # render tag attributes
  public function tag_attrs($attrs) {
    $pairs = [];
    foreach ( $attrs as $k => $v ) {
      $k = trim($k, ':');

      if ( $k == 'default' ) continue;
      if ( ($k == 'data') && is_array($v) ) {
        foreach ( $v as $data_k => $data_v ) {
          $pairs[] = 'data-' . $data_k . '="' . htmlspecialchars($data_v, ENT_COMPAT) .  '"';
        }
      }
      else {
        $pairs[] = $k . '="' . htmlspecialchars($v, ENT_COMPAT) .  '"';
      }
    }

    return $pairs ? ' ' . implode(' ', $pairs) : '';
  }

  # render html from phpy tpl
  public function render($t) {
    $html = '';
    $attrs = [];

    if ( is_array($t) ) {
      foreach ( $t as $kk => $tt ) {
        if ( substr($kk, 0, 1) == ':' ) {
          $attrs[$kk] = $tt;
        }
        else {
          $tag = preg_split('/(\.|:|#)/', $kk)[0];
          if ( function_exists("phpy_pre_render_{$tag}") ) {
            call_user_func_array("phpy_pre_render_{$tag}", [&$kk, &$tt, $this]);
          }

          list($in, $at) = $this->render($tt);
          $html .= $this->tag($kk, $in, $at);
        }
      }
    }
    else {
      $html = $t;
    }

    return [$html, $attrs];
  }



  /* Default routing handlers */
  protected static function collect($dir, $exts) {
    $c = '';

    foreach (glob($dir . '/*') as $f ) {
      if ( is_dir($f) ) $c .= self::collect($f, $exts);
      else if ( in_array(pathinfo($f, PATHINFO_EXTENSION), $exts) ) $c .= "\n" . file_get_contents($f);
    }

    return $c;
  }

  public static function css() {
    header('Content-type: text/css');
    $js = file_get_contents(__DIR__ . '/phpy.css') . self::collect((self::instance()->get('/') . '/../app'), ['css']);
    echo $js;
  }

  public static function js() {
    header('Content-type: application/javascript');
    $js = file_get_contents(__DIR__ . '/phpy.js') . self::collect((self::instance()->get('/') . '/../app'), ['js']);
    echo $js;
  }
}
