<?php

/**
 * @package framework.form
 */

/**
 * Validator
 *
 * @package framework.form
 */
class FWFormOptionsValidator extends FWFormValidator
{
  /**
   * Initialize validator
   *
   */
  public function init()
  {
    if(!$this->getOption('options'))
      throw new Exception('FWFormOptionsValidator: required option: options');
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
    if(!in_array($value, $this->getOption('options')))
      $this->error('invalid', 'invalid_option', null, $value);
    return $value;
  }
}

