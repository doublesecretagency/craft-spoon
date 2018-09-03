# Spoon Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased


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
