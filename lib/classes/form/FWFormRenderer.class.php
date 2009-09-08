<?php

/**
 * @package framework.form
 */

/**
 * Form renderer base class
 *
 * @package framework.form
 */
abstract class FWFormRenderer
{
  /**
   * @var FWForm
   */
  private $_form;

  /**
   * Render form
   *
   * @return string
   */
  abstract public function render();

  /**
   * Set FWForm object
   *
   * @param FWForm $form
   */
  final public function setForm(FWForm $form)
  {
    $this->_form = $form;
  }

  /**
   * Get FWForm object
   *
   * @return FWForm
   */
  final protected function getForm()
  {
    return $this->_form;
  }
}

