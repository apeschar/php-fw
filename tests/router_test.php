<?php

require_once '_core.php';
require_once 'FWRouter.class.php';

class FWRouterTest extends UnitTestCase
{
  private function _getRouterWithRoutes()
  {
    $router = new FWRouter();
    $router->connect('/:test');
    $router->connect('/2/:test2', array('a' => 'aval', 'b' => 'bval'));
    $router->connect('/3/:test', array(), array('test' => '|^[a-z]+$|'));
    return $router;
  }

  private function _test($router)
  {
    // test simple routing
    $this->assertEqual($router->route('/value'), array('test' => 'value'));
    $this->assertEqual($router->route('/value=2F'), array('test' => 'value/'));
    $this->assertEqual($router->route('/value+'), array('test' => 'value '));

    // test defaults
    $this->assertEqual($router->route('/2/value'), array('a' => 'aval', 'b' => 'bval', 'test2' => 'value'));

    // test requirements
    $this->assertEqual($router->route('/3/value'), array('test' => 'value'));
    $this->assertFalse($router->route('/3/value1'));

    // test route assembling
    $this->assertEqual($router->assemble(array('test' => 'value')), 'value');
    $this->assertEqual($router->assemble(array('test' => 'value/')), 'value=2F');
    $this->assertEqual($router->assemble(array('test' => 'value ')), 'value+');
    $this->assertFalse($router->assemble(array('nonexistent' => 'hello')));
    $this->assertEqual($router->assemble(array('test2' => 'value', 'a' => 'aval', 'b' => 'bval')), '2/value');
    $this->assertFalse($router->assemble(array('test2' => 'value', 'a' => 'qval')));

    // add a default route
    $router->setDefaultRoute(':controller/:action');

    $this->assertEqual($router->route('/c/a'), array('controller' => 'c', 'action' => 'a'));
    $this->assertEqual($router->route('/c/a=2F'), array('controller' => 'c', 'action' => 'a/'));
    $this->assertEqual($router->route('/c/a+'), array('controller' => 'c', 'action' => 'a '));
    $this->assertEqual($router->route('/c/a/a'), array('controller' => 'c', 'action' => 'a'));
    $this->assertEqual($router->route('/c/a/a=b'), array('controller' => 'c', 'action' => 'a', 'a' => 'b'));

    $this->assertEqual($router->assemble(array('controller' => 'c', 'action' => 'a')), 'c/a');
    $this->assertEqual($router->assemble(array('controller' => 'c', 'action' => 'a', 'key' => 'value')), 'c/a/key=value');
    $this->assertEqual($router->assemble(array('controller' => 'c', 'action' => 'a', 'key' => 'hello/')), 'c/a/key=hello=2F');
    $this->assertEqual($router->assemble(array('controller' => 'c', 'action' => 'a', 'key' => 'hello ')), 'c/a/key=hello+');
  }

  public function testRouter()
  {
    $this->_test($this->_getRouterWithRoutes());
  }

  public function testSerialization()
  {
    $router = $this->_getRouterWithRoutes();
    $router = unserialize(serialize($router));
    $this->_test($router);
  }
}

