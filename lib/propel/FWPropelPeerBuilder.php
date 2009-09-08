<?php

require_once 'propel/engine/builder/om/php5/PHP5PeerBuilder.php';

class FWPropelPeerBuilder extends PHP5PeerBuilder
{
  /**
  * Closes class.
  * Adds closing brace at end of class and the static map builder registration code.
  * @param      string &$script The script will be modified in this method.
  * @see        addStaticMapBuilderRegistration()
  */
  public function addClassClose(&$script)
  {
    // create retrieveBy* functions
    $methods_created = array();
    foreach($this->getTable()->getUnices() as $index)
    {
      // get Column
      if(sizeof($index->getColumns()) != 1) continue;
      $column_name = $index->getColumns();
      $column_name = $column_name[0];
      $column = $this->getTable()->getColumn($column_name);
      if(!$column) continue;

      // method already created?
      if(in_array($column, $methods_created)) continue;
      $methods_created[] = $column;

      // create retrieveBy method
      $table_phpname = $this->getTable()->getPhpName();
      $phpname = $column->getPhpName();
      $peerclass = $this->getPeerClassName();
      $colconst = $this->getColumnConstant($column);

      $script .= "
\t/**
\t * Retrieve a single object by {$phpname}
\t *
\t * @param      mixed \${$phpname} the {$phpname}
\t * @param      PropelPDO \$con the connection to use
\t * @return     {$table_phpname}
\t */
\tpublic static function retrieveBy{$phpname}(\${$phpname}, PropelPDO \$con = null)
\t{
\t\tif(\$con === null) {
\t\t\t\$con = Propel::getConnection({$peerclass}::DATABASE_NAME);
\t\t}
\t\t
\t\t\$criteria = new Criteria({$peerclass}::DATABASE_NAME);
\t\t\$criteria->add({$colconst}, \${$phpname});
\t\t
\t\t\$v = {$peerclass}::doSelect(\$criteria, \$con);
\t\t
\t\treturn !empty(\$v) ? \$v[0] : null;
\t}
";
    }

    // create findBy* functions
    $methods_created = array();
    $columns = array();

    foreach($this->getTable()->getIndices() as $index)
    {
      if(sizeof($index->getColumns()) != 1) continue;
      $column_name = $index->getColumns();
      $column_name = $column_name[0];
      $column = $this->getTable()->getColumn($column_name);
      if(!$column) continue;
      $columns[] = $column;
    }

    foreach($this->getTable()->getForeignKeys() as $fk)
    {
      if(sizeof($fk->getLocalColumns()) != 1) continue;
      $column_name = $fk->getLocalColumns();
      $column_name = $column_name[0];
      $column = $this->getTable()->getColumn($column_name);
      if(!$column) continue;
      $columns[] = $column;
    }

    foreach($columns as $column)
    {
      // method already created
      if(in_array($column, $methods_created)) continue;
      $methods_created[] = $column;

      // create findBy method
      $table_phpname = $this->getTable()->getPhpName();
      $phpname = $column->getPhpName();
      $peerclass = $this->getPeerClassName();
      $colconst = $this->getColumnConstant($column);

      $script .= "
\t/**
\t * Retrieve multiple objects by {$phpname}
\t *
\t * @param      mixed \${$phpname} the {$phpname}
\t * @param      PropelPDO \$con the connection to use
\t * @param      Criteria \$criteria additional criteria to use
\t * @return     array
\t */
\tpublic static function findBy{$phpname}(\${$phpname}, PropelPDO \$con = null, Criteria \$criteria = null)
\t{
\t\tif(\$con === null) {
\t\t\t\$con = Propel::getConnection({$peerclass}::DATABASE_NAME);
\t\t}
\t\t
\t\tif(\$criteria === null) {
\t\t\t\$criteria = new Criteria;
\t\t}
\t\t
\t\t\$criteria->add({$colconst}, \${$phpname});
\t\t
\t\treturn {$peerclass}::doSelect(\$criteria, \$con);
\t}
";
    }

    // create find()
    $this->addScript($script, 'FWPropelPeerBuilder/find.php');

    parent::addClassClose($script);
  }

  private function addScript(&$script, $filename, array $args = array())
  {
    $args['peerclass'] = $this->getPeerClassName();

    $insert = file_get_contents(dirname(__FILE__) . '/../../data/propel/' . $filename);
    $insert = trim(preg_replace('|^<\?php|i', '', $insert));
    $insert = preg_replace('|^(?:  )*|me', 'str_repeat("\t", (strlen("$0") / 2) + 1)', $insert);
    $insert .= "\n";

    $insert = preg_replace('|{{([a-z0-9_]+)}}|e', 'isset($args["$1"]) ? $args["$1"] : "$0"', $insert);

    $script .= $insert;
  }
}

