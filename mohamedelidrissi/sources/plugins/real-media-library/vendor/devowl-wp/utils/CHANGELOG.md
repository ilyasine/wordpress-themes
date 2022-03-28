# Change Log

All notable changes to this project will be documented in this file.
See [Conventional Commits](https://conventionalcommits.org) for commit guidelines.

## 1.11.1 (2021-10-12)


### chore

* always pull translations from remote repository
* translate plugin meta data correctly (plugin description, ..., CU-1kvxtge)


### fix

* use correct user locale for REST API requests in admin area when different from blog language (CU-1k51hkh)





# 1.11.0 (2021-09-30)


### build

* allow to define allowed locales to make release management possible (CU-1257b2b)
* copy files for i18n so we can drop override hooks and get performance boost (CU-wtt3hy)
* dependency map also gets generated even languages folder does not exist
* do not build development and production webpack concurrently to avoid OOM
* finalize mojito import, push and pull (CU-f94bdr)
* revert version in package.json to keep webpack cache intact (CU-qtd0c9)


### chore

* initial commit for @devowl-wp/grunt-mojito
* introduce weblate as continuous localization platform (CU-f94bdr)
* make build of plugins work together with Composer InstalledVersions fix
* no longer generate i18n files while development
* prepare for continuous localization with weblate (CU-f94bdr)
* refactor texts to use ellipses instead of ... (CU-f94bdr)
* remove language files from repository (CU-f94bdr)


### ci

* introduce continuous localization (CU-f94bdr)
* make continuous localization cache depending on current branch name (CU-1257b2b)


### feat

* translation into Russian (CU-10hyfnv)


### fix

* cache busting chunk translation files in frontend (CU-f94bdr)
* correctly load translation file from frontend folder (CU-wtt3hy)


### perf

* introduce un-prerelease mechanism instead of building the whole plugin again (CU-11eb54a)
* remove translation overrides in preference of language files (CU-wtt3hy)


### refactor

* grunt-mojito to abstract grunt-continuous-localization package (CU-f94bdr)
* introduce @devowl-wp/continuous-integration
* introduce new command with execa instead of own exec implementation





## 1.10.10 (2021-08-31)


### build

* generate i18n files correctly for bundled WP packages


### fix

* allow composer-patches for plugins (CU-wkuq39)





## 1.10.9 (2021-08-20)


### build

* introduce new grunt script to update all composer packages across monorepo
* scope composer files more accurate


### fix

* error Composer InstalledVersions class was declared twice and lead to fatal error (CU-w8kvcq)





## 1.10.8 (2021-08-10)


### chore

* translations into German (CU-pb8dpn)


### fix

* review 1 (CU-mtdp7v, CU-n1f1xc)
* review 2 (CU-7mvhak)
* review 3 (CU-7mvhak)





## 1.10.7 (2021-08-05)


### fix

* mixed languages in cookie settings dashboard when using formal germany language (CU-pzazqj9)





## 1.10.6 (2021-07-16)


### fix

* avoid to check for absolute pathes in Localization overwrites to avoid open_basedir issues (CU-nnff7m)





## 1.10.5 (2021-06-05)


### fix

* use priority language files over wp-content/languages (CU-ktatf6)





## 1.10.4 (2021-05-25)


### chore

* enqueue wp-polyfill for our utils; until we drop support for IE (CU-jh3cza)
* migarte loose mode to compiler assumptions
* polyfill setimmediate only if needed (CU-jh3czf)
* prettify code to new standard
* remove whatwg-fetch polyfill (CU-jh3czg)
* revert update of typedoc@0.20.x as it does not support monorepos yet
* update cypress@7
* update dependencies for safe major version bumps
* update immer@9
* upgrade dependencies to latest minor version





## 1.10.3 (2021-05-14)


### fix

* compatibility with Perfmatters users when delay JS is active (CU-jq8hzf)





## 1.10.2 (2021-05-12)


### fix

* compatibility with WP Rocket new DeferJS method since v3.9 (CU-jq4bhw)





## 1.10.1 (2021-05-11)


### fix

* automatically refetch announcments for updates (CU-jn95nz)
* **hotfix :** translations are not correctly updated (CU-jf8acx)





# 1.10.0 (2021-05-11)


### chore

* remove classnames as dependency
* **release :** publish [ci skip]


### feat

* native compatibility with preloading and defer scripts with caching plugins (CU-h75rh2)
* save previous versions of installed plugin in database for migrations (CU-g75t1p)


### fix

* allow to defer loading MobX and run configuration correctly (CU-j575je)
* compatibility with wp-json-less URLs and plain permalink settings (CU-j93mr8)
* do not output RCB settings as base64 encoded string (CU-gx8jkw)
* use updated link in REST API notice when not reachable


### perf

* introduce deferred and preloaded scripts for cookie banner (CU-gn4ng5)


### refactor

* create wp-webpack package for WordPress packages and plugins
* introduce eslint-config package
* introduce new grunt workspaces package for monolithic usage
* introduce new package to validate composer licenses and generate disclaimer
* introduce new package to validate yarn licenses and generate disclaimer
* introduce new script to run-yarn-children commands
* move build scripts to proper backend and WP package
* move jest scripts to proper backend and WP package
* move PHP CodeSniffer configuration to @devowl-wp/utils
* move PHP Unit bootstrap file to @devowl-wp/utils package
* move PHPUnit and Cypress scripts to @devowl-wp/utils package
* move technical doc scripts to proper WP and backend package
* move WP build process to @devowl-wp/utils
* move WP i18n scripts to @devowl-wp/utils
* move WP specific typescript config to @devowl-wp/wp-webpack package
* remove @devowl-wp/development package





## 1.9.4 (2021-03-23)


### fix

* add password-protected plugin as security plugin which blocks REST API (CU-g14ub7)





## 1.9.3 (2021-03-02)


### chore

* **release :** publish [ci skip]
* **release :** publish [ci skip]


### fix

* no longer markup plugin to avoid issues with quotes (wptexturize)


### test

* update tests for wptexturize bugfix





## 1.9.2 (2021-01-24)


### fix

* make restNonce option optional for public APIs (CU-cwvke2)





## 1.9.1 (2021-01-11)


### build

* reduce javascript bundle size by using babel runtime correctly with webpack / babel-loader


### chore

* **release :** publish [ci skip]


### fix

* caching issues with new versions in settings page
* compatibility with combine JS in newest WP Rocket update (CU-c11w2c)
* generate dependency map for translations
* wrong language for duplicated cookie when using PolyLang default language in admin dashboard





# 1.9.0 (2020-12-15)


### feat

* introduce code splitting with chunked translations (CU-b10ahe)





## 1.8.1 (2020-12-10)


### chore

* export sprintf as i18n method





# 1.8.0 (2020-12-09)


### feat

* more customizable multipart requests (CU-80q24e)





# 1.7.0 (2020-12-09)


### chore

* update to webpack v5 (CU-4akvz6)
* updates typings and min. Node.js and Yarn version (CU-9rq9c7)
* **release :** publish [ci skip]


### feat

* allow uploading files via commonRequest (CU-80q24e)
* introduce code splitting functionality to plugins (CU-b10ahe)


### fix

* anonymous localized script settings to avoid incompatibility with WP Rocket lazy execution (CU-b4rp51)





## 1.6.3 (2020-12-01)


### chore

* update dependencies (CU-3cj43t)
* update to composer v2 (CU-4akvjg)
* update to core-js@3 (CU-3cj43t)
* **release :** publish [ci skip]


### refactor

* enforce explicit-member-accessibility (CU-a6w5bv)





## 1.6.2 (2020-11-24)


### fix

* modify max index length for MySQL 5.6 databases so all database tables get created (CU-agzcrp)
* use no-store caching for WP REST API calls to avoid issues with browsers and CloudFlare (CU-agzcrp)





## 1.6.1 (2020-11-12)


### fix

* allow DELETE and PUT verbs to get empty response





# 1.6.0 (2020-10-23)


### feat

* add function getExternalContainerUrl to get backend URLs for frontend
* route PATCH PaddleIncompleteOrder (#8ywfdu)


### fix

* correctly detect usage of _method parameter


### refactor

* use "import type" instead of "import"





# 1.5.0 (2020-10-16)


### chore

* rename folder name (#94xp4g)


### feat

* announcements (#8cxk67)





## 1.4.7 (2020-10-09)


### fix

* delete requests to REST API does no longer set Content-Type (#90vkd5)





## 1.4.6 (2020-10-08)


### chore

* **release :** version bump





## 1.4.5 (2020-09-29)


### build

* backend pot files and JSON generation conflict-resistent (#6utk9n)


### chore

* introduce development package (#6utk9n)
* move backend files to development package (#6utk9n)
* move grunt to common package (#6utk9n)
* move packages to development package
* move packages to development package (#6utk9n)
* move some files to development package (#6utk9n)
* prepare package grunt scripts (#6utk9n)
* update dependencies (#3cj43t)





## 1.4.4 (2020-09-22)


### fix

* do not use encodeURIComponent as it is supported by url-parse by default
* import settings (#82rk4n)
* truncate -lite and -pro from REST service (#82rgxu)





## 1.4.3 (2020-08-26)


### ci

* install container volume with unique name (#7gmuaa)


### perf

* remove transients and introduce expire options for better performance (#7cqdzj)


### test

* fix ExpireOptionTest::testSet





## 1.4.2 (2020-08-17)


### ci

* prefer dist in composer install





## 1.4.1 (2020-08-11)


### chore

* backends for monorepo introduced





# 1.4.0 (2020-07-30)


### feat

* introduce dashboard with assistant (#68k9ny)
* WordPress 5.5 compatibility (#6gqcm8)





# 1.3.0 (2020-07-02)


### chore

* allow to define allowed licenses in root package.json (#68jvq7)
* update dependencies (#3cj43t)


### feat

* use window.fetch with polyfill instead of jquery (#5whc2c)





# 1.2.0 (2020-06-17)


### feat

* email input (with privacy checkbox) (#5ymj7f), 'none' option (#5ymhx1), reason note required (#5ymhjt)





# 1.1.0 (2020-06-12)


### chore

* i18n update (#5ut991)


### ci

* use hot cache and node-gitlab-ci (#54r34g)


### feat

* add abstract post and category REST model (#5phrh4)





## 1.0.8 (2020-05-20)


### chore

* move plugin/rcb branch to develop


### fix

* add PATCH to available HTTP methods (#5cjaau)
* remove ~ due to G6 blacklist filtering (security plugins, #5cqdn0)





## 1.0.7 (2020-05-12)


### build

* cleanup temporary i18n files correctly


### fix

* correctly enqueue dependencies (#52jf92)
* improvement speed up in admin dashboard (#52gj39)
* install database tables after reactivate plugin (#52k7f1)
* use correct assets class





## 1.0.6 (2020-04-27)


### chore

* add hook_suffix to enqueue_scripts_and_styles function (#4ujzx0)
* **release :** publish [ci skip]


### fix

* cronjob URL not working with plain permalink setting (#4pmk26, #4ar47j)





## 1.0.5 (2020-04-16)


### build

* move test namespaces to composer autoload-dev (#4jnk84)
* optional clean:webpackDevBundles grunt task to remove dev bundles in build artifact (#4jjq0u)
* scope PHP vendor dependencies (#4jnk84)


### chore

* create real-ad package to introduce more UX after installing the plugin (#1aewyf)
* rename real-ad to real-utils (#4jpg5f)


### ci

* correctly build i18n frontend files (#4jjq0u)
* run package jobs also on devops changes


### docs

* broken links in developer documentation (#5yg1cf)


### style

* reformat php codebase (#4gg05b)


### test

* fix typo in test files





## 1.0.4 (2020-03-31)


### chore

* update dependencies (#3cj43t)
* **release :** publish [ci skip]
* **release :** publish [ci skip]
* **release :** publish [ci skip]
* **release :** publish [ci skip]


### ci

* use concurrency 1 in yarn disclaimer generation


### test

* configure jest setupFiles correctly with enzyme and clearMocks (#4akeab)
* generate test reports (#4cg6tp)





## 1.0.3 (2020-03-05)


### build

* chunk vendor libraries (#3wkvfe) and update antd@4 (#3wnntb)


### chore

* update dependencies (webpack, types)
* **release :** publish [ci skip]





## 1.0.2 (2020-02-26)


### build

* migrate real-thumbnail-generator to monorepo


### fix

* usage of React while using Divi in dev environment (WP_DEBUG, #3rfqjk)
* use own wp_set_script_translations to make it compatible with deferred scripts (#3mjh0e)





## 1.0.1 (2020-02-13)


### fix

* do not load script translations for libraries (#3mjh0e)
