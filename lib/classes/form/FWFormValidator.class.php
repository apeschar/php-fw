<?php

/**
 * @package framework.form
 */

/**
 * Validator base class
 *
 * @package framework.form
 */
abstract class FWFormValidator
{
  /**
   * @var array
   */
  private $_options;

  /**
   * @var FWForm
   */
  private $_form;

  /**
   * Constructor
   *
   * @param array $options
   */
  final public function __construct($options = array())
  {
    assert('is_array($options)');
    $this->_options = $options;
    $this->init();
  }

  /**
   * Get option
   *
   * @param string $name
   * @return mixed
   */
  final public function getOption($name)
  {
    assert('is_string($name)');
    if(isset($this->_options[$name]))
      return $this->_options[$name];
  }

  /**
   * Initialize validator
   *
   * @return void
   */
  abstract public function init();

  /**
   * Validate and sanitize specified value
   *
   * @param mixed $value
   * @return mixed
   * @throws FWFormValidationException
   */
  abstract public function sanitize($value);

  /**
   * Throw validation exception
   *
   * @param string $short_error_name
   * @param string $long_error_name
   * @param array|null $arguments
   * @param mixed $value
   */
  final protected function error($short_error_name, $long_error_name, $arguments, $value)
  {
    assert('is_string($short_error_name)');
    assert('is_string($long_error_name)');
    assert('is_null($arguments) || is_array($arguments)');

    // add options to $arguments
    if(!is_array($arguments)) $arguments = array();
    $arguments = array_merge($this->_options, $arguments);

    // get error format
    $short_error_key = $short_error_name . '_error';
    if(!($format = $this->getOption($short_error_key)))
    {
      $long_error_key = $long_error_name . '_error';
      $format = FWI18N::get('FWFormValidator', $long_error_key);
      if(!$format) $format = 'is invalid';
    }

    // generate message
    $search = $replace = array();
    foreach($arguments as $s => $r)
    {
      if(!is_object($r) && !is_array($r))
      {
        $search[] = '{' . $s . '}';
        $replace[] = $r;
      }
    }
    $message = str_replace($search, $replace, $format);

    // throw exception
    $e = new FWFormValidationException($message);
    $e->sanitizedValue = $value;
    throw $e;
  }

  /**
   * Set form
   *
   * @param FWForm $form
   */
  public function setForm(FWForm $form)
  {
    $this->_form = $form;
  }

  /**
   * Get form
   *
   * @return FWForm|null
   */
  public function getForm()
  {
    if($this->_form)
      return $this->_form;
  }
}

