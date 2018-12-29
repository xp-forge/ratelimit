<?php namespace util\invoke;

use lang\Enum;

class Per extends Enum {
  public static $SECOND= 1;
  public static $MINUTE= 60;
  public static $HOUR= 3600;
  public static $DAY= 86400;

  /** @return int */
  public function seconds() { return $this->ordinal(); }
}