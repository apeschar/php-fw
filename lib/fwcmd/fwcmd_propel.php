<?php

class fwcmd_propel extends fwcmd_plugin
{
  /**
   * Build classes
   *
   */
  public function executeBuild()
  {
    $this->_checkProjectDir();
    
    $dir_model = 'model';
    $dir_tmp = 'model/tmp';

    // tmp directory exists?
    if(file_exists($dir_tmp))
      die("`$dir_tmp' still exists. Please remove it to continue.\n");
    
    // create tmp directory and copy needed files
    mkdir($dir_tmp);
    foreach(array('build.properties', 'runtime-conf.xml', 'schema.xml') as $f)
      copy("$dir_model/$f", "$dir_tmp/$f") or die("Couldn't copy `$f'.\n");

    // add some lines to build.properties
    $fh = fopen("$dir_tmp/build.properties", 'a');
    fwrite($fh, "\n\n# AUTOMATICALLY ADDED BY FRAMEWORK\n");
    fwrite($fh, "propel.output.dir = \${propel.project.dir}/../build\n");
    fwrite($fh, "propel.builder.object.class = " . $this->_propelClassPath(FW_DIR . '/lib/propel/FWPropelObjectBuilder.php') . "\n");
    fwrite($fh, "propel.builder.peer.class = " . $this->_propelClassPath(FW_DIR . '/lib/propel/FWPropelPeerBuilder.php') . "\n");
    fclose($fh);

    // execute propel-gen
    $out = $this->_propel_gen(array($dir_tmp));

    // clean up
    if(strpos($out, "BUILD FINISHED") === false) die("It seems the build failed.\n");
    $this->_rmdir($dir_tmp);
  }

  /**
   * Insert SQL
   *
   */
  public function executeInsert_SQL()
  {
    $this->_checkProjectDir();
    $out = $this->_propel_gen(array('model', 'insert-sql'));
    if(strpos($out, "BUILD FINISHED") === false) die("It seems the build failed.\n");

    // create foreign counter triggers
    $counter_file = 'model/foreign_counters';
    if(is_file($counter_file))
    {
      $propel_conf = include 'model/build/conf/propel-conf.php';
      $adapter = $propel_conf['propel']['datasources']['propel']['adapter'];
      $lines = file($counter_file);
      $trigger = array();
      foreach($lines as $line)
      {
        $line = trim(preg_replace('|#.*$|', '', $line));
        if(!$line) continue;

        if(strpos($line, '->') !== false)
        {
          $parts = array_map('trim', explode('->', $line));
          if(sizeof($parts) != 2) die("Invalid foreign counter line:\n$line\n");
          $owner_table = $parts[0];
          $owner_id_col = 'id';
          $owner_count_col = $parts[1] . '_count';
          $child_table = $parts[1];
          $child_ref_col = $parts[0] . '_id';
        }
        else
        {
          $parts = preg_split('|\s+|', $line);
          if(sizeof($parts) != 5) die("Invalid foreign counter line:\n$line\n");
          list($owner_table, $owner_id_col, $owner_count_col, $child_table, $child_ref_col) = $parts;
        }

        if($adapter == 'mysql')
        {
          $trigger[$child_table]['AFTER INSERT'][] = "UPDATE `{$owner_table}` SET `{$owner_count_col}` = (SELECT COUNT(*) FROM `{$child_table}` WHERE `{$child_table}`.`{$child_ref_col}` = `{$owner_table}`.`{$owner_id_col}`) WHERE `{$owner_id_col}` = NEW.`{$child_ref_col}`;\n";
          $trigger[$child_table]['AFTER DELETE'][] = "UPDATE `{$owner_table}` SET `{$owner_count_col}` = (SELECT COUNT(*) FROM `{$child_table}` WHERE `{$child_table}`.`{$child_ref_col}` = `{$owner_table}`.`{$owner_id_col}`) WHERE `{$owner_id_col}` = OLD.`{$child_ref_col}`;\n";
        }
      }

      if($trigger)
      {
        $this->_initPropel();
        $conn = Propel::getConnection();

        if($adapter == 'mysql')
        {
          foreach($trigger as $table => $events)
          {
            foreach($events as $event => $queries)
            {
              $sql = "CREATE TRIGGER foreign_key_count_" . md5(uniqid(microtime(true), true)) . " $event ON `$table` FOR EACH ROW BEGIN\n";
              foreach($queries as $query) $sql .= $query;
              $sql .= "END\n";
              $conn->query($sql);
            }
          }
        }
      }
    }

    // execute SQL in extra-sql dir
    $extra_dir = 'model/extra-sql';
    if(is_dir($extra_dir))
    {
      $files = scandir($extra_dir);
      sort($files);
      $this->_initPropel();
      $conn = Propel::getConnection();
      foreach($files as $file)
      {
        if(strpos($file, '.') === 0) continue;
        if(!preg_match('|\.sql$|', $file)) continue;
        $full = $extra_dir . '/' . $file;
        $lines = file($full);
        foreach($lines as $sql)
        {
          $sql = trim($sql);
          if($sql)
          {
            $conn->query($sql);
          }
        }
      }
    }
  }

