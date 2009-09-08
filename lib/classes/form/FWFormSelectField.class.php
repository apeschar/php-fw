<?php

/**
 * @package framework.form
 */

/**
 * Select field widget
 *
 * @package framework.form
 */
class FWFormSelectField extends FWFormWidget
{
  /**
   * Initialize field
   *
   */
  protected function init()
  {
    if(!$this->getOption('options'))
      throw new Exception('A select box needs options.');
  }

  /**
   * Render field
   *
   * @return string
   */
  public function render()
  {
    $o = '<select ' . $this->getNameAttr() . $this->getIdAttr() . '>';
    foreach($this->getOption('options') as $key => $value)
    {
      $style = '';
      if($this->getOption('tree'))
      {
        $depth = strlen($key) - strlen($key = ltrim($key, '_'));

        if(strpos($key, '/') === 0)
        {
          $nohead = true;
          $key = substr($key, 1);
        }
        else
        {
          $nohead = false;
        }

        if(!$nohead)
        {
          if($depth > 0)
            $style .= 'margin-left:' . ($depth * 5) . 'px;';
          else
            $style .= 'font-weight:bold;background:#b9b9b9;';
        }
      }

      $o .= '<option value="' . htmlentities($key) . '"';
      if($this->getValue() == $key) $o .= ' selected="selected"';
      if($style) $o .= ' style="' . htmlentities($style) . '"';
      $o .= '>' . ($this->getOption('no_entities') ? $value : htmlentities($value)) . '</option>';
    }
    $o .= '</select>';
    return $o;
  }
}

