<?php

/**
 * @package framework.form
 */

/**
 * Form widget
 *
 * @package framework.form
 */
abstract class FWFormWidget
{
  /**
   * @var string
   */
  private $_name;

  /**
   * @var string
   */
  private $_id;

  /**
   * @var mixed
   */
  private $_value;

  /**
   * @var array
   */
  private $_options;

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
   * Initialize field
   *
   */
  abstract protected function init();

  /**
   * Get option
   *
   * @param string $name
   * @return mixed
   */
  final public function getOption($name)
  {
    if(isset($this->_options[$name]))
      return $this->_options[$name];
  }

  /**
   * Set id
   *
   * @param string $id
   * @return void
   */
  final public function setId($id)
  {
    assert('is_string($id)');
    $this->_id = $id;
  }

  /**
   * Get id
   *
   * @return null|string
   */
  final public function getId()
  {
    if(isset($this->_id))
      return $this->_id;
  }

  /**
   * Set name
   *
   * @param string $name
   * @return void
   */
  final public function setName($name)
  {
    assert('is_string($name)');
    $this->_name = $name;
  }

  /**
   * Get name
   *
   * @return null|string
   */
  final public function getName()
  {
    if(isset($this->_name))
      return $this->_name;
  }

  /**
   * Set value
   *
   * @param mixed $value
   * @return void
   */
  final public function setValue($value)
  {
    $this->_value = $value;
  }

  /**
   * Get value
   *
   * @return mixed
   */
  final public function getValue()
  {
    if(isset($this->_value))
      return $this->_value;
  }

  /**
   * Get id attribute
   *
   * @return string
   */
  final protected function getIdAttr()
  {
    if($id = $this->getId())
      return sprintf(' id="%s" ', htmlentities($id, ENT_QUOTES, 'UTF-8'));
    else
      return ' ';
  }

  /**
   * Get name attribute
   *
   * @return string
   */
  final protected function getNameAttr()
  {
    if($name = $this->getName())
      return sprintf(' name="%s" ', htmlentities($name, ENT_QUOTES, 'UTF-8'));
    else
      return ' ';
  }

  /**
   * Get value from data source
   *
   * @param array $source
   * @param string $name
   */
  public function getValueFromSource($source, $name)
  {
    assert('is_array($source)');
    assert('is_string($name)');

    if(isset($source[$name])) return $source[$name];
  }

  /**
   * Render field
   *
   * @return string
   */
  abstract public function render();
}

