<?php namespace util\invoke\unittest;

use test\{Assert, Test, Values};
use util\invoke\RateLimiting;

class ClockOffsetTest extends AbstractRateLimitingTest {

  /**
   * Creates a new fixture
   *
   * @param  int $time
   * @return util.invoke.RateLimiting
   */
  private function fixture($time) { return new RateLimiting(1, $this->clock->resetTo($time)); }

  /** @return var[][] */
  private function offsets() { return [[0.0], [0.1], [0.5], [0.9], [1.0]]; }

  #[Test, Values(from: 'offsets')]
  public function acquire($offset) {
    $fixture= $this->fixture(self::CLOCK_START + $offset);

    $fixture->acquire();
    $fixture->acquire();
    $this->assertDouble(self::CLOCK_START + 1.0 + $offset, $this->clock->time());
  }

  #[Test, Values(from: 'offsets')]
  public function tryAcquiring_with_one_second_timeout($offset) {
    $fixture= $this->fixture(self::CLOCK_START + $offset);

    $fixture->acquire();
    $fixture->tryAcquiring(1, 1.0);

    $this->assertDouble(self::CLOCK_START + 1.0 + $offset, $this->clock->time());
  }

  #[Test, Values(from: 'offsets')]
  public function tryAcquiring_with_timeout_larger_than_one_second($offset) {
    $fixture= $this->fixture(self::CLOCK_START + $offset);

    $fixture->acquire();
    $fixture->tryAcquiring(1, 10.0);

    $this->assertDouble(self::CLOCK_START + 1.0 + $offset, $this->clock->time());
  }

  #[Test, Values(from: 'offsets')]
  public function tryAcquiring_with_timeout_less_than_one_second($offset) {
    $fixture= $this->fixture(self::CLOCK_START + $offset);

    $fixture->acquire();
    $fixture->tryAcquiring(1, 0.1);

    $this->assertDouble(self::CLOCK_START + $offset, $this->clock->time());
  }
}