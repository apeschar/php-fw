<?php

/**
 * @package framework.form
 */

/**
 * Confirmation validator
 *
 * @package framework.form
 */
class FWFormConfirmValidator extends FWFormValidator
{
  /**
   * Initialize validator
   *
   */
  public function init()
  {
    if(!$this->getOption('other'))
      throw new Exception('Option `other\' should specify another field.');
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
    $other_value = $this->getForm()->getTaintedValue($this->getOption('other'));
    if($value != $other_value)
      $this->error('max', 'invalid_confirmation', null, $value);

    return $value;
  }
}

