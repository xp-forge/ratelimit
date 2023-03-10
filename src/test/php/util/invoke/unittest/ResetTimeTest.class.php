<?php namespace util\invoke\unittest;

use test\{Assert, Test, Values};
use util\invoke\{Rate, RateLimiting};

/**
 * Base class for `RateLimiting::resetTime()` method.
 *
 * @see  util.invoke.unittest.ResetTimePerSecondTest
 * @see  util.invoke.unittest.ResetTimePerHourTest
 */
abstract class ResetTimeTest extends AbstractRateLimitingTest {
  const RATE= 1000;

  /**
   * Returns the unit to use. Implemented in subclasses.
   *
   * @return util.invoke.Per
   */
  protected abstract function unit();

  /** @return var[][] */
  protected function permits() { return [[1], [2], [self::RATE - 1], [self::RATE]]; }

  /**
   * Creates a new fixture
   *
   * @param  int $time
   * @return util.invoke.RateLimiting
   */
  protected function fixture($time= self::CLOCK_START) {
    return new RateLimiting(new Rate(self::RATE, $this->unit()), $this->clock->resetTo($time));
  }

  #[Test]
  public function initially_null() {
    $fixture= $this->fixture();

    Assert::null($fixture->resetTime());
  }

  #[Test, Values(from: 'permits')]
  public function after_acquiring($permits) {
    $fixture= $this->fixture();
    $fixture->acquire($permits);

    $this->assertDouble(self::CLOCK_START + $this->unit()->seconds(), $fixture->resetTime());
  }

  #[Test, Values([0.0, 1.0, 6100.8, 86400.0])]
  public function after_sleeping_and_then_acquiring($sleep) {
    $fixture= $this->fixture();
    $this->clock->forward($sleep);
    $fixture->acquire();

    $this->assertDouble(self::CLOCK_START + $sleep + $this->unit()->seconds(), $fixture->resetTime());
  }

  #[Test, Values([0.0, 1.0, 6100.8, 86400.0])]
  public function after_acquiring_and_then_sleeping($sleep) {
    $fixture= $this->fixture();
    $fixture->acquire();
    $this->clock->forward($sleep);

    $this->assertDouble(self::CLOCK_START + $this->unit()->seconds(), $fixture->resetTime());
  }
}