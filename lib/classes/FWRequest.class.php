<?php

/**
 * @package framework
 */

/**
 * Exceptions
 *
 */
require_once dirname(__FILE__) . '/exceptions.php';

/**
 * Request handler
 *
 * @package framework
 */
class FWRequest
{
  /**
   * FWRouter instance
   *
   * @var FWRouter
   */
  private $_router;

  /**
   * Parameters
   *
   * @var array
   */
  private $_parameters = array();

  /**
   * Set FWRouter
   *
   * @param FWRouter|null $router
   * @return void
   */
  public function setRouter($router)
  {
    assert('$router instanceof FWRouter || is_null($router)');
    $this->_router = $router;
  }

  /**
   * Get FWRouter
   *
   * @return FWRouter|null
   */
  public function getRouter()
  {
    return $this->_router;
  }

  /**
   * Dispatch request
   *
   * @param string|array $url
   * @return void
   */
  public function dispatch($url)
  {
    assert('is_string($url) || is_array($url)');

    // check recursion
    static $dispatch_count = 0;
    if($dispatch_count >= 50) throw new FWException('FWRequest->dispatch: Too much recursion. (loop?)');
    ++$dispatch_count;

    // get router
    $router = $this->getRouter();
    if(!$router) throw new FWException('Please FWRouter->setRouter() first.');

    try
    {
      // route url
      if(is_string($url))
      {
        $route = $router->route($url);
        if(!$route) $this->forward404();
        if(!isset($route['controller']) || !isset($route['action'])) $this->forward404();
      }
      else
      {
        $route = $url;
      }

      // validate controller and action names
      $controller = FWU::underscoresToCamelcase($route['controller']);
      $action = FWU::underscoresToCamelcase($route['action']);

      $controller_re = '|^[A-Za-z][A-Za-z0-9]*$|';
      $action_re = '|^[A-Za-z0-9]+$|';
      if(!preg_match($controller_re, $controller) || !preg_match($action_re, $action)) $this->forward404();

      // controller exists?
      $controller_class = $controller . 'Controller';
      $controller_file = APP_DIR . '/controllers/' . $controller_class . '.class.php';
      if(!is_file($controller_file)) $this->forward404();
      require_once $controller_file;
      if(!class_exists($controller_class)) $this->forward404();

      // action exists?
      $action_method = 'execute' . $action;
      if(!method_exists($controller_class, $action_method)) $this->forward404();

      // set parameters
      $this->flushParameters();
      $this->addParameters($route);
      $this->addParameters($_GET);
      $this->addParameters($_POST);

      // call action
      $obj = new $controller_class();
      $obj->setRequest($this);
      $this->forward404Unless($obj->dispatch($action));
    }
    catch(FWForwardException $e)
    {
      $this->dispatch($e->parameters);
    }
  }

  /**
   * Forward request
   *
   * @param array $parameters
   * @return void
   */
  public function forward(array $parameters)
  {
    $e = new FWForwardException();
    $e->parameters = $parameters;
    throw $e;
  }

  /**
   * Show 404 page
   *
   * @return void
   */
  public function forward404()
  {
    $this->forward(array('controller' => 'error', 'action' => '404'));
  }

  /**
   * Show 404 page unless parameter is true
   *
   * @param mixed $test
   * @return void
   */
  public function forward404Unless($test)
  {
    if(!$test) $this->forward404();
  }

  /**
   * Redirect
   *
   * @param string|array $parameters
   */
  public function redirect($parameters)
  {
    if(is_array($parameters))
      $url = FWContext::getRouter()->assemble($parameters);
    else
      $url = $parameters;

    header('Location: ' . $url);
    exit;
  }

  /**
   * Remove all parameters
   *
   * @return void
   */
  public function flushParameters()
  {
    $this->_parameters = array();
  }

  /**
   * Add array of parameters
   *
   * @param array $parameters
   * @return void
   */
  public function addParameters($parameters)
  {
    assert('is_array($parameters)');
    $this->_parameters = array_merge($this->_parameters, $parameters);
  }

  /**
   * Get parameter
   *
   * @param string $name
   */
  public function getParameter($name, $default = null)
  {
    assert('is_string($name)');
    if(!isset($this->_parameters[$name])) return $default;
    return $this->_parameters[$name];
  }

  /**
   * Get request method
   *
   * @return string
   */
  public function getMethod()
  {
    $request_method = $_SERVER['REQUEST_METHOD'];
    return $request_method;
  }
}

