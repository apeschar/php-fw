<?php

/**
 * @package framework.form
 */

/**
 * Form class
 *
 * @package framework.form
 */
abstract class FWForm
{
  /**
   * @var array
   */
  private $_widgets;

  /**
   * @var array
   */
  private $_validators;

  /**
   * @var FWFormRenderer
   */
  private $_renderer;

  /**
   * @var string
   */
  private $_nameFormat = 'form[%s]';

  /**
   * @var string
   */
  private $_idFormat = null;

  /**
   * @var array
   */
  private $_labels = array();

  /**
   * @var array
   */
  private $_values;

  /**
   * @var array
   */
  private $_sanitizedValues;

  /**
   * Constructor
   *
   */
  final public function __construct()
  {
    $this->setRenderer(new FWFormParagraphRenderer());
    $this->setUp();
  }

  /**
   * Set up
   *
   * @return void
   */
  abstract protected function setUp();

  /**
   * Set widgets
   *
   * @param array $widgets
   */
  final public function setWidgets($widgets)
  {
    if(isset($this->_widgets)) throw new Exception('Widgets already set.');
    
    foreach($widgets as $name => $widget)
    {
      if(!is_string($name)) throw new Exception('Widget doesn\'t have a name.');
      if(!$widget instanceof FWFormWidget) throw new Exception('Invalid widget: ' . $name);
    }

    $this->_widgets = $widgets;
  }

  /**
   * Get widgets
   *
   * @return array|null
   */
  final public function getWidgets()
  {
    return $this->_widgets;
  }

  /**
   * Set validators
   *
   * @param array $validators
   */
  final public function setValidators($validators)
  {
    if(!isset($this->_widgets)) throw new Exception('You need to set the widgets before setting validators.');
    
    foreach($validators as $name => $validator)
    {
      if(!is_string($name)) throw new Exception('Validator doesn\'t have a field name.');
      if(!isset($this->_widgets[$name])) throw new Exception('Field for validator does not exist: ' . $name);
      if(!$validator instanceof FWFormValidator) throw new Exception('Invalid validator: ' . $name);
      $validator->setForm($this);
    }

    foreach($this->_widgets as $name => $widget)
    {
      if(!isset($validators[$name])) throw new Exception('No validator for field: ' . $name);
    }

    $this->_validators = $validators;
  }

  /**
   * Set renderer
   *
   * @param FWFormRenderer $renderer
   */
  final public function setRenderer(FWFormRenderer $renderer)
  {
    $renderer->setForm($this);
    $this->_renderer = $renderer;
  }

  /**
   * Get renderer
   *
   * @return FWFormRenderer
   */
  final private function getRenderer()
  {
    return $this->_renderer;
  }

  /**
   * Render form
   *
   * @return string
   */
  final public function render()
  {
    if(!$this->getWidgets()) throw new Exception('Cannot render form without widgets.');

    foreach($this->getWidgets() as $widget_name => $widget)
    {
      $widget->setName($this->formatName($widget_name));
      $widget->setId($this->formatId($widget_name));
      $widget->setValue($this->getValue($widget_name));
    }

    $renderer = $this->getRenderer();
    return $renderer->render();
  }

  /**
   * Format widget name
   *
   * @param string $widget_name
   * @return string
   */
  final protected function formatName($widget_name)
  {
    return sprintf($this->_nameFormat, $widget_name);
  }

  /**
   * Format widget id
   *
   * @param string $widget_name
   * @return string
   */
  final protected function formatId($widget_name)
  {
    if(isset($this->_idFormat))
    {
      return sprintf($this->_idFormat, $widget_name);
    }
    else
    {
      $name = $this->formatName($widget_name);
      return preg_replace('|_$|', '', preg_replace('|[\[\]]|', '_', $name));
    }
  }

  /**
   * Set name format
   *
   * @param string $format
   */
  final public function setNameFormat($format)
  {
    assert('is_string($format)');
    $this->_nameFormat = $format;
  }

  /**
   * Set id format
   *
   * @param string|null $format
   */
  final public function setIdFormat($format)
  {
    assert('is_null($format) || is_string($format)');
    $this->_idFormat = $format;
  }

  /**
   * Set widget label
   *
   * @param string $widget_name
   * @param string $label
   */
  final public function setLabel($widget_name, $label)
  {
    assert('is_string($widget_name)');
    assert('is_string($label)');

    if(!isset($this->_widgets[$widget_name])) throw new Exception('No such widget: ' . $widget_name);

    $this->_labels[$widget_name] = $label;
  }

  /**
   * Set multiple widget labels
   *
   * @param array $labels
   */
  final public function setLabels($labels)
  {
    assert('is_array($labels)');
    foreach($labels as $widget_name => $label) $this->setLabel($widget_name, $label);
  }

  /**
   * Get widget label
   *
   * @param string $widget_name
   * @return string
   */
  final public function getLabel($widget_name)
  {
    assert('is_string($widget_name)');

    if(!isset($this->_widgets[$widget_name])) throw new Exception('No such widget: ' . $widget_name);
    if(isset($this->_labels[$widget_name])) return $this->_labels[$widget_name];

    $label = str_replace('_', ' ', $widget_name);
    $this->setLabel($widget_name, $label);
    return $label;
  }

  /**
   * Bind values
   *
   * @param array $values
   */
  final public function bind($values)
  {
    if(!is_array($values)) $values = array();
    $this->_values = $values;
  }

  /**
   * Validate input
   *
   * @return bool
   */
  final public function isValid()
  {
    if(!isset($this->_values)) throw new Exception('First bind() values.');
    if(!isset($this->_widgets)) throw new Exception('First set widgets.');
    if(!isset($this->_validators)) throw new Exception('First set validators.');

    $errors = array();

    foreach($this->getWidgets() as $widget_name => $widget)
    {
      $value = $widget->getValueFromSource($this->_values, $widget_name);
      $validator = $this->_validators[$widget_name];

      try
      {
        $this->_sanitizedValues[$widget_name] = $validator->sanitize($value);
      }
      catch(FWFormValidationException $e)
      {
        $errors[$widget_name] = $e->getMessage();
        $this->_sanitizedValues[$widget_name] = $e->sanitizedValue;
      }
    }

    if(empty($errors))
    {
      return true;
    }
    else
    {
      $this->_errors = $errors;
      return false;
    }
  }

  /**
   * Get sanitized value
   *
   * @param string $name
   * @return mixed
   */
  public function getValue($name)
  {
    if(!isset($this->_sanitizedValues)) return null;
    if(isset($this->_sanitizedValues[$name])) return $this->_sanitizedValues[$name];
  }

  /**
   * Get sanitized value
   *
   * @param string $name
   * @return mixed
   */
  public function __get($name)
  {
    return $this->getValue($name);
  }

  /**
   * Get tainted value
   *
   * @param string $name
   * @return mixed
   */
  public function getTaintedValue($name)
  {
    if(!isset($this->_values)) return null;
    if(isset($this->_values[$name])) return $this->_values[$name];
  }

  /**
   * Get error
   *
   * @param string $name
   * @return null|string
   */
  public function getError($name)
  {
    if(isset($this->_errors) && isset($this->_errors[$name]))
      return $this->_errors[$name];
  }

  /**
   * Set error
   *
   * @param string $field
   * @param string $error
   * @return void
   */
  public function setError($field, $error)
  {
    $errors = $this->_errors;
    $errors[$field] = $error;
    $this->_errors = $error;
  }
}

FWIncludePath::prepend(dirname(__FILE__));

