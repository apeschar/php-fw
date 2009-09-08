<?php

/**
 * @package framework.form
 */

/**
 * Renders a form using tr/th/td elements
 *
 * @package framework.form
 */
class FWFormTableRenderer extends FWFormRenderer
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
      $o .= "<tr>\n  <th>\n";
      $o .= sprintf("    <label for=\"%s\">%s:</label>\n", htmlentities($widget->getId()), htmlentities($this->getForm()->getLabel($widget_name), ENT_QUOTES, 'UTF-8'));
      $o .= "  </th>\n  <td>\n";
      $o .= "    " . $widget->render() . "\n";
      if($e = $this->getForm()->getError($widget_name)) $o .= sprintf("    <div class=\"error\">%s.</div>\n", htmlentities(ucfirst($e), ENT_QUOTES, 'UTF-8'));
      $o .= "  </td>\n</tr>\n";
    }

    $o .= "\n";

    return $o;
  }
}

