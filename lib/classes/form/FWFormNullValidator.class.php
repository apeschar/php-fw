<?php

/**
 * @package framework.form
 */

/**
 * Validator that doesn't do anything
 *
 * @package framework.form
 */
class FWFormNullValidator extends FWFormValidator
{
  /**
   * Initialize validator
   *
   */
  public function init()
  {
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
    return $value;
  }
}

