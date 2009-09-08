<?php

/**
 * @package framework.form
 */

/**
 * E-mail address validator
 *
 * @package framework.form
 */
class FWFormEmailValidator extends FWFormValidator
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
    if($this->getOption('trim'))
      $value = trim($value);
    
    // use the extremely long and complex regex from Mail::RFC822::Address (CPAN)
    static $regex;

    if(!isset($regex))
    {
      $regex = file_get_contents(dirname(__FILE__) . '/../../../data/form/RFC822-email-regex.txt');
      $regex = str_replace("\n", "", rtrim($regex));
    }

    if(!preg_match("/$regex/", $value))
      $this->error('invalid', 'invalid_email_address', null, $value);

    return $value;
  }
}

