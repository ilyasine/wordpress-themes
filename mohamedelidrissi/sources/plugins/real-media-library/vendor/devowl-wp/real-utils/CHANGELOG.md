# Change Log

All notable changes to this project will be documented in this file.
See [Conventional Commits](https://conventionalcommits.org) for commit guidelines.

## 1.7.1 (2021-10-12)


### fix

* use correct user locale for REST API requests in admin area when different from blog language (CU-1k51hkh)





# 1.7.0 (2021-09-30)


### build

* allow to define allowed locales to make release management possible (CU-1257b2b)
* copy files for i18n so we can drop override hooks and get performance boost (CU-wtt3hy)


### chore

* prepare for continuous localization with weblate (CU-f94bdr)
* refactor texts to use ellipses instead of ... (CU-f94bdr)
* remove language files from repository (CU-f94bdr)
* review english texts (CU-12w4012)


### ci

* introduce continuous localization (CU-f94bdr)


### feat

* translation into Russian (CU-10hyfnv)


### perf

* remove translation overrides in preference of language files (CU-wtt3hy)


### refactor

* grunt-mojito to abstract grunt-continuous-localization package (CU-f94bdr)
* introduce @devowl-wp/continuous-integration





## 1.6.12 (2021-08-20)


### chore

* update PHP dependencies





## 1.6.11 (2021-08-10)


### chore

* translations into German (CU-pb8dpn)


### refactor

* split i18n and request methods to save bundle size





## 1.6.10 (2021-05-25)


### chore

* migarte loose mode to compiler assumptions
* polyfill setimmediate only if needed (CU-jh3czf)
* prettify code to new standard
* upgrade dependencies to latest minor version


### fix

* do not rely on install_plugins capability, instead use activate_plugins so GIT-synced WP instances work too (CU-k599a2)





## 1.6.9 (2021-05-11)


### chore

* **release :** publish [ci skip]


### refactor

* create wp-webpack package for WordPress packages and plugins
* introduce eslint-config package
* introduce new grunt workspaces package for monolithic usage
* introduce new package to validate composer licenses and generate disclaimer
* introduce new package to validate yarn licenses and generate disclaimer
* introduce new script to run-yarn-children commands
* move build scripts to proper backend and WP package
* move jest scripts to proper backend and WP package
* move PHP Unit bootstrap file to @devowl-wp/utils package
* move PHPUnit and Cypress scripts to @devowl-wp/utils package
* move WP build process to @devowl-wp/utils
* move WP i18n scripts to @devowl-wp/utils
* move WP specific typescript config to @devowl-wp/wp-webpack package
* remove @devowl-wp/development package





## 1.6.8 (2021-03-30)


### chore

* **release :** publish [ci skip]


### refactor

* use composer autoload to setup constants and package localization





## 1.6.7 (2021-03-02)


### fix

* respect language of newsletter subscriber to assign to correct newsletter (CU-aar8y9)


### test

* typing mistakes (CU-ewzae8)





## 1.6.6 (2021-02-24)


### chore

* rename go-links to new syntax (#en621h)
* **release :** publish [ci skip]
* **release :** publish [ci skip]





## 1.6.5 (2021-01-11)


### build

* reduce javascript bundle size by using babel runtime correctly with webpack / babel-loader


### chore

* **release :** publish [ci skip]
* **release :** publish [ci skip]





## 1.6.4 (2020-12-09)


### chore

* update to webpack v5 (CU-4akvz6)
* updates typings and min. Node.js and Yarn version (CU-9rq9c7)
* **release :** publish [ci skip]





## 1.6.3 (2020-12-01)


### chore

* update dependencies (CU-3cj43t)
* update to composer v2 (CU-4akvjg)
* update to core-js@3 (CU-3cj43t)
* **release :** publish [ci skip]





## 1.6.2 (2020-11-24)


### fix

* do always show checkbox to force hiding cross selling pointer (CU-ajyaar)
* remove cross selling completely (CU-ajyaar)
* use no-store caching for WP REST API calls to avoid issues with browsers and CloudFlare (CU-agzcrp)





## 1.6.1 (2020-11-18)


### fix

* do not show Real Cookie Banner in welcome page when already installed (#acypm6)
* remove named cross selling pointers for Real Cookie Banner (#aew3kw)





# 1.6.0 (2020-11-17)


### feat

* enable cross selling ads for Real Cookie Banner (#4rruvq)
* translation (#8mrn5a)


### revert

* translation comments to avoid semver of all plugins





# 1.5.0 (2020-10-23)


### feat

* route PATCH PaddleIncompleteOrder (#8ywfdu)


### refactor

* use "import type" instead of "import"





## 1.4.1 (2020-10-16)


### chore

* deprecate feedback pointer (#8cxk67)
* rename folder name (#94xp4g)





# 1.4.0 (2020-10-09)


### feat

* add cross-selling for Real Cookie Banner (#4rrt0d)





# 1.3.0 (2020-10-08)


### chore

* **release :** version bump


### feat

* review two user tests, added VG WORT preset (#8wx249)





## 1.2.6 (2020-09-29)


### build

* backend pot files and JSON generation conflict-resistent (#6utk9n)


### chore

* introduce development package (#6utk9n)
* move backend files to development package (#6utk9n)
* move grunt to common package (#6utk9n)
* move packages to development package (#6utk9n)
* move some files to development package (#6utk9n)
* update dependencies (#3cj43t)
* update package.json script for WordPress packages (#6utk9n)


### fix

* show pro advertisement only in 7 days interval in lite version (#8jthkf)





## 1.2.5 (2020-09-22)


### fix

* allow more than three key features (#82uuh5)
* allow to set image height for welcome page key features (#4rru6v)
* import settings (#82rk4n)
* review 1 (#86wk0t)
* review 3 (#86wk0t)





## 1.2.4 (2020-08-31)


### fix

* avoid duplicate pointers and fix RCM pointer position (#7pvy6a)
* error on frontend page when Real Media Library is loaded (#7mw525)





## 1.2.3 (2020-08-26)


### ci

* install container volume with unique name (#7gmuaa)


### fix

* pagination add for RCM in firefox (#7gma2b)


### perf

* remove transients and introduce expire options for better performance (#7cqdzj)





## 1.2.2 (2020-08-17)


### ci

* prefer dist in composer install


### fix

* bug when redirecting to welcome page and active WPML (#4wqqym)





## 1.2.1 (2020-08-11)


### chore

* backends for monorepo introduced





# 1.2.0 (2020-07-30)


### feat

* introduce dashboard with assistant (#68k9ny)
* WordPress 5.5 compatibility (#6gqcm8)


### fix

* backupbuddy compatibility (#6mmxmj)





## 1.1.1 (2020-07-02)


### chore

* allow to define allowed licenses in root package.json (#68jvq7)
* update dependencies (#3cj43t)
* update to typescript 3.9.5





# 1.1.0 (2020-06-17)


### feat

* email input (with privacy checkbox) (#5ymj7f), 'none' option (#5ymhx1), reason note required (#5ymhjt)





## 1.0.7 (2020-06-12)


### chore

* i18n update (#5ut991)
* update translation pot file (CU-7pezg1)


### ci

* use hot cache and node-gitlab-ci (#54r34g)


### fix

* rename translation files of real-utils





## 1.0.6 (2020-05-20)


### chore

* move plugin/rcb branch to develop


### fix

* remove ~ due to G6 blacklist filtering (security plugins, #5cqdn0)


### test

* adjust assets bump





## 1.0.5 (2020-05-12)


### build

* cleanup temporary i18n files correctly


### fix

* correctly enqueue dependencies (#52jf92)
* shortcut info list has duplicates in some cases
* use correct assets class





## 1.0.4 (2020-04-27)


### chore

* add hook_suffix to enqueue_scripts_and_styles function (#4ujzx0)
* adjust text for advertisement setting





## 1.0.3 (2020-04-20)


### fix

* folder tree not loading in page builders like Elementor and Divi Builder (#4rknyh)





## 1.0.2 (2020-04-16)


### fix

* scripts are not loaded correctly for real-utils package (#4pgz4m)





## 1.0.1 (2020-04-16)


### chore

* rename real-ad to real-utils (#4jpg5f)


### ci

* run package jobs also on devops changes


### docs

* broken links in developer documentation (#5yg1cf)


### fix

* do not show already installed plugins in welcome page (#4pm5e2)
