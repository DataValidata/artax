<?php

class MediatorTest extends PHPUnit_Framework_TestCase
{
  public function testBeginsEmpty()
  {
    $m = new MediatorTestImplementationClass;
    $this->assertEquals([], $m->all());
    return $m;
  }
  
  /**
   * @depends testBeginsEmpty
   * @covers artax\events\Mediator::setRebinder
   */
  public function testSetRebinderAssignsProperty($m)
  {
    $lambda = function() { return 42; };
    $m->setRebinder($lambda);
    $this->assertEquals($lambda, $m->rebinder);
  }
  
  /**
   * @covers artax\events\Mediator::push
   * @covers artax\events\Mediator::last
   * @covers artax\events\Mediator::rebind
   */
  public function testPushAddsEventListener()
  {
    $m = new artax\events\Mediator;
    $listeners = $m->push('test.event1', function() { return TRUE; });
    $this->assertEquals(1, $listeners);
    
    $listeners = $m->push('test.event1', function() { return 42; });
    $this->assertEquals(2, $listeners);
    $this->assertEquals(function() { return 42; }, $m->last('test.event1'));
    return $m;
  }
  
  /**
   * @covers artax\events\Mediator::unshift
   * @covers artax\events\Mediator::first
   */
  public function testUnshiftAddsEventListener()
  {
    $m = new artax\events\Mediator;
    $listeners = $m->push('test.event1', function() { return TRUE; });
    $this->assertEquals(1, $listeners);
    
    $listeners = $m->unshift('test.event1', function() { return 42; });
    $this->assertEquals(2, $listeners);
    $this->assertEquals(function() { return 42; }, $m->first('test.event1'));
    return $m;
  }
  
  /**
   * @covers artax\events\Mediator::first
   */
  public function testFirstReturnsNullIfNoListenersMatch()
  {
    $m = new artax\events\Mediator;
    $this->assertEquals(NULL, $m->first('test.event1'));
  }
  
  /**
   * @covers artax\events\Mediator::last
   */
  public function testLastReturnsNullIfNoListenersMatch()
  {
    $m = new artax\events\Mediator;
    $this->assertEquals(NULL, $m->last('test.event1'));
  }
  
  /**
   * @depends testPushAddsEventListener
   * @covers  artax\events\Mediator::count
   */
  public function testCountReturnsNumberOfListenersForSpecifiedEvent($m)
  {
    $this->assertEquals(2, $m->count('test.event1'));
  }
  
  /**
   * @depends testPushAddsEventListener
   * @covers  artax\events\Mediator::keys
   */
  public function testKeysReturnsArrayOfListenedForEvents($m)
  {
    $m->push('test.event2', function() { return 42; });
    $this->assertEquals(['test.event1', 'test.event2'], $m->keys());
    return $m;
  }
  
  /**
   * @depends testKeysReturnsArrayOfListenedForEvents
   * @covers  artax\events\Mediator::clear
   */
  public function testClearRemovesAllListenersAndListenedForEvents($m)
  {
    $m->clear('test.event2');
    $this->assertEquals(['test.event1'], $m->keys());
    
    $m->clear();
    $this->assertEquals([], $m->keys());
  }
  
  /**
   * @depends testKeysReturnsArrayOfListenedForEvents
   * @covers  artax\events\Mediator::pop
   */
  public function testPopRemovesLastListenerForSpecifiedEvent($m)
  {
    $count = $m->count('test.event1');
    $f = function() { return 'unnecessary'; };
    $m->push('test.event1', $f);
    $listener = $m->pop('test.event1');
    $this->assertEquals($f, $listener);
    $this->assertEquals($count, $m->count('test.event1'));
  }
  
  /**
   * @depends testKeysReturnsArrayOfListenedForEvents
   * @covers  artax\events\Mediator::pop
   */
  public function testPopReturnsNullIfNoEventsMatchSpecifiedEvent($m)
  {
    $listener = $m->pop('test.eventDoesntExist');
    $this->assertEquals(NULL, $listener);
  }
  
  /**
   * @depends testKeysReturnsArrayOfListenedForEvents
   * @covers  artax\events\Mediator::shift
   */
  public function testShiftRemovesFirstListenerForSpecifiedEvent($m)
  {
    $count = $m->count('test.event1');
    $f = function() { return 'unnecessary'; };
    $m->push('test.event1', $f);
    $listener = $m->shift('test.event1');
    $this->assertEquals($f, $listener);
    $this->assertEquals($count, $m->count('test.event1'));
  }
  
  /**
   * @depends testKeysReturnsArrayOfListenedForEvents
   * @covers  artax\events\Mediator::shift
   */
  public function testShiftReturnsNullIfNoEventsMatchSpecifiedEvent($m)
  {
    $listener = $m->shift('test.eventDoesntExist');
    $this->assertEquals(NULL, $listener);
  }
  
  /**
   * @covers  artax\events\Mediator::unshift
   */
  public function testUnshiftCreatesEventHolderIfNotExists()
  {
    $m = new artax\events\Mediator;
    $listeners = $m->push('test.event1', function() { return TRUE; });
    $this->assertEquals(1, $listeners);
    
    $listeners = $m->unshift('test.event2', function() { return 42; });
    $this->assertEquals(1, $listeners);
    $this->assertEquals(function() { return 42; }, $m->first('test.event2'));
  }
  
  /**
   * @covers  artax\events\Mediator::notify
   * @covers  artax\events\Mediator::all
   */
  public function testNotifyDistributesMessagesToListeners()
  {
    $m = new artax\events\Mediator;
    $this->assertEquals(0, $m->notify('no.listeners.event'));
    
    $listeners = $m->push('test.event1', function() { return TRUE; });
    $this->assertEquals(1, $m->notify('test.event1'));
    
    
    $listeners = $m->unshift('test.event2', function($x) {
      return isset($x) ? 42*$x : 42;
    });
    
    $m->push('test.event2', function() { return FALSE; });
    $m->push('test.event2', function() { return TRUE; });
    $this->assertEquals(2, $m->notify('test.event2'));
  }
  
  /**
   * @covers  artax\events\Mediator::all
   */
  public function testAllReturnsEventSpecificListIfSpecified()
  {
    $m = new artax\events\Mediator;
    $listener  = function() { return TRUE; };
    $listeners = $m->push('test.event1', $listener);
    
    $this->assertEquals([$listener], $m->all('test.event1'));
  }
}

class MediatorTestImplementationClass extends artax\events\Mediator
{
  use MagicTestGetTrait;
}












