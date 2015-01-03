<?php namespace util\invoke\unittest;

use util\invoke\RateLimiting;
use util\invoke\Per;
use util\invoke\Rate;

class RateLimitingTest extends AbstractRateLimitingTest {

  #[@test, @values([
  #  [5000],
  #  [new Rate(5000)],
  #  [new Rate(5000, Per::$SECOND)]
  #])]
  public function can_create($rate) {
    new RateLimiting($rate);
  }

  #[@test, @values([0, -1]), @expect('lang.IllegalArgumentException')]
  public function rate_cannot_be_zero_or_negative($rate) {
    new RateLimiting($rate);
  }

  #[@test]
  public function rate() {
    $this->assertEquals(new Rate(2, Per::$SECOND), (new RateLimiting(2))->rate());
  }

  #[@test]
  public function throttle() {
    $fixture= new RateLimiting(1000);
    $fixture->throttle(100);
    $this->assertEquals(new Rate(900, Per::$SECOND), $fixture->rate());
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannot_throttle_to_zero() {
    (new RateLimiting(1000))->throttle(1000);
  }

  #[@test]
  public function increase() {
    $fixture= new RateLimiting(1000);
    $fixture->increase(100);
    $this->assertEquals(new Rate(1100, Per::$SECOND), $fixture->rate());
  }
}