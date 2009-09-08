<?php

/**
 * @package framework
 */

/**
 * Dwoo
 */
require_once dirname(__FILE__) . '/../vendor/dwoo/lib/dwooAutoload.php';

/**
 * Template class
 *
 * @package framework
 */
class FWTemplate extends Dwoo
{
  /**
   * Constructor
   *
   */
  public function __construct()
  {
    $compile_dir = FW_CACHE_DIR . '/dwoo_compiled';
    parent::__construct($compile_dir);
  }
}

/**
 * Dwoo plugin to generate and return a url
 *
 * @param Dwoo_Compiler $dwoo
 * @param array $rest
 */
function Dwoo_Plugin_url_compile(Dwoo_Compiler $dwoo, array $rest = array())
{
  foreach($rest as $key => $value)
  {
    if($key === 0 && !isset($rest['controller']))
    {
      $rest['controller'] = $value;
      unset($rest[$key]);
    }
    elseif($key === 1 && !isset($rest['action']))
    {
      $rest['action'] = $value;
      unset($rest[$key]);
    }
  }

  if(!isset($rest['controller'])) throw new Exception('No controller.');
  if(!isset($rest['action'])) throw new Exception('No action.');

  // generate array(...)
  $array = 'array(';
  foreach($rest as $key => $value) $array .= var_export($key, true) . '=>' . $value . ',';
  $array .= ')';

  // generate function call
  $return = 'FWContext::getRouter()->assemble(' . $array . ')';

  return $return;
}

/**
 * Dwoo plugin to help with menu creation
 *
 * @package framework
 */
class Dwoo_Plugin_menu extends Dwoo_Block_Plugin implements Dwoo_ICompilable_Block
{
  public function init(array $rest)
  {
  }

  public static function preProcessing(Dwoo_Compiler $compiler, array $params, $prepend, $append, $type)
  {
    return '';
  }

  public static function postProcessing(Dwoo_Compiler $compiler, array $params, $prepend, $append, $content)
  {
    $params = $compiler->getCompiledParams($params);
    $params = isset($params['*']) ? $params['*'] : array();

    $params_php = array();
    foreach($params as $key => $val) $params_php[] = var_export($key, true) . ' => ' . $val;
    $params_php = 'array(' . implode(', ', $params_php) . ')';

    $pre = Dwoo_Compiler::PHP_OPEN . 'ob_start();' . Dwoo_Compiler::PHP_CLOSE;
    $post = Dwoo_Compiler::PHP_OPEN . __CLASS__ . '::_process(ob_get_clean(), ' . $params_php . ');' . Dwoo_Compiler::PHP_CLOSE;

    return $pre . $content . $post;
  }

  public static function _process($content, $params)
  {
    // get parameters
    $active = isset($params['active']) ? $params['active'] : null;
    $active_class = isset($params['active_class']) ? $params['active_class'] : null;
    $active_id = isset($params['active_id']) ? $params['active_id'] : null;
    $link_class = isset($params['link_class']) ? $params['link_class'] : null;

    // parse content
    $cache = FWCache::getInstance();
    $cache_key = 'dwoo_menu_' . md5($content);

    if(!($parsed = $cache->get($cache_key)))
    {
      $parsed = self::_parse($content);
      $cache->set($cache_key, $parsed, 3600);
    }

    extract($parsed);

    // process links
    $search = $replace = array();
    
    foreach($links as $link)
    {
      $classes = array();
      $id = null;

      // add class to all links
      if($link_class)
      {
        $classes[] = $link_class;
      }

      // active?
      if($active && $link['id'] == $active)
      {
        if($active_class) $classes[] = $active_class;
        if($active_id) $id = $active_id;
      }

      // replacements
      $classes = implode(' ', $classes);
      $search[] = $link['placeholders']['class'];
      $replace[] = $classes;

      if($id)
      {
        $search[] = $link['placeholders']['id'];
        $replace[] = $id;
      }
      else
      {
        $search[] = 'id="' . $link['placeholders']['id'] . '"';
        $replace[] = '';
      }
    }

    // execute search/replace
    if($search)
    {
      $html = str_replace($search, $replace, $html);
    }

    echo $html;
  }

  private static function _parse($content)
  {
    // create DOMDocument instance
    $dom = new DOMDocument;
    if(!$dom->loadXML('<root>' . $content . '</root>'))
      throw new Exception('menu plugin: PHP DOM couldn\'t parse menu XML.');
    $dom->normalizeDocument();

    // process <a> elements
    $dom_links = $dom->getElementsByTagName('a');
    $links = array();

    foreach($dom_links as $dom_link)
    {
      // get `l' attribute
      if($dom_link->hasAttribute('l'))
      {
        $link_id = trim($dom_link->getAttribute('l'));
        if(!$link_id) throw new Exception('menu plugin: link id cannot be empty.');
        $dom_link->removeAttribute('l');
      }
      else
      {
        $link_id = null;
      }

      // add placeholders
      $placeholders = array();

      $placeholders['class'] = '--' . FWU::randomString() . '--';
      if($dom_link->hasAttribute('class'))
      {
        $dom_link->setAttribute('class', trim($dom_link->getAttribute('class') . ' ' . $placeholders['class']));
      }
      else
      {
        $dom_link->setAttribute('class', $placeholders['class']);
      }

      if(!$dom_link->hasAttribute('id'))
      {
        $placeholders['id'] = '--' . FWU::randomString() . '--';
        $dom_link->setAttribute('id', $placeholders['id']);
      }
      else
      {
        $placeholders['id'] = null;
      }

      // add to $links array
      $links[] = array(
        'id'            => $link_id,
        'placeholders'  => $placeholders,
      );
    }

    // done
    return array(
      'links'           => $links,
      'html'            => preg_replace('|^\s*<\?xml version="1.0"\?>\s*<root>(.*)</root>\s*$|s', '$1', $dom->saveXML()),
    );
  }
}

FWErrorHandler::relax(E_WARNING, dirname(__FILE__) . '/../vendor/dwoo', 'No such file or directory');

