<?php

/**
 * @package framework.form
 */

/**
 * String validator
 *
 * @package framework.form
 */
class FWFormIdentifierValidator extends FWFormValidator
{
  /**
   * Initialize validator
   *
   */
  public function init()
  {
    // check option types
    foreach(array('min', 'max') as $opt)
    {
      if($this->getOption($opt) && !is_int($this->getOption($opt)))
      {
        throw new Exception('Option `' . $opt . '\' should be an integer.');
      }
    }
  }

  /**
   * Validate and sanitize specified value
   *
   * @param mixed $value
   * @return mixed
   * @throws FWFormValidationException
   */
  public function sanitize($value)
  {
    if($this->getOption('trim'))
      $value = trim($value);

    if($this->getOption('min') !== null && strlen($value) < $this->getOption('min'))
      $this->error('min', 'string_too_short', array('length' => strlen($value)), $value);
    if($this->getOption('max') !== null && strlen($value) > $this->getOption('max'))
      $this->error('max', 'string_too_long', array('length' => strlen($value)), $value);

    if(!preg_match('|^[a-z0-9_]+$|', $value))
      $this->error('invalid', 'invalid_identifier', array('characters' => 'a-z, 0-9, _'), $value);

    return $value;
  }
}

