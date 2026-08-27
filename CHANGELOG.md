# Changelog

All notable changes to this project will be documented in this file, per [the Keep a Changelog standard](http://keepachangelog.com/).  Moving forward, this project will (more strictly) adhere to [Semantic Versioning](http://semver.org/).

## [Unreleased] - TBD

## [7.6.2] - 2026-08-27
**Note that this version bumps the WordPress minimum supported version from 6.6 to 6.9.**

### Fixed
- Allow programatic changes to first allow-listed IP address label. (props [@thisismyurl](https://github.com/thisismyurl), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#419](https://github.com/10up/restricted-site-access/pull/419))

### Changed
- Bump tested up to header to indicate WordPress 6.9 support. (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter) via [#379](https://github.com/10up/restricted-site-access/pull/379))
- Bump WordPress "tested up to" version 7.0 (props [@phpbits](https://github.com/phpbits), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#404](https://github.com/10up/restricted-site-access/pull/404))
- Bump tested up to header to indicate WordPress 7.1 support (props [@phpbits](https://github.com/phpbits), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#428](https://github.com/10up/restricted-site-access/pull/428))
- Increase minimum supported WordPress version from 6.6 to 6.9 (props [@phpbits](https://github.com/phpbits), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#428](https://github.com/10up/restricted-site-access/pull/428))

### Developer
- Add Patchstack security-reporting FAQ. (props [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#380](https://github.com/10up/restricted-site-access/pull/380))
- Add unit tests to ensure plugin headers and other meta data is correct. (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter) via [#432](https://github.com/10up/restricted-site-access/pull/432))
- E2E Tests: update selector used for plugin activation/deactivation tests. (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#435](https://github.com/10up/restricted-site-access/pull/435))
- Pass WordPress Plugin Check. (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@jeffpaul](https://github.com/jeffpaul) via [#417](https://github.com/10up/restricted-site-access/pull/417))
- Relocate minimum required versions headers to readme.txt. (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter) via [#432](https://github.com/10up/restricted-site-access/pull/432))
- Update PHPCS to follow WordPress Coding Standards. (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter) via [#412](https://github.com/10up/restricted-site-access/pull/412))
- Update our PHPUnit workflow (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#388](https://github.com/10up/restricted-site-access/pull/388))
- Updated npm dependencies via `npm audit fix`. (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter) via [#381](https://github.com/10up/restricted-site-access/pull/381))
- Bump `@babel/plugin-transform-modules-systemjs` from 7.28.5 to 7.29.4 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#410](https://github.com/10up/restricted-site-access/pull/410))
- Bump `axios` from 1.13.2 to 1.18.1 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#402](https://github.com/10up/restricted-site-access/pull/402), [#407](https://github.com/10up/restricted-site-access/pull/407), [#423](https://github.com/10up/restricted-site-access/pull/423))
- Bump `basic-ftp` from 5.1.0 to 5.2.2 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#389](https://github.com/10up/restricted-site-access/pull/389), [#400](https://github.com/10up/restricted-site-access/pull/400), [#401](https://github.com/10up/restricted-site-access/pull/401))
- Bump `brace-expansion` from 1.1.12 to 1.1.13 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#397](https://github.com/10up/restricted-site-access/pull/397))
- Bump `diff` from 5.2.0 to 5.2.2 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#382](https://github.com/10up/restricted-site-access/pull/382))
- Bump `fast-uri` from 3.1.0 to 3.1.2 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#408](https://github.com/10up/restricted-site-access/pull/408))
- Bump `flatted` from 3.3.3 to 3.4.2 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#393](https://github.com/10up/restricted-site-access/pull/393))
- Bump `follow-redirects` from 1.15.11 to 1.16.0 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#403](https://github.com/10up/restricted-site-access/pull/403))
- Bump `immutable` from 5.1.4 to 5.1.9 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc), [@dependabot](https://github.com/dependabot) via [#390](https://github.com/10up/restricted-site-access/pull/390), [#425](https://github.com/10up/restricted-site-access/pull/425))
- Bump `ip-address` from 10.1.0 to 10.2.0 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#406](https://github.com/10up/restricted-site-access/pull/406))
- Bump `lodash` from 4.17.21 to 4.18.1 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#383](https://github.com/10up/restricted-site-access/pull/383), [#399](https://github.com/10up/restricted-site-access/pull/399))
- Bump `lodash-es` from 4.17.22 to 4.18.1 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#384](https://github.com/10up/restricted-site-access/pull/384), [#398](https://github.com/10up/restricted-site-access/pull/398))
- Bump `node-forge` from 1.3.3 to 1.4.0 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#396](https://github.com/10up/restricted-site-access/pull/396))
- Bump `phpunit/phpunit` from 9.4.4 to 9.6.33 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#385](https://github.com/10up/restricted-site-access/pull/385))
- Bump `picomatch` from 2.3.1 to 2.3.2 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#394](https://github.com/10up/restricted-site-access/pull/394))
- Bump `postcss` from 8.5.6 to 8.5.13 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#405](https://github.com/10up/restricted-site-access/pull/405))
- Bump `qs` from 6.14.1 to 6.15.3 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc), [@dependabot](https://github.com/dependabot) via [#387](https://github.com/10up/restricted-site-access/pull/387), [#415](https://github.com/10up/restricted-site-access/pull/415))
- Bump `shell-quote` from 1.8.3 to 1.10.0 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc), [@dependabot](https://github.com/dependabot), [@jeffpaul](https://github.com/jeffpaul) via [#415](https://github.com/10up/restricted-site-access/pull/415), [#424](https://github.com/10up/restricted-site-access/pull/424))
- Bump `simple-git` from 3.30.0 to 3.36.0 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#392](https://github.com/10up/restricted-site-access/pull/392), [#407](https://github.com/10up/restricted-site-access/pull/407))
- Bump `svgo` from 3.3.2 to 3.3.3 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#391](https://github.com/10up/restricted-site-access/pull/391))
- Bump `ws` from 7.5.10 to 8.21.1 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#421](https://github.com/10up/restricted-site-access/pull/421))
- Bump `form-data` from 4.0.5 to 4.0.6 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#420](https://github.com/10up/restricted-site-access/pull/420))
- Bump `launch-editor` from 2.12.0 to 2.14.1 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#420](https://github.com/10up/restricted-site-access/pull/420))
- Bump `tmp` from 0.2.5 to 0.2.7 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#414](https://github.com/10up/restricted-site-access/pull/414))
- Bump `webpack` from 5.99.8 to 5.105.0 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@jeffpaul](https://github.com/jeffpaul), [@peterwilsoncc](https://github.com/peterwilsoncc), [@dependabot](https://github.com/dependabot) via [#386](https://github.com/10up/restricted-site-access/pull/386))
- Bump `websocket-driver` from 0.7.4 to 0.7.5 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#422](https://github.com/10up/restricted-site-access/pull/422))
- Bump `wp-coding-standards/wpcs` from 3.3.0 to 3.4.1 (props [@dependabot[bot]](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#426](https://github.com/10up/restricted-site-access/pull/426))
- `@10up/cypress-wp-utils` updated from from 0.2.0 to 0.6.0. (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter) via [#381](https://github.com/10up/restricted-site-access/pull/381))
- `@wordpress/env` updated from 9.2.0 to 10.37.0 (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter) via [#381](https://github.com/10up/restricted-site-access/pull/381))
- `@wordpress/scripts` updated from 30.16.0 to 31.2.0 (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@dkotter](https://github.com/dkotter) via [#381](https://github.com/10up/restricted-site-access/pull/381))

## [7.6.1] - 2025-10-29
### Fixed
- Ensure field data is set properly before we use it. Resolves a fatal error with Elementor (props [@ktorktor](https://github.com/ktorktor), [Vishal Patel](https://profiles.wordpress.org/bhaveshnariya/), [fatjester](https://profiles.wordpress.org/fatjester/), [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#371](https://github.com/10up/restricted-site-access/pull/371)).

## [7.6.0] - 2025-10-27
### Added
- New setting allowing you to hide the WordPress admin bar on the frontend for specific user roles (props [@sanketio](https://github.com/sanketio), [@fabiankaegy](https://github.com/fabiankaegy), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#362](https://github.com/10up/restricted-site-access/pull/362)).
- New `RSA_NETWORK_MODE` constant to define default setting for network mode for multisite (props [@sanketio](https://github.com/sanketio), [@claytoncollie](https://github.com/claytoncollie), [@dkotter](https://github.com/dkotter) via [#363](https://github.com/10up/restricted-site-access/pull/363)).
- More details on how caching may impact the plugin (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@jakemgold](https://github.com/jakemgold), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [GHSA-jfqv-gvp2-qq5f](https://github.com/10up/restricted-site-access/security/advisories/GHSA-jfqv-gvp2-qq5f)).

### Fixed
- Ensure IP addresses can be saved properly at the network level (props [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#367](https://github.com/10up/restricted-site-access/pull/367)).

### Security
- Prevent caching of page content when using an IP allow list (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@fabiankaegy](https://github.com/fabiankaegy), [@joemcgill](https://github.com/joemcgill), [@jakemgold](https://github.com/jakemgold), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [GHSA-jfqv-gvp2-qq5f](https://github.com/10up/restricted-site-access/security/advisories/GHSA-jfqv-gvp2-qq5f)).
- Bump `cross-spawn` from 7.0.3 to 7.0.6, `@wordpress/scripts` from 29.0.0 to 30.16.0 and `http-proxy-middleware` from 2.0.6 to 2.0.9 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh) via [#355](https://github.com/10up/restricted-site-access/pull/355)).
- Bump `tar-fs` from 3.0.8 to 3.0.9 (props [@dependabot](https://github.com/apps/dependabot), [@faisal-alvi](https://github.com/faisal-alvi) via [#359](https://github.com/10up/restricted-site-access/pull/359)).
- Bump `brace-expansion` from 1.1.11 to 1.1.12, `on-headers` from 1.0.2 to 1.1.0 and `compression` from 1.7.4 to 1.8.1 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh) via [#361](https://github.com/10up/restricted-site-access/pull/361)).

### Developer
- Update screenshots to reflect current state of plugin (props [@iamdharmesh](https://github.com/iamdharmesh), [@rickalee](https://github.com/rickalee), [@jeffpaul](https://github.com/jeffpaul) via [#358](https://github.com/10up/restricted-site-access/pull/358)).
- Ensure all our GitHub Actions workflow files have proper permissions (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#360](https://github.com/10up/restricted-site-access/pull/360)).
- Fix issue with attaching release assets during release deploy action (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#364](https://github.com/10up/restricted-site-access/pull/364)).

## [7.5.3] - 2025-05-19
**Note that this version bumps the WordPress minimum supported version from 6.5 to 6.6.**

### Changed
- Bump WordPress "tested up to" version 6.8 (props [@kmgalanakis](https://github.com/kmgalanakis), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#349](https://github.com/10up/restricted-site-access/pull/349), [#352](https://github.com/10up/restricted-site-access/pull/352)).
- Bump WordPress minimum from 6.5 to 6.6 (props [@jeffpaul](https://github.com/jeffpaul) via [#351](https://github.com/10up/restricted-site-access/pull/351), [#352](https://github.com/10up/restricted-site-access/pull/352)).

### Fixed
- PHP Notice that the function `_load_textdomain_just_in_time` was called incorrectly (props [@kmgalanakis](https://github.com/kmgalanakis), [@dkotter](https://github.com/dkotter) via [#350](https://github.com/10up/restricted-site-access/pull/350)).

### Security
- Bump `axios` from 1.7.4 to 1.8.3 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh) via [#346](https://github.com/10up/restricted-site-access/pull/346)).

### Developer
- Update the number of tags in our readme (props [@jeffpaul](https://github.com/jeffpaul) via [#353](https://github.com/10up/restricted-site-access/pull/353)).
- Update all third-party actions our workflows rely on to use versions based on specific commit hashes (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#347](https://github.com/10up/restricted-site-access/pull/347)).

## [7.5.2] - 2025-02-05
**Note that this version bumps the WordPress minimum supported version from 6.4 to 6.5.**

### Changed
- Bump WordPress "tested up to" version 6.7 (props [@sudip-md](https://github.com/sudip-md), [@jeffpaul](https://github.com/jeffpaul), [@mehidi258](https://github.com/mehidi258) via [#335](https://github.com/10up/restricted-site-access/pull/335), [#336](https://github.com/10up/restricted-site-access/pull/336)).
- Bump WordPress minimum from 6.4 to 6.5 (props [@sudip-md](https://github.com/sudip-md), [@jeffpaul](https://github.com/jeffpaul), [@mehidi258](https://github.com/mehidi258) via [#335](https://github.com/10up/restricted-site-access/pull/335), [#336](https://github.com/10up/restricted-site-access/pull/336)).

### Fixed
- Add missing textdomain to a few strings (props [@NekoJonez](https://github.com/NekoJonez), [@dkotter](https://github.com/dkotter) via [#338](https://github.com/10up/restricted-site-access/pull/338)).

### Security
- Bump `axios` from 1.6.7 to 1.7.4 (props [@dependabot](https://github.com/apps/dependabot), [@faisal-alvi](https://github.com/faisal-alvi) via [#326](https://github.com/10up/restricted-site-access/pull/326)).
- Bump `webpack` from 5.90.0 to 5.94.0 (props [@dependabot](https://github.com/apps/dependabot), [@faisal-alvi](https://github.com/faisal-alvi) via [#327](https://github.com/10up/restricted-site-access/pull/327)).
- Bump `ws` from 7.5.10 to 8.18.0 and `@wordpress/scripts` from 27.1.0 to 29.0.0 (props [@dependabot](https://github.com/apps/dependabot), [@faisal-alvi](https://github.com/faisal-alvi) via [#328](https://github.com/10up/restricted-site-access/pull/328)).
- Bump `express` from 4.19.2 to 4.21.2, `send` from 0.18.0 to 0.19.0 and `serve-static` from 1.15.0 to 1.16.2 (props [@dependabot](https://github.com/apps/dependabot), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#340](https://github.com/10up/restricted-site-access/pull/340)).
- Bump `@wordpress/e2e-test-utils-playwright` from 1.7.0 to 1.16.0, `nanoid` from 3.3.7 to 3.3.8, `mocha` from 10.2.0 to 11.0.1 and removes `cookie` (props [@dependabot](https://github.com/apps/dependabot), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#341](https://github.com/10up/restricted-site-access/pull/341)).

### Developer
- Support for the WordPress.org plugin preview (props [@Sidsector9](https://github.com/Sidsector9), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#330](https://github.com/10up/restricted-site-access/pull/330)).
- Fix typo in the changelog URL (props [@chandrapatel](https://github.com/chandrapatel), [@jeffpaul](https://github.com/jeffpaul) via [#333](https://github.com/10up/restricted-site-access/pull/333)).
- Disable linting on external libraries (props [@Sidsector9](https://github.com/Sidsector9), [@dkotter](https://github.com/dkotter) via [#323](https://github.com/10up/restricted-site-access/pull/323)).
- Add plugin banner image to README and update badges (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#329](https://github.com/10up/restricted-site-access/pull/329), [#332](https://github.com/10up/restricted-site-access/pull/332)).

## [7.5.1] - 2024-07-09
**Note that this version bumps the WordPress minimum supported version from 5.7 to 6.4.**

### Changed
- Bump WordPress "tested up to" version 6.6 (props [@sudip-md](https://github.com/sudip-md), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#313](https://github.com/10up/restricted-site-access/pull/313), [#318](https://github.com/10up/restricted-site-access/pull/318)).
- Bump WordPress minimum from 5.7 to 6.4 (props [@sudip-md](https://github.com/sudip-md), [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#313](https://github.com/10up/restricted-site-access/pull/313), [#318](https://github.com/10up/restricted-site-access/pull/318)).

### Security
- Bump `tj-actions/changed-files` from 32 to 41 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh) via [#297](https://github.com/10up/restricted-site-access/pull/297)).
- Bump `express` from 4.18.2 to 4.19.2 (props [@dependabot](https://github.com/apps/dependabot), [@Sidsector9](https://github.com/Sidsector9) via [#312](https://github.com/10up/restricted-site-access/pull/312)).
- Bump `follow-redirects` from 1.15.5 to 1.15.6 (props [@dependabot](https://github.com/apps/dependabot), [@Sidsector9](https://github.com/Sidsector9) via [#312](https://github.com/10up/restricted-site-access/pull/312)).
- Bump `webpack-dev-middleware` from 5.3.3 to 5.3.4 (props [@dependabot](https://github.com/apps/dependabot), [@Sidsector9](https://github.com/Sidsector9) via [#312](https://github.com/10up/restricted-site-access/pull/312)).
- Bump `braces` from 3.0.2 to 3.0.3 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh) via [#319](https://github.com/10up/restricted-site-access/pull/319)).
- Bump `pac-resolver` from 7.0.0 to 7.0.1 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh) via [#319](https://github.com/10up/restricted-site-access/pull/319)).
- Bump `socks` from 2.7.1 to 2.8.3 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh) via [#319](https://github.com/10up/restricted-site-access/pull/319)).
- Bump `ws` from 7.5.9 to 7.5.10 (props [@dependabot](https://github.com/apps/dependabot), [@iamdharmesh](https://github.com/iamdharmesh) via [#319](https://github.com/10up/restricted-site-access/pull/319)).

### Developer
- Clean up NPM dependencies and update node to v20 (props [@Sidsector9](https://github.com/Sidsector9), [@dkotter](https://github.com/dkotter) via [#303](https://github.com/10up/restricted-site-access/pull/303)).
- Update `CODEOWNERS` (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#300](https://github.com/10up/restricted-site-access/pull/300)).
- Disabled auto sync pull requests with target branch (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul) via [#307](https://github.com/10up/restricted-site-access/pull/307)).
- Upgrade `download-artifact` from v3 to v4 (props [@iamdharmesh](https://github.com/iamdharmesh), [@jeffpaul](https://github.com/jeffpaul) via [#309](https://github.com/10up/restricted-site-access/pull/309)).
- Replaced [lee-dohm/no-response](https://github.com/lee-dohm/no-response) with [actions/stale](https://github.com/actions/stale) to help with closing no-response/stale issues (props [@jeffpaul](https://github.com/jeffpaul), [@dkotter](https://github.com/dkotter) via [#310](https://github.com/10up/restricted-site-access/pull/310)).
- Added a "Testing" section in the `CONTRIBUTING.md` file (props [@kmgalanakis](https://github.com/kmgalanakis), [@jeffpaul](https://github.com/jeffpaul) via [#314](https://github.com/10up/restricted-site-access/pull/314)).
- Removed `ip` dependency (props [@dependabot](https://github.com/apps/dependabot), [@Sidsector9](https://github.com/Sidsector9), [@iamdharmesh](https://github.com/iamdharmesh) via [#312](https://github.com/10up/restricted-site-access/pull/312), [#319](https://github.com/10up/restricted-site-access/pull/319)).

## [7.5.0] - 2023-12-14
**Note:** this release changes the default behavior for new installs in regards to IP detection. This shouldn't impact existing installs but there are two filters that can be used to change this behavior. See the [readme](https://github.com/10up/restricted-site-access#how-secure-is-this-plug-in) for full details.

### Fixed
- Update code snippet in the readme (props [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#291](https://github.com/10up/restricted-site-access/pull/291)).

### Security
- For new installs, ensure we only trust the `REMOTE_ADDR` HTTP header by default. Existing installs will still utilize the old list of approved headers but can modify this (and are recommended to) by using the `rsa_trusted_headers` filter (props [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc), [@dustinrue](https://github.com/dustinrue), [@mikhail-net](https://github.com/mikhail-net), [Darius Sveikauskas](https://patchstack.com/) via [#290](https://github.com/10up/restricted-site-access/pull/290)).
- Bump `axios` from 0.25.0 to 1.6.2 and `@wordpress/scripts` from 23.7.2 to 26.19.0 (props [@dependabot](https://github.com/apps/dependabot), [@dkotter](https://github.com/dkotter) via [#293](https://github.com/10up/restricted-site-access/pull/293)).

## [7.4.1] - 2023-11-14
### Added
- GitHub Action summary report for Cypress end-to-end tests (props [@jayedul](https://github.com/jayedul), [@Sidsector9](https://github.com/Sidsector9) via [#258](https://github.com/10up/restricted-site-access/pull/258)).
- `Restricted_Site_Access::append_ips()` method to add IP addresses programatically (props [@Sidsector9](https://github.com/Sidsector9), [@faisal-alvi](https://github.com/faisal-alvi) via [#267](https://github.com/10up/restricted-site-access/pull/267)).
- Repository Automator GitHub Action (props [@iamdharmesh](https://github.com/iamdharmesh), [@Sidsector9](https://github.com/Sidsector9) via [#273](https://github.com/10up/restricted-site-access/pull/273)).

### Changed
- Bumped WordPress "tested up to" version 6.4 (props [@kirtangajjar](https://github.com/kirtangajjar), [@Sidsector9](https://github.com/Sidsector9), [@qasumitbagthariya](https://github.com/qasumitbagthariya), [@jeffpaul](https://github.com/jeffpaul) via [#271](https://github.com/10up/restricted-site-access/pull/271), [#288](https://github.com/10up/restricted-site-access/pull/288)).
- WordPress compatibility validation library namespace (props [@Sidsector9](https://github.com/Sidsector9), [@dkotter](https://github.com/dkotter) via [#278](https://github.com/10up/restricted-site-access/pull/278)).
- Documentation to clarify what the restricted site access & discourage search engine options do (props [@lkraav](https://github.com/lkraav), [@jeffpaul](https://github.com/jeffpaul), [@helen](https://github.com/helen), [@dinhtungdu](https://github.com/dinhtungdu), [@bmarshall511](https://github.com/bmarshall511), [@Sidsector9](https://github.com/Sidsector9) via [#262](https://github.com/10up/restricted-site-access/pull/262)).
- Updates the Dependency Review GitHub Action to check for GPL-compatible licenses (props [@jeffpaul](https://github.com/jeffpaul), [@Sidsector9](https://github.com/Sidsector9) via [#261](https://github.com/10up/restricted-site-access/pull/261)).

### Fixed
- Issue with autovivification (props [@mae829](https://github.com/mae829), [@Sidsector9](https://github.com/Sidsector9) via [#281](https://github.com/10up/restricted-site-access/pull/281), [@turtlepod](https://github.com/turtlepod) via [#281](https://github.com/10up/restricted-site-access/pull/281)).

### Security
- Add PHP environment compatibility checker (props [@vikrampm1](https://github.com/vikrampm1), [@Sidsector9](https://github.com/Sidsector9) via [#268](https://github.com/10up/restricted-site-access/pull/268)).
- Bump `word-wrap` from `1.2.3` to `1.2.4` (props [@Sidsector9](https://github.com/Sidsector9) via [#266](https://github.com/10up/restricted-site-access/pull/266)).
- Bump `semver` from `5.7.1` to `5.7.2` (props [@Sidsector9](https://github.com/Sidsector9) via [#264](https://github.com/10up/restricted-site-access/pull/264)).
- Bump `tough-cookie` from `4.1.2` to `4.1.3` (props [@Sidsector9](https://github.com/Sidsector9) via [#270](https://github.com/10up/restricted-site-access/pull/270)).
- Bump `@cypress/request` from `2.88.10` to `2.88.12` (props [@Sidsector9](https://github.com/Sidsector9) via [#270](https://github.com/10up/restricted-site-access/pull/270)).
- Bump `postcss` from `8.4.18` to `8.4.31` (props [@Sidsector9](https://github.com/Sidsector9) via [#279](https://github.com/10up/restricted-site-access/pull/279)).
- Bump `@babel/traverse` from `7.20.0` to `7.23.2` (props [@Sidsector9](https://github.com/Sidsector9) via [#279](https://github.com/10up/restricted-site-access/pull/279)).
- Bump `Cypress` version from `10.3.0` to `13.2.0` (props [@iamdharmesh](https://github.com/iamdharmesh), [@Sidsector9](https://github.com/Sidsector9) via [#276](https://github.com/10up/restricted-site-access/pull/276)).
- Bump `@10up/cypress-wp-utils` version to `0.2.0` (props [@iamdharmesh](https://github.com/iamdharmesh), [@Sidsector9](https://github.com/Sidsector9) via [#276](https://github.com/10up/restricted-site-access/pull/276)).
- Bump `@wordpress/env` version from `5.4.0` to `8.7.0` (props [@iamdharmesh](https://github.com/iamdharmesh), [@Sidsector9](https://github.com/Sidsector9) via [#276](https://github.com/10up/restricted-site-access/pull/276)).
- Bump `@babel/traverse` from 7.20.0 to 7.23.2 (props [@dependabot](https://github.com/apps/dependabot), [@Sidsector9](https://github.com/Sidsector9) via [#282](https://github.com/10up/restricted-site-access/pull/282)).

## [7.4.0] - 2023-04-18
### Added
- Support for application passwords (props [@kirtangajjar](https://github.com/kirtangajjar), [@peterwilsoncc](https://github.com/peterwilsoncc), [@Sidsector9](https://github.com/Sidsector9) via [#247](https://github.com/10up/restricted-site-access/pull/247)).
- Support for custom header based allow-listing (props [@mikelking](https://github.com/mikelking), [@ravinderk](https://github.com/ravinderk), [@dkotter](https://github.com/dkotter), [@jeffpaul](https://github.com/jeffpaul) via [#242](https://github.com/10up/restricted-site-access/pull/242)).

### Changed
- [Support Level](https://github.com/10up/restricted-site-access#support-level) from `Active` to `Stable` (props [@jeffpaul](https://github.com/jeffpaul), [@Sidsector9](https://github.com/Sidsector9) via [#244](https://github.com/10up/restricted-site-access/pull/244)).
- Bump WordPress "tested up to" version 6.2 (props [@jayedul](https://github.com/jayedul), [@Sidsector9](https://github.com/Sidsector9) via [251](https://github.com/10up/restricted-site-access/pull/251)).
- Improve Github actions workflow (props [@Sidsector9](https://github.com/Sidsector9), [@dkotter](https://github.com/dkotter) via [#227](https://github.com/10up/restricted-site-access/pull/227), [#253](https://github.com/10up/restricted-site-access/pull/253)).

### Fixed
- Plugin settings header UX (props [@barryceelen](https://github.com/barryceelen), [@Sidsector9](https://github.com/Sidsector9) via [#236](https://github.com/10up/restricted-site-access/pull/236)).
- Issue that caused redirect loop (props [@mikegibbons4](https://profiles.wordpress.org/mikegibbons4/), [@Sidsector9](https://github.com/Sidsector9), [@cadic](https://github.com/cadic), [@peterwilsoncc](https://github.com/peterwilsoncc)) via [#221](https://github.com/10up/restricted-site-access/issues/221).

### Security
- Run E2E tests on the final ZIP build (props [@iamdharmesh](https://github.com/iamdharmesh), [@jayedul](https://github.com/jayedul) via [#249](https://github.com/10up/restricted-site-access/pull/249)).
- Bump `json5` from `1.0.1` to `1.0.2` (props [@Sidsector9](https://github.com/Sidsector9) via [#241](https://github.com/10up/restricted-site-access/pull/241)).
- Bump `simple-git` from `3.15.0` to `3.16.0` (props [@Sidsector9](https://github.com/Sidsector9) via [#243](https://github.com/10up/restricted-site-access/pull/243)).
- Bump `http-cache-semantics` from 4.1.0 to 4.1.1 (props [@Sidsector9](https://github.com/Sidsector9) via [#245](https://github.com/10up/restricted-site-access/pull/245)).
- Bump `@sideway/formula` from 3.0.0 to 3.0.1 (props [@Sidsector9](https://github.com/Sidsector9) via [#246](https://github.com/10up/restricted-site-access/pull/246)).
- Bump `webpack` from `5.74.0` to `5.76.1` (props [@Sidsector9](https://github.com/Sidsector9) via [#248](https://github.com/10up/restricted-site-access/pull/248)).

## [7.3.5] - 2022-12-14
### Added
- Show an admin notice if our autoloader doesn't exist (props [@dkotter](https://github.com/dkotter), [@pablojmarti](https://github.com/pablojmarti), [@shahzaib10up](https://github.com/shahzaib10up), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#231](https://github.com/10up/restricted-site-access/pull/231)).

### Fixed
- Ensure we load our autoloader from the root of our plugin directory (props [@dkotter](https://github.com/dkotter), [@pablojmarti](https://github.com/pablojmarti), [@shahzaib10up](https://github.com/shahzaib10up), [@peterwilsoncc](https://github.com/peterwilsoncc) via [#231](https://github.com/10up/restricted-site-access/pull/231)).

### Changed
- Improved performance of our E2E tests (props [@Sidsector9](https://github.com/Sidsector9), [@iamdharmesh](https://github.com/iamdharmesh) via [#218](https://github.com/10up/restricted-site-access/pull/218)).
- Release instructions and release ZIP building via GitHub Action (props [@dkotter](https://github.com/dkotter), [@faisal-alvi](https://github.com/faisal-alvi) via [#232](https://github.com/10up/restricted-site-access/pull/232)).

### Security
- Bump `loader-utils` from 2.0.3 to 2.0.4 (props [@dependabot](https://github.com/apps/dependabot) via [#226](https://github.com/10up/restricted-site-access/pull/226)).
- Bump `simple-git` from 3.6.0 to 3.15.0 (props [@dependabot](https://github.com/apps/dependabot) via [#230](https://github.com/10up/restricted-site-access/pull/230)).

## [7.3.4] - 2022-11-01
### Fixed
- Fatal error due to missing vendor directory.

## [7.3.3] - 2022-10-31
### Added
- Support for IPv6 addresses (props [@jeffpaul](https://github.com/jeffpaul), [@Sidsector9](https://github.com/Sidsector9), [@cadic](https://github.com/cadic) via [#217](https://github.com/10up/restricted-site-access/pull/217)).
- Support for subnet range and pattern formats for IPv4 and IPv6 addresses (props [@jeffpaul](https://github.com/jeffpaul), [@Sidsector9](https://github.com/Sidsector9), [@cadic](https://github.com/cadic) via [#217](https://github.com/10up/restricted-site-access/pull/217)).
- WP VIP Coding Standards (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@faisal-alvi](https://github.com/faisal-alvi), [@eflorea](https://github.com/eflorea) via [#212](https://github.com/10up/restricted-site-access/pull/212)).

### Changed
- Improved adding IP user experience via settings (props [@ankitguptaindia](https://github.com/ankitguptaindia), [@dhanendran](https://github.com/dhanendran), [@Sidsector9](https://github.com/Sidsector9), [@dinhtungdu](https://github.com/dinhtungdu) via [#205](https://github.com/10up/restricted-site-access/pull/205)).
- Replace Grunt with Webpack (props [@cadic](https://github.com/cadic), [@Sidsector9](https://github.com/Sidsector9) via [#202](https://github.com/10up/restricted-site-access/pull/202)).

### Fixed
- Missing textdomains to translatable strings (props [@pedro-mendonca](https://github.com/pedro-mendonca), [@Sidsector9](https://github.com/Sidsector9) via [#214](https://github.com/10up/restricted-site-access/pull/214)).

## [7.3.2] - 2022-08-29
**Note:** this release contains two new filters that we recommend using to further secure your site. See the [readme](https://github.com/10up/restricted-site-access#how-secure-is-this-plug-in) for full details.

### Added
- New filter - `rsa_get_client_ip_address_filter_flags` to modify the range of accepted IP addresses (props [@dsXLII](https://github.com/dsXLII), [@dinhtungdu](https://github.com/dinhtungdu), [@Sidsector9](https://github.com/Sidsector9) via [#113](https://github.com/10up/restricted-site-access/pull/113)).

### Changed
- Avoid disjointed plugin settings (props [@helen](https://github.com/helen), [@peterwilsoncc](https://github.com/peterwilsoncc), [@Sidsector9](https://github.com/Sidsector9) via [#200](https://github.com/10up/restricted-site-access/pull/200)).
- Bump minimum WordPress version from 5.0 to 5.7 (props [@vikrampm1](https://github.com/vikrampm1), [@Sidsector9](https://github.com/Sidsector9), [@faisal-alvi](https://github.com/faisal-alvi) via [#207](https://github.com/10up/restricted-site-access/pull/207)).
- Bump minimum PHP version from 5.6 to 7.4 (props [@vikrampm1](https://github.com/vikrampm1), [@Sidsector9](https://github.com/Sidsector9), [@faisal-alvi](https://github.com/faisal-alvi) via [#207](https://github.com/10up/restricted-site-access/pull/207)).

### Security
- New filters - `rsa_trusted_proxies` and `rsa_trusted_headers` have been added to help prevent IP spoofing attacks (props [@dkotter](https://github.com/dkotter), [@peterwilsoncc](https://github.com/peterwilsoncc), [@marcS0H](https://github.com/marcS0H), [@DanielRuf](https://github.com/DanielRuf), [@Sidsector9](https://github.com/Sidsector9) via [#198](https://github.com/10up/restricted-site-access/pull/198)).

## [7.3.1] - 2022-06-30
### Added
- PHP8 compatibility check GitHub Action (props [@Sidsector9](https://github.com/Sidsector9), [dkotter](https://github.com/dkotter) via [#183](https://github.com/10up/restricted-site-access/pull/183)).
- Dependency security scanning GitHub Action (props [@jeffpaul](https://github.com/jeffpaul) via [#188](https://github.com/10up/restricted-site-access/pull/188)).

### Changed
- Admin settings HTML semantics for easier testing (props [@Sidsector9](https://github.com/Sidsector9), [@faisal-alvi](https://github.com/faisal-alvi) via [#193](https://github.com/10up/restricted-site-access/pull/193)).
- Bump WordPress "tested up to" version 6.0 (props [@peterwilsoncc](https://github.com/peterwilsoncc), [@faisal-alvi](https://github.com/faisal-alvi), [@cadic](https://github.com/cadic), [@jeffpaul](https://github.com/jeffpaul) via [#194](https://github.com/10up/restricted-site-access/pull/194), [#196](https://github.com/10up/restricted-site-access/pull/196)).
- Documentation, asset, and e2e test updates (props [@Sidsector9](https://github.com/Sidsector9), [@iamdharmesh](https://github.com/iamdharmesh) via [#180](https://github.com/10up/restricted-site-access/pull/180), [#201](https://github.com/10up/restricted-site-access/pull/201)).

### Fixed
- Check netmask range before IP is added (props [@Sidsector9](https://github.com/Sidsector9), [@PypWalters](https://github.com/PypWalters) via [#178](https://github.com/10up/restricted-site-access/pull/178)).

### Security
- Bump `minimist` from 1.2.5 to 1.2.6 (props [@dependabot](https://github.com/apps/dependabot) via [#185](https://github.com/10up/restricted-site-access/pull/185)).
- Bump `grunt` from 1.4.1 to 1.5.3 (props [@dependabot](https://github.com/apps/dependabot) via [#189](https://github.com/10up/restricted-site-access/pull/189), [#199](https://github.com/10up/restricted-site-access/pull/199)).
- Bump `async` from 2.6.3 to 2.6.4 (props [@dependabot](https://github.com/apps/dependabot) via [#190](https://github.com/10up/restricted-site-access/pull/190)).

## [7.3.0] - 2022-02-08
### Added
- Ability to add, remove, and set IPs programatically (props [@ivankruchkoff](https://github.com/ivankruchkoff), [@helen](https://github.com/helen), [@paulschreiber](https://github.com/paulschreiber) via [#104](https://github.com/10up/restricted-site-access/pull/104)).
- Cloudflare IP detection compatibility (props [@eightam](https://github.com/eightam), [@dinhtungdu](https://github.com/dinhtungdu) via [#110](https://github.com/10up/restricted-site-access/pull/110)).
- WP-CLI option to modify and retrieve IP entry labels (props [@Sidsector9](https://github.com/Sidsector9), [@dinhtungdu](https://github.com/dinhtungdu), [@mikelking](https://github.com/mikelking) via [#152](https://github.com/10up/restricted-site-access/pull/152)).
- Acceptance and end-to-end tests (props [@dinhtungdu](https://github.com/dinhtungdu), [@helen](https://github.com/helen), [@jeffpaul](https://github.com/jeffpaul), [@Sidsector9](https://github.com/Sidsector9), [@cadic](https://github.com/cadic) via [#121](https://github.com/10up/restricted-site-access/pull/121), [#132](https://github.com/10up/restricted-site-access/pull/132), [#155](https://github.com/10up/restricted-site-access/pull/155), [#169](https://github.com/10up/restricted-site-access/pull/169), [#175](https://github.com/10up/restricted-site-access/pull/175)).
- Issue management automation, JavaScript linting, and PHPUnit testing via GitHub Actions (props [@jeffpaul](https://github.com/jeffpaul), [@Sidsector9](https://github.com/Sidsector9), [@dinhtungdu](https://github.com/dinhtungdu), [@mitogh](https://github.com/mitogh) via [#154](https://github.com/10up/restricted-site-access/pull/154), [#161](https://github.com/10up/restricted-site-access/pull/161), [#171](https://github.com/10up/restricted-site-access/pull/171), [#177](https://github.com/10up/restricted-site-access/pull/177)).

### Changed
- Update WP-CLI code to use new API for add/remove/set IPs (props [@paulschreiber](https://github.com/paulschreiber), [@dinhtungdu](https://github.com/dinhtungdu) via [#130](https://github.com/10up/restricted-site-access/pull/130)).
- Bump WordPress "tested up to" version 5.9 (props [@dinhtungdu](https://github.com/dinhtungdu), [@jeffpaul](https://github.com/jeffpaul), [@ankitguptaindia](https://github.com/ankitguptaindia), [@BBerg10up](https://github.com/BBerg10up), [@sudip-10up](https://github.com/sudip-10up) via [#120](https://github.com/10up/restricted-site-access/pull/120), [#122](https://github.com/10up/restricted-site-access/pull/122), [#141](https://github.com/10up/restricted-site-access/pull/141), [#149](https://github.com/10up/restricted-site-access/pull/149)).
- Improved Composer configuration and support (props [@kopepasah](https://github.com/kopepasah), [@dinhtungdu](https://github.com/dinhtungdu) via [#128](https://github.com/10up/restricted-site-access/pull/128)).
- Improved documentation (props [@jeffpaul](https://github.com/jeffpaul), [@dinhtungdu](https://github.com/dinhtungdu), [@helen](https://github.com/helen) via [#146](https://github.com/10up/restricted-site-access/pull/146)).
- The default constant `WP_TESTS_DOMAIN` is replaced by a new constant `PHP_UNIT_TESTS_ENV` to allow testing correct redirections for restricted users by Cypress end-to-end tests (props [@faisal-alvi](https://github.com/faisal-alvi), [@Sidsector9](https://github.com/Sidsector9), [@dkotter](https://github.com/dkotter) via [#159](https://github.com/10up/restricted-site-access/pull/159)).

### Fixed
- Issue with allowed IPs and associated comments being offset (props [@adamsilverstein](https://github.com/adamsilverstein), [@helen](https://github.com/helen), [@ivankruchkoff](https://github.com/ivankruchkoff) via [#106](https://github.com/10up/restricted-site-access/pull/106)).
- Prevents new users from getting WordPress setup email, new user flow in multisite installations now work as expected (props [@dinhtungdu](https://github.com/dinhtungdu), [@wkw](https://github.com/wkw), [@jeffpaul](https://github.com/jeffpaul), [@ivanlopez](https://github.com/ivanlopez) via [#116](https://github.com/10up/restricted-site-access/pull/116)).
- Ensure assets are enqueued on correct screen only (props [@kopepasah](https://github.com/kopepasah), [@dinhtungdu](https://github.com/dinhtungdu), [@paulschreiber](https://github.com/paulschreiber), [@n8dnx](https://github.com/n8dnx) via [#123](https://github.com/10up/restricted-site-access/pull/123), [#131](https://github.com/10up/restricted-site-access/pull/131)).
- Use correct variable for screen reader text (props [@dinhtungdu](https://github.com/dinhtungdu), [@lkraav](https://github.com/lkraav) via [#126](https://github.com/10up/restricted-site-access/pull/126)).
- Set the correct filter option value to `site_public` if `RSA_FORBID_RESTRICTION` is defined (props [@pabamato](https://github.com/pabamato), [@dinhtungdu](https://github.com/dinhtungdu) via [#139](https://github.com/10up/restricted-site-access/pull/139)).
- Prevent redirect loops when Redirect URL set on the same domain with or without Redirect to same path enabled (props [@Sidsector9](https://github.com/Sidsector9), [@faisal-alvi](https://github.com/faisal-alvi), [@cadic](https://github.com/cadic) via [#158](https://github.com/10up/restricted-site-access/pull/158)).
- Undefined key "url" warning (props [@Sidsector9](https://github.com/Sidsector9) via [#163](https://github.com/10up/restricted-site-access/pull/163)).
- `Redirect to same path` setting screen-reader-text (props [@pedro-mendonca](https://github.com/pedro-mendonca) via [#168](https://github.com/10up/restricted-site-access/pull/168)).
- No loading of JS admin scripts on the network admin page (props [@Sidsector9](https://github.com/Sidsector9), [@dinhtungdu](https://github.com/dinhtungdu) via [#175](https://github.com/10up/restricted-site-access/pull/175)).

### Security
- Bump `websocket-extensions` from 0.1.3 to 0.1.4 (props [@dependabot](https://github.com/apps/dependabot) via [#129](https://github.com/10up/restricted-site-access/pull/129), [#166](https://github.com/10up/restricted-site-access/pull/166)).
- Bump `lodash` from 4.17.15 to 4.17.21 (props [@dependabot](https://github.com/apps/dependabot) via [#133](https://github.com/10up/restricted-site-access/pull/133), [#145](https://github.com/10up/restricted-site-access/pull/145), [#165](https://github.com/10up/restricted-site-access/pull/165)).
- Bump `rmccue/requests` from 1.7.0 to 1.8.0 (props [@dependabot](https://github.com/apps/dependabot) via [#143](https://github.com/10up/restricted-site-access/pull/143)).
- Bump `grunt` from 1.0.4 to 1.3.0 (props [@dependabot](https://github.com/apps/dependabot) via [#144](https://github.com/10up/restricted-site-access/pull/144)).
- Bump `path-parse` from 1.0.6 to 1.0.7 (props [@dependabot](https://github.com/apps/dependabot) via [#151](https://github.com/10up/restricted-site-access/pull/151)).

## [7.2.0] - 2019-11-27
### Added
- Warn and confirm before network disabling the plugin (props [@pereirinha](https://github.com/pereirinha), [@adamsilverstein](https://github.com/adamsilverstein) via [#29](https://github.com/10up/restricted-site-access/pull/29)).
- WP Acceptance integration tests (props [@dkotter](https://github.com/dkotter), [@adamsilverstein](https://github.com/adamsilverstein) via [#86](https://github.com/10up/restricted-site-access/pull/86)).

### Fixed
- Ensure comments associated with IPs stay associated correctly (props [@adamsilverstein](https://github.com/adamsilverstein), [@ivankruchkoff](https://github.com/ivankruchkoff), [@helen](https://github.com/helen) via [#106](https://github.com/10up/restricted-site-access/pull/106)).
- Don't show escaped HTML in page caching notice (props [@adamsilverstein](https://github.com/adamsilverstein), [@aaemnnosttv](https://github.com/aaemnnosttv) via [#99](https://github.com/10up/restricted-site-access/pull/99)).
- Multisite: Avoid a redirect loop when logging in as user with no role (props [@JayWood](https://github.com/JayWood), [@adamsilverstein](https://github.com/adamsilverstein), [@roytanck](https://github.com/roytanck), [@helen](https://github.com/helen), [@rmccue](https://github.com/rmccue) via [#98](https://github.com/10up/restricted-site-access/pull/98)).

### Changed
- GitHub Actions workflow files to YAML format (props [@helen](https://github.com/helen) via [#100](https://github.com/10up/restricted-site-access/pull/100)).
- Header and icon images (props [@jenniferbourn](https://profiles.wordpress.org/jenniferbourn/) via [#91](https://github.com/10up/restricted-site-access/pull/91)).
- Bump WordPress "tested up to" version (props [@adamsilverstein](https://github.com/adamsilverstein) via [#84](https://github.com/10up/restricted-site-access/pull/84)).

## [7.1.0] - 2019-04-11
### Added
- IP whitelist: Add a Comment field next to each IP address to help identify IP addresses added to the whitelist.
- Add constants to force enable/disable restrictions. Set `RSA_FORCE_RESTRICTION` to `true` to force restriction or `RSA_FORBID_RESTRICTION` to disable restriction. `RSA_FORCE_RESTRICTION` will override `RSA_FORBID_RESTRICTION` if both are set.
- Unit tests accross plugin. Note that when the `WP_TESTS_DOMAIN` constant is set, plugin redirects are disabled. Only set this constant when running the tests.
- Deploy plugin from GitHub to WordPress.org using GitHub Actions.
- Various GitHub community files.

### Fixed
- Disable individual site settings when network enforced mode is on to avoid confusion about why your settings are not being respected.
- Correctly load admin JS.
- Improve coding standards across plugin and introduce continuous integration linting against the WordPress coding standards. Update code to VIP Go coding standards.

## [7.0.1] - 2018-09-06
### Fixed
- Avoid redirect loop when the unrestricted page is set to be the static front page.
- Fall back to the login screen if the unrestricted page is no longer published.

## [7.0.0] - 2018-08-30
### Added
- WP-CLI support! 🎉 Try `wp rsa` to get started.
- Whitelist IPs via the `RSA_IP_WHITELIST` constant.
- Use WordPress.org-provided language packs instead of bundled translations.

### Fixed
- Restrict "virtual pages" and allow them to be used as the unrestricted page, such as with BuddyPress.
- Hide settings properly when no published pages exist.
- Avoid double slashes in asset URLs that can lead to 404 errors.

## [6.2.1] - 2018-05-21
### Fixed
- Don't redirect logged-in users viewing the site in a single site install.

## [6.2.0] - 2018-05-18
### Added
- Alter or restore previous user permission checking with the `restricted_site_access_user_can_access` filter.

### Changed
- **Functionality change:** Check user's role on a site in multisite before granting permission.

### Fixed
- Avoid a fatal due to differing parameter counts for the `restricted_site_access_is_restricted` filter.

## [6.1.0] - 2018-02-14
### Changed
- Correct a PHP notice when running PHP >= 7.1.
- Refactor logic for checking ip address is in masked ip range.

## [6.0.2] - 2018-01-29
### Added
- 'restrict_site_access_ip_match' action which fires when an ip match occurs. Enables adding session_start() to the IP check, ensuring Varnish type cache will not cache the request.

## [6.0.1] - 2017-06-13
### Changed
- When plugin is network activated, don't touch individual blog visiblity settings.
- When plugin is network deactivated, set all individual blogs to default visibility.

## [6.0] - 2017-06-12
### Added
- Use Grunt to manage assets.
- Network settings added for management of entire network visibility settings.
- Display warning if page caching is enabled.

## [5.1] - 2014-11-29
### Changed
- Under the hood refactoring and clean up for performance and maintainability.
- Small visual refinements to the settings panel.

## [5.0.1] - 2013-01-27
### Fixed
- Does not block user activation page in network mode

## [5.0] - 2012-11-02
### Added
- WordPress 3.5 compatibility (3.5 eliminated the Privacy settings panel in favor of a refreshed Reading panel)

### Changed
- Real validation (on the fly and on save) for IP address entries
- "Restriction message" now supports simple HTML and is edited using WordPress's simple HTML tag editor
- A bunch of visual refinements that conform better with WordPress 3.4 and newer (spacing, native "shake" effect on invalid entries just like the login form, etc.)
- A bunch of under the hood refinements (e.g. playing nicer with current screen Help API)

## [4.0] - 2011-07-16
### Added
- New restriction option - show restricted visitor a specified page; use with custom page templates for great for website teasers!
- New filter hooks for other developers: 'restricted_site_access_is_restricted', 'restricted_site_access_approach', 'restricted_site_access_redirect_url', and 'restricted_site_access_head'
- Localization ready - rough Spanish translation included!
- Basic support for no JavaScript mode

### Changed
- Major improvements to settings user interface, including hiding unused fields based on settings, easier selection of restriction type, and cleaner "remove" confirmation for IP address list
- Performance improvements - catches and blocks restricted visitors earlier in the loading process
- Optimized for PHP 5.2, per new WordPress 3.2 requirements (no longer supports PHP < 5.2.4)
- Assorted other improvements and optimizations to the code base

## [3.2.1] - 2011-03-25
### Changed
- Restored PHP4 compatibility

## [3.2] - 2011-03-25
### Changed
- More meaningful page title in "Display Message" mode (previously WordPress > Error)
- Code clean up, prevent rare warnings in debug mode

## [3.1.1] - 2010-07-17
### Fixed
- PHP warning when debugging is enabled and redirect path is not checked

## [3.1] - 2010-07-11
### Added
- Backwards compatibility with PHP < 5.1 (limited testing with earlier versions)

### Changed
- Built in help on configuration page updated, clearer
- "IP already in list" indicator
- Optimizations to code that handles restriction behavior

### Fixed
- Disappearing blocked access message text box on configuration page
- Login always redirects visitor back to correct page

## [3.0] - 2010-07-05
### Added
- Indicates whether the site is blocked in the admin next to the site title (WordPress 3.0+ only)
- New action hook, `restrict_site_access_handling`, allowing developers to add their own restriction handling

### Changed
- Integrates with Privacy settings page and site visibility option instead of adding a whole new page
- Simplified options: clearer instructions, removed unnecessary hiding / showing of some options, fewer lines
- Cleans up / removes settings when uninstalled
- Assorted under the hood improvements for best coding practices, sanitization of options, etc

## [2.1] - 2010-02-10
### Changed
- Customize blocked visitor message
- Better display / handling of blocked visitor message

### Security
- Stronger security (patched "search" hole)

## [2.0] - 2010-01-10
### Added
- Support for IP ranges courtesy Eric Buth

### Changed
- Major UI changes and improvements; major code improvements

## [1.0.2] - 2009-10-13
### Fixed
- Login redirect to home; improve redirect handling to take advantage of wp_redirect function

## [1.0.1] - 2009-09-10
### Changed
- Important fundamental change related to handling of what should be restricted

## [1.0] - 2009-08-17
### Added
- Initial public release

[Unreleased]: https://github.com/10up/restricted-site-access/compare/trunk...develop
[7.6.2]: https://github.com/10up/restricted-site-access/compare/7.6.1...7.6.2
[7.6.1]: https://github.com/10up/restricted-site-access/compare/7.6.0...7.6.1
[7.6.0]: https://github.com/10up/restricted-site-access/compare/7.5.3...7.6.0
[7.5.3]: https://github.com/10up/restricted-site-access/compare/7.5.2...7.5.3
[7.5.2]: https://github.com/10up/restricted-site-access/compare/7.5.1...7.5.2
[7.5.1]: https://github.com/10up/restricted-site-access/compare/7.5.0...7.5.1
[7.5.0]: https://github.com/10up/restricted-site-access/compare/7.4.1...7.5.0
[7.4.1]: https://github.com/10up/restricted-site-access/compare/7.4.0...7.4.1
[7.4.0]: https://github.com/10up/restricted-site-access/compare/7.3.5...7.4.0
[7.3.5]: https://github.com/10up/restricted-site-access/compare/7.3.4...7.3.5
[7.3.4]: https://github.com/10up/restricted-site-access/compare/7.3.3...7.3.4
[7.3.3]: https://github.com/10up/restricted-site-access/compare/7.3.2...7.3.3
[7.3.2]: https://github.com/10up/restricted-site-access/compare/7.3.1...7.3.2
[7.3.1]: https://github.com/10up/restricted-site-access/compare/7.3.0...7.3.1
[7.3.0]: https://github.com/10up/restricted-site-access/compare/7.2.0...7.3.0
[7.2.0]: https://github.com/10up/restricted-site-access/compare/7.1.0...7.2.0
[7.1.0]: https://github.com/10up/restricted-site-access/compare/7.0.1...7.1.0
[7.0.1]: https://github.com/10up/restricted-site-access/compare/7.0.0...7.0.1
[7.0.0]: https://github.com/10up/restricted-site-access/compare/6.2.1...7.0.0
[6.2.1]: https://github.com/10up/restricted-site-access/compare/6.2.0...6.2.1
[6.2.0]: https://github.com/10up/restricted-site-access/compare/6.1.0...6.2.0
[6.1.0]: https://github.com/10up/restricted-site-access/compare/6.0.2...6.1.0
[6.0.2]: https://github.com/10up/restricted-site-access/compare/6.0.1...6.0.2
[6.0.1]: https://github.com/10up/restricted-site-access/compare/6.0...6.0.1
[6.0]: https://github.com/10up/restricted-site-access/compare/5.1..6.0
[5.1]: https://github.com/10up/restricted-site-access/compare/5.0.1..5.1
[5.0.1]: https://github.com/10up/restricted-site-access/compare/5.0...5.0.1
[5.0]: https://github.com/10up/restricted-site-access/compare/4.0...5.0
[4.0]: https://github.com/10up/restricted-site-access/compare/3.2.1...4.0
[3.2.1]: https://github.com/10up/restricted-site-access/compare/3.2...3.2.1
[3.2]: https://github.com/10up/restricted-site-access/compare/3.1.1...3.2
[3.1.1]: https://github.com/10up/restricted-site-access/compare/3.1...3.1.1
[3.1]: https://github.com/10up/restricted-site-access/compare/3.0...3.1
[3.0]: https://github.com/10up/restricted-site-access/compare/2.1..3.0
[2.1]: https://github.com/10up/restricted-site-access/compare/2.0..2.1
[2.0]: https://github.com/10up/restricted-site-access/compare/1.0.2..2.0
[1.0.2]: https://github.com/10up/restricted-site-access/compare/1.0.1..1.0.2
[1.0.1]: https://github.com/10up/restricted-site-access/compare/1.0..1.0.1
[1.0]: https://github.com/10up/restricted-site-access/releases/tag/1.0
