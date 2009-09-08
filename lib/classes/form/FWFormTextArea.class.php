<?php

/**
 * @package framework.form
 */

/**
 * Text area widget
 *
 * @package framework.form
 */
class FWFormTextArea extends FWFormWidget
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
    $o  = '<textarea' . $this->getNameAttr() . $this->getIdAttr();
    $o .= 'rows="5" cols="30"';
    $o .= '>';
    if($this->getValue()) $o .= htmlentities($this->getValue(), ENT_QUOTES, 'UTF-8');
    $o .= '</textarea>';
    return $o;
  }
}

