<?php

/**
 * @package framework.form
 */

/**
 * Renders a form using p elements
 *
 * @package framework.form
 */
class FWFormParagraphRenderer extends FWFormRenderer
{
  /**
   * Render form
   *
   * @return string
   */
  public function render()
  {
    $o = "\n";

    foreach($this->getForm()->getWidgets() as $widget_name => $widget)
    {
      $o .= "<p>\n";
      $o .= sprintf("  <label for=\"%s\">%s:</label><br/>\n", htmlentities($widget->getId()), htmlentities($this->getForm()->getLabel($widget_name), ENT_QUOTES, 'UTF-8'));
      if($e = $this->getForm()->getError($widget_name)) $o .= sprintf("  <span class=\"error\">%s.</span><br/>\n", htmlentities(ucfirst($e), ENT_QUOTES, 'UTF-8'));
      $o .= "  " . $widget->render() . "\n";
      $o .= "</p>\n";
    }

    $o .= "\n";

    return $o;
  }
}

