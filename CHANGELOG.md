# Changelog

All notable changes to `stagerightlabs/actions` will be documented in this file

## 0.00.41 - 2022-01-08

### Added

- I was hoping to be able to use the same `execute()` method to trigger actions both statically and from within an object context.  However, this turns out not to be practical.  Instead `execute()` will be used for static triggering, and a new `run()` method will be used to trigger instantiated action classes.

## 0.00.40 - 2022-01-07

### Changed

- Allow action classes to have optional constructors.

## 0.00.30 - 2020-12-17

### Added

- Added support for PHP 8

## 0.00.22 - 2020-10-20

### Changed

- Fixed bug that was preventing the ability to generate action classes in Laravel applications.

## 0.00.21 - 2020-10-12

### Changed

- Fixed typo that was causing incorrect extraneous input key warnings.

## 0.00.20 - 2020-10-10

## Added

- Laravel Integration

## Changed

- Streamline the usage of Action classes as DTOs by removing the `payload` property and its attendant methods.

## 0.00.10 - 2020-10-08

### Added

- Initial proof of concept and beta release.
