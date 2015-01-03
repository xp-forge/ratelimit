<?php namespace util\invoke\unittest;

use util\invoke\RateLimiting;
use util\invoke\Per;
use util\invoke\Rate;

class AcquiringTest extends AbstractRateLimitingTest {

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
    $this->assertDouble(self::CLOCK_START + 1.0 + $offset, self::$clock->time());
  }

  #[@test]
  public function acquiring_one_more_than_permitted_results_in_sleep_until_next_second() {
    $fixture= new RateLimiting(1, self::$clock);
    $fixture->acquire(1);
    self::$clock->forward(0.8);
    $this->assertDouble(0.2, $fixture->acquire());
    $this->assertDouble(self::CLOCK_START + 1.0, self::$clock->time());
  }

  #[@test]
  public function acquiring_one_more_than_permitted_results_in_sleep_until_next_minute() {
    $fixture= new RateLimiting(new Rate(1, Per::$MINUTE), self::$clock);
    $fixture->acquire(1);
    self::$clock->forward(40.0);
    $this->assertDouble(20.0, $fixture->acquire());
    $this->assertDouble(self::CLOCK_START + 60.0, self::$clock->time());
  }

  #[@test]
  public function acquire_after_waiting_one_second() {
    $fixture= new RateLimiting(1, self::$clock);
    $fixture->acquire(1);
    self::$clock->forward(1.0);
    $this->assertDouble(0.0, $fixture->acquire(1));
  }

  #[@test]
  public function acquire_after_waiting_one_hour() {
    $fixture= new RateLimiting(new Rate(1, Per::$HOUR), self::$clock);
    $fixture->acquire(1);
    self::$clock->forward(3600.0);
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
    $this->assertDouble(self::CLOCK_START + 1.0 + $offset, self::$clock->time());
  }

  #[@test, @values([0.0, 0.1, 0.5, 0.9, 1.0])]
  public function try_acquiring_with_timeout_of_greater_than_one_second($offset) {
    $fixture= new RateLimiting(1, self::$clock);
    self::$clock->forward($offset);
    $fixture->acquire(1);
    $this->assertTrue($fixture->tryAcquiring(1, 10.0));
    $this->assertDouble(self::CLOCK_START + 1.0 + $offset, self::$clock->time());
  }
}