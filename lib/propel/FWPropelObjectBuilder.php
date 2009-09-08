<?php

require_once 'propel/engine/builder/om/php5/PHP5ObjectBuilder.php';

class FWPropelObjectBuilder extends PHP5ObjectBuilder
{
  /**
   * Adds the function body for the save method
   * @param      string &$script The script will be modified in this method.
   * @see        addSave()
   **/
  protected function addSaveBody(&$script) {
    $add = false;
    $columns = $this->getTable()->getColumns();
    
    // created_at, updated_at columns
    $added_now = false;
    $set_now = "\n\t\t\$now = new DateTime('now');";
    foreach($columns as $column)
    {
      if($column->getPhpName() == 'CreatedAt' && $column->getType() == 'TIMESTAMP')
      {
        $add = true;
        if(!$added_now)
        {
          $script .= $set_now;
          $added_now = true;
        }
        $script .= "\n\t\tif(!\$this->getCreatedAt()) \$this->setCreatedAt(clone \$now);";
      }
      elseif($column->getPhpName() == 'UpdatedAt' && $column->getType() == 'TIMESTAMP')
      {
        $add = true;
        if(!$added_now)
        {
          $script .= $set_now;
          $added_now = true;
        }
        $script .= "\n\t\t\$this->setUpdatedAt(clone \$now);";
      }
    }

    // pass it on
    if($add) $script .= "\n";
    parent::addSaveBody($script);
  }
}

