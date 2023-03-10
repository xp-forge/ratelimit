<?php namespace util\invoke\unittest;

use test\Assert;
use util\invoke\Per;

class ResetTimePerSecondTest extends ResetTimeTest {

  /** @return util.invoke.Per */
  protected function unit() { return Per::$SECOND; }
}