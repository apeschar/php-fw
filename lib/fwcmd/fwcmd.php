<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', true);

if(!defined('FW_DIR')) define('FW_DIR', dirname(__FILE__) . '/../..');

set_include_path(FW_DIR . '/lib/classes' . PATH_SEPARATOR . get_include_path());

require_once 'FWAutoloader.class.php';
FWAutoloader::register();

require_once 'fwcmd_plugin.php';
require_once 'fwcmd_general.php';
require_once 'fwcmd_propel.php';

class fwcmd
{
  /**
   * @var array
   */
  protected $_arguments = array();

  /**
   * @var string
   */
  protected $_command = "fw";

  /**
   * Interpret arguments and take appropriate action
   *
   * @param string $command
   * @param array $arguments
   * @return void
   */
  public function execute($command, array $arguments)
  {
    // validate arguments
    assert('is_string($command)');
    $this->_command = $command;
    $this->_arguments = $arguments;

    // no arguments or `help'?
    if(sizeof($this->_arguments) == 0
       || $this->_arguments[0] == 'help'
       || trim($this->_arguments[0]) == '')
    {
      $this->printUsage();
      exit;
    }

    // get plugin name and command
    $pieces = explode(':', array_shift($this->_arguments));
    if(sizeof($pieces) > 2)
    {
      $this->printUsage();
      exit;
    }
    if(sizeof($pieces) == 1)
      $pieces = array('general', $pieces[0]);
    list($plugin_name, $plugin_command) = $pieces;
    
    // create instance of plugin
    $plugin_class = 'fwcmd_' . $plugin_name;
    $plugin_method = 'execute' . str_replace('-', '_', $plugin_command);

    if(!class_exists($plugin_class))
    {
      echo "No such plugin.\n\n";
      $this->printUsage();
      exit;
    }
    $plugin = new $plugin_class;
    if(!method_exists($plugin, $plugin_method))
    {
      echo "No such command.\n\n";
      $this->printUsage();
      exit;
    }

    // execute appropriate method
    $method_args = $this->_arguments;
    $reflect = new ReflectionMethod($plugin, $plugin_method);

    if(sizeof($method_args) < $reflect->getNumberOfRequiredParameters()
        || sizeof($method_args) > $reflect->getNumberOfParameters())
    {
      if(sizeof($method_args) > $reflect->getNumberOfParameters())
      {
        $too_much = sizeof($method_args) - $reflect->getNumberOfParameters();
        echo "$too_much parameter(s) too much. Try {$this->_command} help.\n";
      }
      elseif(sizeof($method_args) < $reflect->getNumberOfRequiredParameters())
      {
        $too_few = $reflect->getNumberOfRequiredParameters() - sizeof($method_args);
        echo "$too_few parameter(s) too few. Try {$this->_command} help.\n";
        
        // echo which parameters are needed
        $parameters = array_slice($reflect->getParameters(), sizeof($method_args));
        foreach($parameters as &$param)
        {
          $out = $param->isOptional() ? "[" : "";
          $out .= $param->getName();
          $out .= $param->isDefaultValueAvailable() ? " = " . var_export($param->getDefaultValue(), true) : "";
          $out .= $param->isOptional() ? "]" : "";
          $param = $out;
        }
        echo "Please specify: ", implode(', ', $parameters), "\n";
      }
    }
    else
    {
      call_user_func_array(array($plugin, $plugin_method), $method_args);
    }
    
    exit;
  }

  /**
   * Print usage message
   *
   */
  public function printUsage()
  {
    require dirname(__FILE__) . '/usage.php';
  }
}

