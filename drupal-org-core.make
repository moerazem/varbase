api = 2
core = 7.x
projects[drupal][type] = core
projects[drupal][version] = 7.41

; Patches for core

; Allow install profiles to declare base profiles for Drupal 7 - http://drupal.org/node/2067229
projects[drupal][patch][2067229] = "http://www.drupal.org/files/issues/allow_install_profiles-2067229-62.patch"

; Improve theme registry build performance by 85% - https://www.drupal.org/node/2339447
projects[drupal][patch][2339447] = "http://www.drupal.org/files/issues/D7_improve_theme_registry-2339447-65.patch"

; PHP warnings in PHP 5.6 because of always_populate_raw_post_data ini setting - http://www.drupal.org/node/2456025
projects[drupal][patch][2456025] = "http://www.drupal.org/files/issues/php_warnings_in_php_5_6-2456025-21.patch"

; Varbase core patches (custom patches for varbase installation profile)
projects[drupal][patch][2531762] = "http://www.drupal.org/files/issues/misc_improvement_for-2531762-8.patch"
