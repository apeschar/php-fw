<?php

/**
 * @package framework.form
 */

/**
 * Text field widget
 *
 * @package framework.form
 */
class FWFormTextField extends FWFormWidget
{
  /**
   * Initialize field
   *
   */
  protected function init()
  {
  }

  /**
   * Render field
   *
   * @return string
   */
  public function render()
  {
    $o  = '<input' . $this->getNameAttr() . $this->getIdAttr();
    if($this->getValue()) $o .= sprintf(' value="%s" ', htmlentities($this->getValue(), ENT_QUOTES, 'UTF-8'));
    $o .= '/>';
    return $o;
  }
}

