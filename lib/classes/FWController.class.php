<?php

/**
 * @package framework
 */

/**
 * Base class for controller
 *
 * @package framework
 */
abstract class FWController
{
  const SUCCESS = 'Success';
  const INPUT = 'Input';

  /**
   * FWRequest object
   *
   * @var FWRequest
   */
  private $_request;

  /**
   * Template values
   *
   * @var array
   */
  private $_templateValues = array();

  /**
   * Constructor
   *
   */
  public function __construct()
  {
    $this->init();
  }

  /**
   * Initialize controller
   *
   */
  protected function init()
  {
  }

  /**
   * Executed before action
   *
   * @param string $action
   */
  protected function beforeAction($action)
  {
  }

  /**
   * Set FWRequest object
   *
   * @param FWRequest $request
   * @return void
   */
  public function setRequest(FWRequest $request)
  {
    $this->_request = $request;
  }

  /**
   * Get FWRequest object
   *
   * @return FWRequest
   */
  public function getRequest()
  {
    assert('isset($this->_request)');
    return $this->_request;
  }

  /**
   * Get FWRouter object
   *
   * @return FWRouter
   */
  public function getRouter()
  {
    return $this->getRequest()->getRouter();
  }

  /**
   * Get parameter
   *
   * @param string $name
   * @param mixed $default
   * @return mixed
   */
  public function getParameter($name, $default = null)
  {
    return $this->getRequest()->getParameter($name, $default);
  }

  /**
   * Dispatch action
   *
   * @param string $action
   * @return void
   */
  public function dispatch($action)
  {
    assert('is_string($action)');

    // action defined?
    $method = 'execute' . $action;
    if(!method_exists($this, $method)) return false;
    if(!in_array($method, get_class_methods(get_class($this)))) return false;

    // call beforeFilter
    $this->beforeAction($action);

    // call action
    $return = call_user_func(array($this, $method));

    // show template
    if(is_string($return))
    {
      $template = $return;
      $template_file = $this->getControllerName() . '/' . $action . $template . '.tpl';
      $template_fullpath = str_replace("\\", '/', APP_DIR . '/templates/' . $template_file);

      if(!is_file($template_fullpath))
      {
        throw new Exception('Template not found: ' . $template_file);
      }
      else
      {
        $tpl = new FWTemplate();
        $data = $this->_templateValues;
        $return = new FWResponse($tpl->get($template_fullpath, $data));
      }
    }

    // handle FWResponse
    if($return instanceof FWResponse)
    {
      $return->flush();
    }

    return true;
  }

  /**
   * Get name of controller
   *
   * @return string
   */
  public function getControllerName()
  {
    return preg_replace('|Controller$|', '', get_class($this));
  }

  /**
   * Set template value
   *
   * @param string $key
   * @param mixed $value
   */
  public function __set($key, $value)
  {
    if(!preg_match('|^[a-zA-Z_][a-zA-Z_0-9]*$|', $key)) throw new Exception('Invalid key.');
    $this->_templateValues[$key] = $value;
  }

  /**
   * Get template value
   *
   * @param string $key
   */
  public function __get($key)
  {
    if(isset($this->_templateValues[$key])) return $this->_templateValues[$key];
  }
}

