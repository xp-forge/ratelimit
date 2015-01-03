<?php namespace util\invoke\unittest;

use util\invoke\RateLimiting;

class ClockOffsetTest extends AbstractRateLimitingTest {
  private $fixture;

  /**
   * Sets up fixture
   *
   * @return void
   */
  public function setUp() {
    parent::setUp();
    $this->fixture= new RateLimiting(1, self::$clock);
  }

  /** @return var[][] */
  protected function offsets() { return [[0.0], [0.1], [0.5], [0.9], [1.0]]; }

  #[@test, @values('offsets')]
  public function acquire($offset) {
    self::$clock->forward($offset);
    $this->fixture->acquire();
    $this->fixture->acquire();
    $this->assertDouble(self::CLOCK_START + 1.0 + $offset, self::$clock->time());
  }

  #[@test, @values('offsets')]
  public function tryAcquiring_with_one_second_timeout($offset) {
    self::$clock->forward($offset);
    $this->fixture->acquire();
    $this->fixture->tryAcquiring(1, 1.0);
    $this->assertDouble(self::CLOCK_START + 1.0 + $offset, self::$clock->time());
  }

  #[@test, @values('offsets')]
  public function tryAcquiring_with_timeout_larger_than_one_second($offset) {
    self::$clock->forward($offset);
    $this->fixture->acquire();
    $this->fixture->tryAcquiring(1, 10.0);
    $this->assertDouble(self::CLOCK_START + 1.0 + $offset, self::$clock->time());
  }

  #[@test, @values('offsets')]
  public function tryAcquiring_with_timeout_less_than_one_second($offset) {
    self::$clock->forward($offset);
    $this->fixture->acquire();
    $this->fixture->tryAcquiring(1, 0.1);
    $this->assertDouble(self::CLOCK_START + $offset, self::$clock->time());
  }
}