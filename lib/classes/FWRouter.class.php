<?php

/**
 * @package framework
 */

/**
 * URL router
 *
 * @package framework
 */
class FWRouter
{
  protected $_routes = array();
  protected $_defaultRoute;
  protected $_defaultRouteParsed;
  protected $_baseURL = '';

  /**
   * Set base URL
   *
   * @param string $base_url
   */
  public function setBaseURL($base_url)
  {
    assert('is_string($base_url)');
    $this->_baseURL = $base_url;
  }

  /**
   * Connect
   *
   * @param string $url
   * @param array $defaults
   * @param array $requirements
   */
  public function connect($url, $defaults = array(), $requirements = array(), $route_name = null)
  {
    assert('is_string($url)');
    assert('is_array($defaults)');
    assert('is_array($requirements)');

    $url = preg_replace('|^/|', '', $url);
    $route = $this->_parseRoute($url, $defaults, $requirements);
    $route['url'] = $url;
    $route['defaults'] = $defaults;
    $route['requirements'] = $requirements;
    $this->_routes[$route_name ? $route_name : sizeof($this->_routes)] = $route;
  }

  /**
   * Set or remove default route
   *
   * @param string|null $route
   */
  public function setDefaultRoute($route)
  {
    if($route === null)
    {
      $this->_defaultRoute = null;
      $this->_defaultRouteParsed = null;
    }
    else
    {
      $route = preg_replace('|^/*(.*?)/*$|', '$1', $route);
      $this->_defaultRoute = $route;
      $this->_defaultRouteParsed = $this->_parseRoute($route);
    }
  }

