<?php

/** 
 * @package framework
 */

/**
 * Represents HTTP response
 *
 * @package framework
 */
class FWResponse
{
  /**
   * @var array
   */
  protected $_headers = array();

  /**
   * @var string
   */
  protected $_content;

  /**
   * Constructor
   *
   * @param string $content
   * @param array $headers
   */
  public function __construct($content = null, array $headers = array())
  {
    if($content !== null)
    {
      $this->setContent($content);
    }

    foreach($headers as $name => $value)
    {
      $this->addHeader($name, $value);
    }
  }

  /**
   * Set content
   * 
   * @param string $content
   */
  public function setContent($content)
  {
    $this->_content = (string) $content;
  }

  /**
   * Add a header
   *
   * @param string $header
   * @param string $value
   */
  public function addHeader($header, $value)
  {
    if(!is_string($header))
    {
      throw new Exception('Header name should be a string.');
    }
    if(!is_string($value))
    {
      throw new Exception('Value should be a string.');
    }

    $header = $this->_headerCase($header);
    $this->_headers[] = array($header, $value);
  }

  /**
   * Set or remove a header
   *
   * @param string $header
   * @param string|null $value
   */
  public function setHeader($header, $value)
  {
    if(!is_string($header))
    {
      throw new Exception('Header name should be a string.');
    }
    if(!is_string($value) && !is_null($value))
    {
      throw new Exception('Value should be a string or null.');
    }

    $header = $this->_headerCase($header);

    foreach($this->_headers as $key => $elem)
    {
      if($elem[0] == $header)
      {
        unset($this->_headers[$key]);
      }
    }

    if(is_string($value))
    {
      $this->_headers[] = array($header, $value);
    }
  }

  /**
   * Output headers
   * 
   */
  public function flushHeaders()
  {
    $output = array();

    foreach($this->_headers as $header)
    {
      if(in_array($header[0], $output))
      {
        $replace = false;
      }
      else
      {
        $replace = true;
        $output[] = $header[0];
      }

      header(sprintf('%s: %s', $header[0], $header[1]), $replace);
    }
  }

  /**
   * Output content
   * 
   */
  public function flushContent()
  {
    if($this->_content)
    {
      echo $this->_content;
    }
  }

  /**
   * Output headers and content
   *
   */
  public function flush()
  {
    $this->flushHeaders();
    $this->flushContent();
  }

  /**
   * Do proper case transformations on header name
   *
   * @param string $name
   * @return string
   */
  protected function _headerCase($name)
  {
    return preg_replace('#(?:^|-)(?:[a-z])#e', 'strtoupper("$0")', strtolower($name));
  }
}

