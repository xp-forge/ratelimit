<?php namespace util\invoke\unittest;

use test\{Assert, Test, Values};
use util\invoke\{Per, Rate, RateLimiting};

class VaryingUnitsTest extends AbstractRateLimitingTest {

  /** @return var[][] */
  protected function units() { return [[Per::$SECOND], [Per::$MINUTE], [Per::$HOUR], [Per::$DAY]]; }

  #[Test, Values(from: 'units')]
  public function one_more_than_permitted_results_in_sleep_until_next($unit) {
    $sleep= (double)($unit->seconds() / 2);
    $fixture= new RateLimiting(new Rate(1, $unit), $this->clock->resetTo(self::CLOCK_START));
    $fixture->acquire();
    $this->clock->forward($sleep);

    $this->assertDouble($unit->seconds() - $sleep, $fixture->acquire());
    $this->assertDouble(self::CLOCK_START + $unit->seconds(), $this->clock->time());
  }

  #[Test, Values(from: 'units')]
  public function after_sleeping_until_next($unit) {
    $fixture= new RateLimiting(new Rate(1, $unit), $this->clock->resetTo(self::CLOCK_START));
    $fixture->acquire();
    $this->clock->forward($unit->seconds());

    $this->assertDouble(0.0, $fixture->acquire());
    $this->assertDouble(self::CLOCK_START + $unit->seconds(), $this->clock->time());
  }

  #[Test, Values(from: 'units')]
  public function try_acquiring_after_waiting_until_next($unit) {
    $fixture= new RateLimiting(new Rate(1, $unit), $this->clock->resetTo(self::CLOCK_START));
    $fixture->acquire();
    $this->clock->forward($unit->seconds());

    Assert::true($fixture->tryAcquiring(1));
  }

  #[Test, Values(from: 'units')]
  public function try_acquiring_with_timeout_of_less_than_unit($unit) {
    $fixture= new RateLimiting(new Rate(1, $unit), $this->clock->resetTo(self::CLOCK_START));
    $fixture->acquire();

    Assert::false($fixture->tryAcquiring(1, $unit->seconds() - 0.5));
  }

  #[Test, Values(from: 'units')]
  public function try_acquiring_with_timeout_of_exactly_unit($unit) {
    $fixture= new RateLimiting(new Rate(1, $unit), $this->clock->resetTo(self::CLOCK_START));
    $fixture->acquire();

    Assert::true($fixture->tryAcquiring(1, $unit->seconds()));
  }

  #[Test, Values(from: 'units')]
  public function try_acquiring_with_timeout_of_larger_than_unit($unit) {
    $fixture= new RateLimiting(new Rate(1, $unit), $this->clock->resetTo(self::CLOCK_START));
    $fixture->acquire();

    Assert::true($fixture->tryAcquiring(1, $unit->seconds() + 0.5));
  }
}