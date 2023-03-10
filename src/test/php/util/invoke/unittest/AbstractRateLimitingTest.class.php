<?php namespace util\invoke\unittest;

use test\{Assert, Before};
use util\invoke\Clock;

abstract class AbstractRateLimitingTest {
  const CLOCK_START= 250944900.1;
  protected $clock;

  #[Before]
  public final function clock() {
    $this->clock= new class() implements Clock {
      private $time= 0.0;
      public function resetTo($time) { $this->time= $time; return $this; }
      public function forward($seconds) { $this->time+= $seconds; }
      public function wait($seconds) { $this->time+= $seconds; }
      public function time() { return $this->time; }
    };
  }

  /**
   * Assertion helper
   *
   * @param  double $expected
   * @param  double $actual
   * @param  int $round Significant digits
   * @throws test.AssertionFailed
   */
  protected function assertDouble($expected, $actual, $digits= 1) {
    Assert::equals($expected, round($actual, $digits));
  }
}