<?php namespace util\invoke\unittest;

use unittest\{Test, Values};
use util\invoke\{Per, Rate, RateLimiting};

class VaryingUnitsTest extends AbstractRateLimitingTest {

  /** @return var[][] */
  protected function units() { return [[Per::$SECOND], [Per::$MINUTE], [Per::$HOUR], [Per::$DAY]]; }

  #[Test, Values('units')]
  public function one_more_than_permitted_results_in_sleep_until_next($unit) {
    $sleep= (double)($unit->seconds() / 2);
    $fixture= new RateLimiting(new Rate(1, $unit), self::$clock);
    $fixture->acquire();
    self::$clock->forward($sleep);
    $this->assertDouble($unit->seconds() - $sleep, $fixture->acquire());
    $this->assertDouble(self::CLOCK_START + $unit->seconds(), self::$clock->time());
  }

  #[Test, Values('units')]
  public function after_sleeping_until_next($unit) {
    $fixture= new RateLimiting(new Rate(1, $unit), self::$clock);
    $fixture->acquire();
    self::$clock->forward($unit->seconds());
    $this->assertDouble(0.0, $fixture->acquire());
    $this->assertDouble(self::CLOCK_START + $unit->seconds(), self::$clock->time());
  }

  #[Test, Values('units')]
  public function try_acquiring_after_waiting_until_next($unit) {
    $fixture= new RateLimiting(new Rate(1, $unit), self::$clock);
    $fixture->acquire();
    self::$clock->forward($unit->seconds());
    $this->assertTrue($fixture->tryAcquiring(1));
  }

  #[Test, Values('units')]
  public function try_acquiring_with_timeout_of_less_than_unit($unit) {
    $fixture= new RateLimiting(new Rate(1, $unit), self::$clock);
    $fixture->acquire();
    $this->assertFalse($fixture->tryAcquiring(1, $unit->seconds() - 0.5));
  }

  #[Test, Values('units')]
  public function try_acquiring_with_timeout_of_exactly_unit($unit) {
    $fixture= new RateLimiting(new Rate(1, $unit), self::$clock);
    $fixture->acquire();
    $this->assertTrue($fixture->tryAcquiring(1, $unit->seconds()));
  }

  #[Test, Values('units')]
  public function try_acquiring_with_timeout_of_larger_than_unit($unit) {
    $fixture= new RateLimiting(new Rate(1, $unit), self::$clock);
    $fixture->acquire();
    $this->assertTrue($fixture->tryAcquiring(1, $unit->seconds() + 0.5));
  }
}