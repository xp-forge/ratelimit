<?php namespace util\invoke\unittest;

use util\invoke\Per;

class ResetTimePerSecondTest extends ResetTimeTest {

  /** @return util.invoke.Per */
  protected function unit() { return Per::$SECOND; }
}