  /**
   * Parse a route
   *
   * returns: array(
   *   'regex'          => '`^...$`i',
   *   'begin_regex'    => '`^...(?<__REST__>/.*|)$`i',
   *   'param_names'    => array('a', 'b', c'),
   *   'offsets'        => array(
   *      'a' => array(0, 10), // $offset, $length
   *      'b' => array(16, 2),
   *      ...
   *   ),
   * )
   *
   * @param string $route
   * @param array $defaults
   * @param array $requirements
   * @return array
   */
  protected function _parseRoute($route, $defaults = array(), $requirements = array())
  {
    assert('is_string($route)');
    assert('is_array($defaults)');
    assert('is_array($requirements)');

    // generate regex
    $regex = str_replace('\:', ':', preg_quote($route, '`'));
    preg_match_all('|:([0-9A-Za-z_]+)|', $regex, $param_matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
    $offset_adjust = 0;
    $param_names = array();
    $offsets = array();

    foreach($param_matches as $match)
    {
      $param_name = $match[1][0];
      $param_offset = $match[0][1];
      $param_length = strlen($match[0][0]);

      // check duplicate
      if(in_array($param_name, $param_names))
        throw new Exception('Duplicate param in url: ' . $param_name);
      if(isset($defaults[$param_name]))
        throw new Exception('Cannot set default value for param defined in url: ' . $param_name);

      // update regex
      $old_length = strlen($regex);
      $regex = substr_replace($regex, '(?<' . $param_name . '>[^/]*?)', $param_offset + $offset_adjust, $param_length);
      $new_length = strlen($regex);
      $offset_adjust += $new_length - $old_length;

      // store offset
      $offsets[$param_name] = array($param_offset, $param_length);
      $param_names[] = $param_name;
    }

    // check requirements
    foreach(array_keys($requirements) as $key)
      if(!in_array($key, $param_names))
        throw new Exception('Cannot set requirement for non-defined param: ' . $key);

    // generate complete regexes
    $begin_regex = '`^' . $regex . '(?<__REST__>/.*|)$`i';
    $regex = '`^' . $regex . '$`i';

    // return
    return array(
      'regex'           => $regex,
      'begin_regex'     => $begin_regex,
      'param_names'     => $param_names,
      'offsets'         => $offsets,
    );
  }

  /**
   * Route a url
   *
   * @param string $url
   * @return array
   */
  public function route($url)
  {
    $url = preg_replace('|^/|', '', $url);

    // try all routes
    foreach($this->_routes as $route)
    {
      if(!preg_match($route['regex'], $url, $matches)) continue;
      
      // get parameters
      $params = $route['defaults'];
      foreach($route['param_names'] as $name) $params[$name] = $this->_decode($matches[$name]);

      // check requirements
      foreach($route['requirements'] as $name => $req)
        if(!preg_match($req, $params[$name]))
          continue 2;

      // we've got a match!
      return $params;
    }

    // default route available?
    if($this->_defaultRoute !== null
       && preg_match($this->_defaultRouteParsed['begin_regex'], $url, $matches))
    {
      // get parameters
      $params = array();
      foreach($this->_defaultRouteParsed['param_names'] as $name)
        $params[$name] = $this->_decode($matches[$name]);
      
      // parse extra parameters
      $rest = explode('/', $matches['__REST__']);
      foreach($rest as $rest_val)
      {
        if(!preg_match('|^([A-Za-z0-9_]+)=(.*)$|', $rest_val, $matches)) continue;
        $params[$matches[1]] = $this->_decode($matches[2]);
      }

      // we've got a match!
      return $params;
    }

    // no match!
    return false;
  }

  /**
   * Generate an url
   *
   * @param array $params
   * @return string
   */
  public function assemble($params)
  {
    $param_keys = array_keys($params);

    // try to find a matching route
    foreach($this->_routes as $route)
    {
      // does this route have all specified parameters?
      foreach($param_keys as $key)
        if(!in_array($key, $route['param_names']) && !(isset($route['defaults'][$key]) && $route['defaults'][$key] == $params[$key]))
          continue 2; // next route

      // are all params set?
      foreach(array_merge($route['param_names'], array_keys($route['defaults'])) as $param)
        if(!in_array($param, $param_keys))
          continue 2; // next route

      // check requirements
      foreach($route['requirements'] as $key => $req)
        if(!preg_match($req, $params[$key]))
          continue 2; // next route

      // we've got a match
      $output = $route['url'];
      
      $offset_adjust = 0;
      foreach($params as $key => $value)
      {
        if(!isset($route['defaults'][$key]))
        {
          $old_length = strlen($output);
          $output = substr_replace($output, $this->_encode($value), $route['offsets'][$key][0] + $offset_adjust, $route['offsets'][$key][1]);
          $offset_adjust += strlen($output) - $old_length;
        }
      }

      // we're done
      return $this->_baseURL . $output;
    }

    // use the default route
    if($this->_defaultRoute !== null)
    {
      // all parameters given?
      foreach($this->_defaultRouteParsed['param_names'] as $key)
        if(!isset($params[$key]))
          throw new Exception('Specify param: ' . $key);

      // generate url
      $url = $this->_defaultRoute;
      
      $offset_adjust = 0;
      foreach($this->_defaultRouteParsed['offsets'] as $param_name => $offset)
      {
        $old_length = strlen($url);
        $url = substr_replace($url, $this->_encode($params[$param_name]), $offset[0] + $offset_adjust, $offset[1]);
        $offset_adjust += strlen($url) - $old_length;
        unset($params[$param_name]);
      }

      // add extra parameters
      foreach($params as $key => $value)
        $url .= '/' . $this->_encode($key) . '=' . $this->_encode($value);

      // done
      return $this->_baseURL . $url;
    }
  }

  /**
   * Encode parameter
   *
   * @param string $value
   * @return string
   */
  protected function _encode($value)
  {
    return str_replace('=20', '+', str_replace('%', '=', rawurlencode($value)));
  }

  /**
   * Decode parameter
   *
   * @param string $value
   * @return string
   */
  protected function _decode($value)
  {
    return rawurldecode(str_replace('=', '%', str_replace('+', '=20', $value)));
  }

  /**
   * Load routes from a YAML file
   *
   * @param string $filename
   */
  public function loadYAMLFile($filename)
  {
    require_once dirname(__FILE__) . '/../vendor/spyc/spyc.php';

    if(!is_file($filename)) throw new Exception('Route file not found.');
    if(!is_readable($filename)) throw new Exception('Route file not readable.');

    $array = Spyc::YAMLLoad($filename);
    if(!is_array($array)) throw new Exception('Cannot load YAML route file: ' . $filename);

    foreach($array as $route_name => $route)
    {
      if(!is_array($route)) $route = array('url' => $route);
      if(!isset($route['url'])) throw new Exception('url not set for route: ' . $route_name);
      
      if($route_name == 'default')
      {
        if(isset($route['requirements']) || isset($route['defaults']))
          throw new Exception('No defaults/requirements accepted for default route.');
        $this->setDefaultRoute($route['url']);
      }
      else
      {
        $this->connect(
          $route['url'],
          isset($route['defaults']) ? $route['defaults'] : array(),
          isset($route['requirements']) ? $route['requirements'] : array(),
          $route_name
        );
      }
    }
  }

  /**
   * Load routes from a FWRouter .conf file
   *
   * @param string $filename
   */
  public function loadConfFile($filename)
  {
    if(!is_file($filename)) throw new Exception('Route file not found.');
    if(!is_readable($filename)) throw new Exception('Route file not readable.');

    $fh = fopen($filename, 'rb');
    if(!$fh) throw new Exception('Couldn\'t open route file for reading.');

    $error = 'Syntax error in route file on line %d: %s';

    $ln = 0;
    $lines = array();
    while(($line = fgets($fh)) !== false)
    {
      $ln++;
      $line = preg_replace('|#.*$|', '', $line);
      $line = preg_replace('|\s+|', ' ', $line);
      $line = rtrim($line);
      if(!trim($line)) continue;

      // lines that begin with spaces continue the previous line
      if($line[0] == ' ')
      {
        // no previous line
        if(sizeof($lines) == 0)
        {
          throw new Exception(sprintf($error, $ln, 'Line continuation but no previous line'));
        }

        // append to previous line
        $lines[sizeof($lines) - 1]['line'] .= ' ' . trim($line);
      }
      else
      {
        $lines[] = array('ln' => $ln, 'line' => $line);
      }
    }

    foreach($lines as $line)
    {
      # "" -> index.index
      # /admin/ -> admin.index

      // $ln, $line
      extract($line);

      // $url = '/admin/';
      // $righthand = 'admin.index';
      $pieces = array_map('trim', explode('->', $line, 2));
      if(sizeof($pieces) != 2) throw new Exception(sprintf($error, $ln, 'No `->\''));
      list($url, $righthand) = $pieces;
      if(preg_match('|^"(.*)"$|', $url, $matches)) $url = $matches[1];

      // $controller = 'admin';
      // $action = 'index';
      $re_ident = '(?:[0-9A-Za-z_]+)';
      $re_hash = '(?:{.*})';
      $re_req = '(?:r' . $re_hash . ')';
      $re_def = '(?:d' . $re_hash . ')';

      if(!$righthand) throw new Exception(sprintf($error, $ln, 'Empty right hand'));
      if(!preg_match("!
          ^
          (?<controller>{$re_ident})\\.(?<action>{$re_ident})

          (?:
            (?:\s+(?<defa>{$re_def}))?
            (?:\s+(?<reqa>{$re_req}))?
            |
            (?:\s+(?<reqb>{$re_req}))?
            (?:\s+(?<defb>{$re_def}))?
          )
          $
        !x" , $righthand, $matches))
        throw new Exception(sprintf($error, $ln, 'Invalid right hand'));
      $controller = $matches['controller'];
      $action = $matches['action'];

      $m_requirements = isset($matches['reqb']) ? $matches['reqb'] : (isset($matches['reqa']) ? $matches['reqa'] : '');
      $m_defaults = isset($matches['defb']) ? $matches['defb'] : (isset($matches['defa']) ? $matches['defa'] : '');

      // $requirements = array(...);
      // $defaults = array(...);
      if($m_requirements)
        $requirements = eval(preg_replace('|^r{(.*)}$|', 'return array($1);', $m_requirements));
      else
        $requirements = array();

      $defaults = array('controller' => $controller, 'action' => $action);
      if($m_defaults)
        $defaults = array_merge(eval(preg_replace('|^d{(.*)}$|', 'return array($1);', $m_defaults)), $defaults);

      // connect
      $this->connect($url, $defaults, $requirements);
    }
  }
}

