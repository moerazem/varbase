<?php
/**
 * Implements hook_drush_command().
 */
function varbase_update_helper_drush_command() {

  $commands['varbase-apply-update'] = [
    'description' => 'Apply varbase optional updates.',
    'aliases' => ['vau'],
    'arguments' => [
       'module' => 'Module name.',
       'number' => 'Update number.',
     ],
     'examples' => [
       'drush vau <module> <number>' => 'Apply the optional update <number> from <module>',
     ],
  ];

  return $commands;
}

/**
 * Drush command logic.
 * drush_[MODULE_NAME]_[COMMAND_NAME]().
 */
function drush_varbase_update_helper_varbase_apply_update($module = "", $number = "") {
  if(!$module || !$number){
    return drush_print(dt('Please provide module name and update number, eg: drush vau <module> <number>'));
  }
  \Drupal::moduleHandler()->invokeAll("varbase_update", [$module, $number]);
}
