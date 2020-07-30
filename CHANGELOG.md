# Spoon Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).


## Unreleased

### Fixed
- Fixed an issue with newer versions of Super Table ([#98](https://github.com/angell-co/Spoon/issues/98))


## 3.5.2 - 2020-02-07

### Changed
- Spoon-specific project config data is now wiped on uninstall ([#85](https://github.com/angell-co/Spoon/issues/85))

### Fixed
- Fixed an issue with updating to 3.5.x from 3.3.x ([#89](https://github.com/angell-co/Spoon/issues/89))
- Fixed an issue with removing field layouts from current block type configurations ([#84](https://github.com/angell-co/Spoon/issues/84))


## 3.5.1 - 2020-02-05

### Fixed
- Fixed missing sort fields on new installs ([#86](https://github.com/angell-co/Spoon/issues/86))


## 3.5.0 - 2020-02-05

> {note} Before updating to 3.5.0 or greater it is a good idea to update to 3.4.3 first.

### Fixed
- Fixed things not working with Craft 3.4 ([#79](https://github.com/angell-co/Spoon/issues/79))
- Fixed an issue with block type configurations not keeping their order when deployed to another environment via project config ([#80](https://github.com/angell-co/Spoon/issues/80))
- Fixed an issue where Craft’s info table was being updated on every CP request ([#82](https://github.com/angell-co/Spoon/issues/82))


## 3.4.3 - 2019-12-16

### Fixed
- Removed a few more return type declarations that rely on PHP >= 7.1.0 ([#78](https://github.com/angell-co/Spoon/issues/78))


## 3.4.2 - 2019-12-04

### Fixed
- Refactored the codebase to not rely on PHP >= 7.1.0 and removed the composer requirement ([#74](https://github.com/angell-co/Spoon/issues/74))


## 3.4.1 - 2019-12-04

### Changed
- Bumped the PHP requirement to >= 7.1 ([#72](https://github.com/angell-co/Spoon/issues/72))

### Fixed
- Fixed a couple of bad references to the `uid` field on a Solspace Calendar model ([#73](https://github.com/angell-co/Spoon/issues/73))
- Fixed the collapsed / expanded reflow issues ([#58](https://github.com/angell-co/Spoon/issues/58) & [#67](https://github.com/angell-co/Spoon/issues/67)) - hat tip to [@jsunsawyer](https://github.com/jsunsawyer) for pinpointing the specific circumstances that cause this to pop up


## 3.4.0 - 2019-11-22

### Added
- Added project config support - at long last! ([#24](https://github.com/angell-co/Spoon/issues/24))

### Changed
- Moved the settings page to the standard plugin settings area ([#60](https://github.com/angell-co/Spoon/issues/60))

### Fixed
- Fixed a bug where all Matrix fields were getting hidden when viewing an entry revision ([#66](https://github.com/angell-co/Spoon/issues/66))


## 3.3.7 - 2019-04-25

### Fixed
- Fixed an error that occurred when trying to remove existing field layouts ([#57](https://github.com/angell-co/Spoon/issues/57))


## 3.3.6 - 2019-04-05

### Changed
- Qualified the Loader count() calls for opcode speed improvements

### Fixed
- Fixed an issue with the configurations not being loaded on existing entries for non-default sites ([#54](https://github.com/angell-co/Spoon/issues/54))
- Fixed an issue where the global block types settings page would still show deleted global configs if there was a global set with a contextual config for that field 


## 3.3.5 - 2019-03-06

### Fixed
- Fixed an issue where spooned blocks with no field layout wouldn’t render if there was a validation error ([#53](https://github.com/angell-co/Spoon/issues/53))


## 3.3.4 - 2019-03-01

### Fixed
- Fixed an issue where sometimes the fields cache needs refreshing ([#46](https://github.com/angell-co/Spoon/pull/46))


## 3.3.3 - 2019-02-22

### Fixed
- Fixed a couple more cases where the Matrix blocktype might not exist for some reason ([#52](https://github.com/angell-co/Spoon/issues/52)) 


## 3.3.2 - 2019-02-21

### Changed
- Upgraded gulp


## 3.3.1 - 2019-02-21

### Added
- Added support for [Solspace Calendar](https://solspace.com/craft/calendar/) specific contexts ([#36](https://github.com/angell-co/Spoon/issues/36))
- Added partial support for [Craft Commerce](https://craftcms.com/commerce) product type contexts ([#49](https://github.com/angell-co/Spoon/issues/49))

### Changed
- Moved the global block type context out of the plugin settings section ([#51](https://github.com/angell-co/Spoon/issues/51))

### Fixed
- Reverted PR [#46](https://github.com/angell-co/Spoon/pull/46) due to large DB impact resulting in slow page loads
- Fixed an issue where Spoon was not loading on previous versions of an Entry ([#48](https://github.com/angell-co/Spoon/issues/48))


## 3.3.0 - 2019-02-14

### Changed
- Changed the way the JavaScript attaches to Matrix to use an event-based method rather than polling the page ([#20](https://github.com/angell-co/Spoon/issues/20))

### Fixed
- Fixed a PHP notice that would sometimes occur due to fields not being refreshed ([#46](https://github.com/angell-co/Spoon/pull/46))


## 3.2.7 - 2019-01-28

### Fixed
- Fixed an issue with Super Table when upgrading to Craft 3.1 ([#44](https://github.com/angell-co/Spoon/issues/44))


## 3.2.6 - 2019-01-21

### Fixed
- Fixed another issue when upgrading to Craft 3.1 ([#43](https://github.com/angell-co/Spoon/issues/43))


## 3.2.5 - 2019-01-16

### Fixed
- Fixed a bunch of issues when upgrading to Craft 3.1 ([#43](https://github.com/angell-co/Spoon/issues/43) & [#40](https://github.com/angell-co/Spoon/issues/40))
- Fixed a layout issue where the tabs would overlap with the disabled block icon ([#42](https://github.com/angell-co/Spoon/issues/42)).
- Stopped tracking node_modules ([#35](https://github.com/angell-co/Spoon/issues/35)).


## 3.2.4 - 2018-10-12

### Changed
- Highlighted the tab if a field inside it has an error ([#27](https://github.com/angell-co/Spoon/issues/27)).
- Improved the asset bundle structure and minified all of the resources ([#21](https://github.com/angell-co/Spoon/issues/21)).


## 3.2.3 - 2018-09-21

### Fixed
- Fixed typing issues introduced in 3.2.2


## 3.2.2 - 2018-09-21

### Fixed
- Fixed a bunch of bugs and spacing issues picked up by Scrutinizer
- Corrected the core craftcms/cms requirement in composer.json


## 3.2.1 - 2018-09-13

### Added
- Added support for static translations ([#22](https://github.com/angell-co/Spoon/issues/22)).

### Fixed
- Fixed an issue where the Settings model wasn’t referencing the correct array validator class and was throwing an error when using the Schematic plugin ([#26](https://github.com/angell-co/Spoon/pull/26) and [#14](https://github.com/angell-co/Spoon/pull/14)).


## 3.2.0 - 2018-09-04

### Added
- Added support for Super Table allowing you to use Spoon on Matrix fields that are inside Super Table fields, both `Super Table > Matrix` and `Matrix > Super Table > Matrix`. Initially this is only for the global context which means you can only change these nested Matrix fields in the Spoon settings page and those changes apply everywhere.  


## 3.1.0 - 2018-08-29

### Added
- Now the global settings page shows you the parent field if the Matrix field is contained in a Super Table block

### Fixed
- Fixed an issue with the settings model rules not being declared properly ([#14](https://github.com/angell-co/Spoon/pull/14))
- Fixed an issue where sometimes the loader js wasn’t getting run ([#13](https://github.com/angell-co/Spoon/issues/13))
- Fixed an issue where no fields would show if the Matrix field being spooned was inside another Matrix field some how, tested inside Super Table ([#7](https://github.com/angell-co/Spoon/issues/7))


## 3.0.1 - 2018-07-18

### Changed
- Updated the README with the relevant [GitHub project](https://github.com/angell-co/Spoon/projects/2) and a note about support
- Changed the required version of Craft to 3.0.16 which fixes an issue with Safari when configuring block type layouts ([#8](https://github.com/angell-co/Spoon/issues/8)) 

### Fixed
- Fixed a typo in the README
- Fixed an issue where the initial page load in Safari was showing blank blocks ([#10](https://github.com/angell-co/Spoon/issues/10))


## 3.0.0 - 2018-07-10

### Added
- Added the `nestedSettings` config setting to allow a new, nested settings menu format ([#2](https://github.com/angell-co/Spoon/issues/2))
- Added the Craft 2 > 3 migration 

### Fixed
- Fixed an issue where switching the entry type didn’t update the UI - requires Craft v3.0.15 or greater ([#4](https://github.com/angell-co/Spoon/issues/4))
- Fixed a missing translation group key ([#5](https://github.com/angell-co/Spoon/issues/5))


## 3.0.0-beta.4 - 2018-07-03

### Added
- Added more general information around the plugin and a banner

### Removed
- Removed support for third-party plugins to add their own contexts

### Fixed
- Fixed an issue with loading the configurator on Users
- Fixed loading issues with Drafts and Sites
- Fixed a PHP error that occurred if you tried to edit an entry that doesn’t exist


## 3.0.0-beta.3 - 2018-07-03

### Added
- Added roadmap and pricing notes.

### Fixed
- Fixed an error if the plugin wasn’t installed ([thanks nilsenpaul](https://github.com/angell-co/Spoon/commit/2b364750f081484377c89b9af38d34fa7055412d))
- Fixed a filename casing issue ([thanks nilsenpaul](https://github.com/angell-co/Spoon/commit/03d377cf99ad2e6f7c7250d4c7af401d502a2675))


## 3.0.0-beta.2 - 2018-07-02

### Fixed
- Fixed a caching error in the BlockTypes Service


## 3.0.0-beta.1 - 2018-07-02

### Added 
- Initial port from [Pimp My Matrix](https://github.com/angell-co/Pimp-My-Matrix/tree/master/pimpmymatrix)
