<?php namespace util\invoke\unittest;

use unittest\{Test, Values};
use util\invoke\{Rate, RateLimiting};

/**
 * Base class for `RateLimiting::resetTime()` method.
 *
 * @see  xp://util.invoke.unittest.ResetTimePerSecondTest
 * @see  xp://util.invoke.unittest.ResetTimePerHourTest
 */
abstract class ResetTimeTest extends AbstractRateLimitingTest {
  const RATE = 1000;
  private $fixture;

  /**
   * Returns the unit to use. Implemented in subclasses.
   *
   * @return util.invoke.Per
   */
  protected abstract function unit();

  /**
   * Sets up fixture
   *
   * @return void
   */
  public function setUp() {
    parent::setUp();
    $this->fixture= new RateLimiting(new Rate(self::RATE, $this->unit()), self::$clock);
  }

  /** @return var[][] */
  protected function permits() { return [[1], [2], [self::RATE - 1], [self::RATE]]; }

  #[Test]
  public function initially_null() {
    $this->assertNull($this->fixture->resetTime());
  }

  #[Test, Values('permits')]
  public function after_acquiring($permits) {
    $this->fixture->acquire($permits);
    $this->assertDouble(self::CLOCK_START + $this->unit()->seconds(), $this->fixture->resetTime());
  }

  #[Test, Values([0.0, 1.0, 6100.8, 86400.0])]
  public function after_sleeping_and_then_acquiring($sleep) {
    self::$clock->forward($sleep);
    $this->fixture->acquire();
    $this->assertDouble(self::CLOCK_START + $sleep + $this->unit()->seconds(), $this->fixture->resetTime());
  }

  #[Test, Values([0.0, 1.0, 6100.8, 86400.0])]
  public function after_acquiring_and_then_sleeping($sleep) {
    $this->fixture->acquire();
    self::$clock->forward($sleep);
    $this->assertDouble(self::CLOCK_START + $this->unit()->seconds(), $this->fixture->resetTime());
  }
}