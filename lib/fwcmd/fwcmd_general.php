<?php

class fwcmd_general extends fwcmd_plugin
{
  /**
   * Create project
   *
   */
  public function executeProject($directory)
  {
    // file exists but is not a directory?
    if(file_exists($directory) && !is_dir($directory))
    {
      echo "The file `$directory' already exists, but is not a directory.\n";
      echo "I will not proceed.\n";
      exit;
    }

    // does the directory exist?
    if(!is_dir($directory))
    {
      if(!$this->_yesno("Directory does not exist. Create?")) die("I will not proceed.\n");
      mkdir($directory) or die("Could not create directory.\n");
      echo "Directory created.\n\n";
    }

    // is it empty?
    if(sizeof(scandir($directory)) > 2
       && !$this->_yesno("Directory is not empty. Proceed anyway?"))
      die("Not proceeding.\n");

    // copy skeleton
    $skeleton_dir = dirname(__FILE__) . '/../../data/skeleton';
    $this->_copyDir($skeleton_dir, $directory, true);
    echo "\n";
    chdir($directory) or die("Could not change working directory to `$directory'.\n");

    // configure application
    $replace = array(
      '//var/www/lib/framework/'                => realpath(dirname(__FILE__) . '/../..'),
    );
    $this->_replaceAssocFile($replace, 'conf/application.php');

    // chmod
    foreach(scandir('cache') as $file) chmod("cache/$file", 0777);

    // configure propel
    if($this->_yesno("Would you like to configure Propel?"))
    {
      $this->_configPropel();
      echo "\n";
    }

    // done
    $this->_format( "Your application has been configured and can be used. The index page will show a simple welcome message. Have a lot of fun!" );
  }

  /**
   * Configure propel
   *
   */
  protected function _configPropel()
  {
    $this->_checkProjectDir();
    if(!is_dir('model')) die("Directory `model' does not exist.\n");

    echo "Configuring Propel...\n\n";
    
    $adapter = 'mysql';
    $dsn = $username = $password = null;

    do
    {
      // ask for adapter
      $options = array('sqlite', 'mysql', 'myssql', 'oracle', 'pgsql');
      echo "You need to specify a Propel adapter.\n",
           "Available adapters are: ", implode(", ", $options), ".\n";
      $adapter = $this->_ask("Which adapter would you like to use?", $adapter, $options);
      echo "Using adapter: ", $adapter, ".\n\n";

      // ask for dsn
      $this->_format( "Propel uses the PDO extension which provides a consistent database interface. PDO uses a DSN which specifies the database source." );
      echo "See: http://php.net/manual/en/pdo.connections.php\n";
      $dsn = $this->_ask("What DSN would you like to use?", $dsn);
      echo "Using DSN: ", $dsn, ".\n\n";

      // do we need username/password?
      $this->_format("You might need to specify a username and password to connect to your database. Some databases require this information to be placed inside the DSN, others like to receive it at seperate parameters (for example, MySQL).");
      $login_required = $this->_yesno("Specify seperate username/password?");

      if($login_required)
      {
        $username = $this->_ask("Username:", $username);
        $password = $this->_ask("Password:", $password);
        echo "\n";
      }

      // summarize information
      echo "Connection parameters\n",
           "---------------------\n",
           "Adapter:      $adapter\n",
           "DSN:          $dsn\n";

      if($login_required)
      {
        echo "Username:     $username\n";
        echo "Password:     $password\n";
      }
      else
      {
        echo "No login information.\n";
      }

      echo "\n";

      // retry?
      $retry = !$this->_yesno("Is this information correct?");
      echo "\n";
      if($retry) continue;

      // verify connection
      if($this->_yesno("Shall I attempt a test connection?"))
      {
        // connect
        echo "Connecting... ";

        try
        {
          if($login_required)
            $dbh = new PDO($dsn, $username, $password);
          else
            $dbh = new PDO($dsn);

          echo "success!\n";
        }
        catch(PDOException $e)
        {
          echo "failed.\n";
          echo "PDO said: ", $e->getMessage(), "\n\n";

          if($this->_yesno("Re-enter connection info?"))
            continue;
        }
      }
      echo "\n";

      break;
    }
    while(1);

    // store connection info
    $replace = array(
      "propel.database = mysql\n"       => "propel.database = $adapter\n",
      "propel.database.url = mysql:host=localhost;dbname=app\n"
                                        => "propel.database.url = $dsn\n",
      "propel.database.user = root\n"   => $login_required ? "propel.database.user = $username\n" : "",
      "propel.database.password = root\n"
                                        => $login_required ? "propel.database.password = $password\n" : "",
    );
    $this->_replaceAssocFile($replace, 'model/build.properties');

    $replace = array(
      "<adapter>mysql</adapter>\n"      => "<adapter>$adapter</adapter>\n",
      "<dsn>mysql:host=localhost;dbname=app</dsn>\n"
                                        => "<dsn>$dsn</dsn>\n",
      "<user>root</user>\n"             => $login_required ? "<user>$username</user>\n" : "",
      "<password>root</password>\n"     => $login_required ? "<password>$password</password>\n" : "",
    );
    $this->_replaceAssocFile($replace, 'model/runtime-conf.xml');

    // enable propel
    $replace = array(
      'define(\'APP_ENABLE_PROPEL\', false);' => 'define(\'APP_ENABLE_PROPEL\', true);',
    );
    $this->_replaceAssocFile($replace, 'conf/application.php');

    // done
    echo "Propel has been configured. Have fun!\n";
  }
}

