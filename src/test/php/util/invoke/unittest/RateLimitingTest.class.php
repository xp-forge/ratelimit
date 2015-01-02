<?php namespace util\invoke\unittest;

use util\invoke\RateLimiting;

class RateLimitingTest extends \unittest\TestCase {
  private static $clock;

  /**
   * Defines a clock for testing purposes
   *
   * @return void
   */
  #[@beforeClass]
  public static function clock() {
    self::$clock= newinstance('util.invoke.Clock', [], '{
      private $time= 0.0;
      public function reset() { $this->time= 0.0; }
      public function forward($seconds) { $this->time+= $seconds; }
      public function wait($seconds) { $this->time+= $seconds; }
      public function time() { return $this->time; }
    }');
  }

  /**
   * Resets clock
   *
   * @return void
   */
  public function setUp() {
    self::$clock->reset();
  }

  /**
   * Assertion helper
   *
   * @param  double $expected
   * @param  double $actual
   * @param  int $round Significant digits
   * @throws unittest.AssertionFailedError
   */
  protected function assertDouble($expected, $actual, $digits= 1) {
    $this->assertEquals($expected, round($actual, $digits));
  }

  #[@test]
  public function can_create() {
    new RateLimiting(2);
  }

  #[@test, @values([0, -1]), @expect('lang.IllegalArgumentException')]
  public function rate_cannot_be_zero_or_negative($qps) {
    new RateLimiting($qps);
  }

  #[@test]
  public function rate() {
    $this->assertEquals(2, (new RateLimiting(2))->rate());
  }

  #[@test]
  public function throttle() {
    $fixture= new RateLimiting(1000);
    $fixture->throttle(100);
    $this->assertEquals(900, $fixture->rate());
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannot_throttle_to_zero() {
    (new RateLimiting(1000))->throttle(1000);
  }

  #[@test]
  public function increase() {
    $fixture= new RateLimiting(1000);
    $fixture->increase(100);
    $this->assertEquals(1100, $fixture->rate());
  }

  #[@test, @values([1, 1000])]
  public function acquire_exact_number_of_permits($qps) {
    $fixture= new RateLimiting($qps, self::$clock);
    $this->assertDouble(0.0, $fixture->acquire($qps));
  }

  #[@test, @values([0.0, 0.1, 0.5, 0.9, 1.0])]
  public function acquiring_one_more_than_permitted_results_in_sleep($offset) {
    $fixture= new RateLimiting(1, self::$clock);
    self::$clock->forward($offset);
    $fixture->acquire(1);
    $this->assertDouble(1.0, $fixture->acquire());
    $this->assertDouble(1.0 + $offset, self::$clock->time());
  }

  #[@test]
  public function acquiring_one_more_than_permitted_results_in_sleep_until_next_second() {
    $fixture= new RateLimiting(1, self::$clock);
    $fixture->acquire(1);
    self::$clock->forward(0.8);
    $this->assertDouble(0.2, $fixture->acquire());
    $this->assertDouble(1.0, self::$clock->time());
  }

  #[@test]
  public function acquire_after_waiting_one_second() {
    $fixture= new RateLimiting(1, self::$clock);
    $fixture->acquire(1);
    self::$clock->forward(1.0);
    $this->assertDouble(0.0, $fixture->acquire(1));
  }

  #[@test, @values([1, 1000])]
  public function try_acquiring_exact_number_of_permits($qps) {
    $fixture= new RateLimiting($qps, self::$clock);
    $this->assertTrue($fixture->tryAcquiring($qps));
  }

  #[@test, @values([1, 1000])]
  public function fails_trying_to_acquire_one_more_than_permitted($qps) {
    $fixture= new RateLimiting($qps, self::$clock);
    $fixture->acquire($qps);
    $this->assertFalse($fixture->tryAcquiring(1));
  }

  #[@test]
  public function try_acquiring_after_waiting_one_second() {
    $fixture= new RateLimiting(1, self::$clock);
    $fixture->acquire(1);
    self::$clock->forward(1.0);
    $this->assertTrue($fixture->tryAcquiring(1));
  }

  #[@test]
  public function try_acquiring_with_timeout_of_less_than_one_second() {
    $fixture= new RateLimiting(1, self::$clock);
    $fixture->acquire(1);
    $this->assertFalse($fixture->tryAcquiring(1, 0.8));
  }

  #[@test, @values([0.0, 0.1, 0.5, 0.9, 1.0])]
  public function try_acquiring_with_timeout_of_exactly_one_second($offset) {
    $fixture= new RateLimiting(1, self::$clock);
    self::$clock->forward($offset);
    $fixture->acquire(1);
    $this->assertTrue($fixture->tryAcquiring(1, 1.0));
    $this->assertDouble(1.0 + $offset, self::$clock->time());
  }

  #[@test, @values([0.0, 0.1, 0.5, 0.9, 1.0])]
  public function try_acquiring_with_timeout_of_greater_than_one_second($offset) {
    $fixture= new RateLimiting(1, self::$clock);
    self::$clock->forward($offset);
    $fixture->acquire(1);
    $this->assertTrue($fixture->tryAcquiring(1, 10.0));
    $this->assertDouble(1.0 + $offset, self::$clock->time());
  }

  #[@test, @values([[1, 100.0], [11, 90.0]])]
  public function try_acquiring_more_than_limit($qps, $wait) {
    $fixture= new RateLimiting($qps, self::$clock);
    $this->assertTrue($fixture->tryAcquiring(100));
    $this->assertEquals($wait, $fixture->acquire(1));
  }
}