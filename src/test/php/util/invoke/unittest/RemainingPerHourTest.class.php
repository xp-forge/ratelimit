<?php namespace util\invoke\unittest;

use util\invoke\Per;

class RemainingPerHourTest extends RemainingTest {

  /** @return util.invoke.Per */
  protected function unit() { return Per::$HOUR; }
}