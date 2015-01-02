<?php namespace util\invoke;

/**
 * System clock
 *
 * @see  php://microtime
 * @see  php://usleep
 */
class SystemClock extends \lang\Object implements Clock {

  /** @return double */
  public function time() { return microtime(true); }

  /** @param double $seconds */
  public function wait($seconds) { usleep($seconds * 1000000); }
}