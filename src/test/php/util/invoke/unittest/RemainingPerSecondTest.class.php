<?php namespace util\invoke\unittest;

use util\invoke\Per;

class RemainingPerSecondTest extends RemainingTest {

  /** @return util.invoke.Per */
  protected function unit() { return Per::$SECOND; }
}