  /**
   * Insert fixtures
   *
   */
  public function executeFixtures()
  {
    $this->_checkProjectDir();
    $this->_initPropel();

    $dir = 'model/fixtures';
    $files = scandir($dir);
    $fixtures = array();
    foreach($files as $file)
    {
      if($file[0] == '.') continue;
      if(!preg_match('|\.yml$|', $file)) continue;
      $full = $dir . '/' . $file;
      if(!is_file($full)) continue;
      if(!is_readable($full)) continue;
      $fixtures[] = $full;
    }

    $base_r = new ReflectionClass('BaseObject');
    foreach($fixtures as $fixture_file)
    {
      $fixture = FWYAML::load($fixture_file);
      $objects = array();

      foreach($fixture as $class => $rows)
      {
        // validate class name
        if(!class_exists($class)) die("Invalid class: $class\n");
        $class_r = new ReflectionClass($class);
        if(!$class_r->isSubclassOf($base_r)) die("$class is not a subclass of BaseObject.\n");

        // create objects
        foreach($rows as $row_name => $row)
        {
          if(isset($objects[$row_name])) die("Duplicate row name: $row_name\n");
          $inst = new $class;
          $objects[$row_name] = $inst;

          foreach($row as $field_name => $field_value)
          {
            $set_method = 'set' . $field_name;
            if(!method_exists($inst, $set_method)) die("Invalid field: $class->$field_name\n");
            if(strpos($field_value, '$') === 0)
            {
              $ref = substr($field_value, 1);
              if(!isset($objects[$ref])) die("Invalid reference: $ref\n");
              $field_value = $objects[$ref];
            }
            call_user_func(array($inst, $set_method), $field_value);
          }
        }
      }

      // save objects
      foreach($objects as $object) $object->save();
    }
  }

  /**
   * Build classes and insert SQL
   * 
   */
  public function executeBuild_Insert()
  {
    $this->executeBuild();
    $this->executeInsert_SQL();
  }

  /**
   * Build classes, insert SQL and insert fixtures
   *
   */
  public function executeBuild_Insert_Data()
  {
    $this->executeBuild();
    $this->executeInsert_SQL();
    $this->executeFixtures();
  }

  /**
   * Execute propel-gen
   *
   * @param array $arguments
   */
  protected function _propel_gen(array $arguments)
  {
    // where is the propel generator?
    $propel_gen_home = FW_DIR . '/lib/vendor/propel/generator';
    $propel_gen = $propel_gen_home . '/bin/propel-gen';

    // set the PROPEL_GEN_HOME env variable
    putenv('PROPEL_GEN_HOME=' . $propel_gen_home);

    // create temporary file for output
    $output_file = tempnam(sys_get_temp_dir(), 'fw_propel');
    $tee = ' | tee ' . escapeshellarg($output_file);

    // execute propel-gen
    $arguments = implode(' ', array_map('escapeshellarg', $arguments));
    $command = escapeshellarg($propel_gen);
    passthru("$command $arguments $tee");

    // return output
    $out = file_get_contents($output_file);
    unlink($output_file);
    return $out;
  }

  /**
   * Generate Propel-style path to class
   *
   * @param string $file
   * @return string
   */
  protected function _propelClassPath($file)
  {
    $file = str_replace('/', '.', preg_replace('|\.php$|', '', $file));
    return $file;
  }

  /**
   * Initialize Propel
   *
   */
  protected function _initPropel()
  {
    static $done = false;
    if($done) return;
    $done = true;

    FWIncludePath::prepend(FW_DIR . '/lib/vendor/propel/runtime/classes');
    FWIncludePath::prepend(APP_DIR . '/model/build/classes/propel');
    FWIncludePath::prepend(APP_DIR . '/model/build/classes');
    require 'propel/Propel.php';
    Propel::init(APP_DIR . '/model/build/conf/propel-conf.php');
  }
}

