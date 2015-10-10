<?php namespace util\invoke\unittest;

use util\invoke\Clock;

abstract class AbstractRateLimitingTest extends \unittest\TestCase {
  const CLOCK_START = 250944900.1;

  protected static $clock;

  /**
   * Defines a clock for testing purposes
   *
   * @return void
   */
  #[@beforeClass]
  public static function clock() {
    self::$clock= newinstance(Clock::class, [], '{
      private $time= 0.0;
      public function resetTo($time) { $this->time= $time; }
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
    self::$clock->resetTo(self::CLOCK_START);
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
}