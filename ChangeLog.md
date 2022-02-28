Rate limiting ChangeLog
========================================================================

## ?.?.? / ????-??-??

## 3.1.0 / 2022-02-28

* Added XP 11 support - @thekid

## 3.0.0 / 2020-04-10

* Implemented xp-framework/rfc#334: Drop PHP 5.6:
  . **Heads up:** Minimum required PHP version now is PHP 7.0.0
  . Rewrote code base, grouping use statements
  . Converted `newinstance` to anonymous classes
  (@thekid)

## 2.0.1 / 2020-04-05

* Made compatible with XP 10 - @thekid

## 2.0.0 / 2018-12-29

* **Heads up: Dropped PHP 5.5 support** - @thekid
* Made compatible with PHP 7.2+ - @thekid

## 1.0.0 / 2016-02-21

* Added version compatibility with XP 7 - @thekid

## 0.3.0 / 2016-01-24

* Fix code to use `nameof()` instead of the deprecated `getClassName()`
  method from lang.Generic. See xp-framework/core#120
  (@thekid)

## 0.2.0 / 2015-10-10

* **Heads up: Dropped PHP 5.4 support**. *Note: As the main source is not
  touched, unofficial PHP 5.4 support is still available though not tested
  with Travis-CI*.
  (@thekid)

## 0.1.0 / 2015-02-12

* First public release - @thekid
