<?php

/**
 * @file
 * Contains \Varbase\composer\ScriptHandler.
 */

namespace Varbase\composer;

use Symfony\Component\Filesystem\Filesystem;

class ScriptHandler {

  /**
   * Get the Drupal root directory.
   *
   * @param type $project_root
   * @return type
   */
  protected static function getDrupalRoot($project_root) {
    return $project_root . '/docroot';
  }

  /**
   * Post Drupal Scaffold Procedure.
   *
   * @param \Composer\EventDispatcher\Event $event
   *   The script event.
   */
  public static function postDrupalScaffoldProcedure(\Composer\EventDispatcher\Event $event) {

    $fs = new Filesystem();
    $root = static::getDrupalRoot(getcwd());

    if ($fs->exists($root . '/profiles/varbase/src/assets/robots-staging.txt')) {
      //Create staging robots file
      copy($root . '/profiles/varbase/src/assets/robots-staging.txt', $root . '/robots-staging.txt');
    }

    if ($fs->exists($root . '/.htaccess')) {
      //Alter .htaccess file
      $htaccess_path = $root . '/.htaccess';
      $htaccess_lines = file($htaccess_path);
      $lines = [];
      foreach ($htaccess_lines as $line) {
        $lines[] = $line;
        if (strpos($line, "RewriteEngine on") !== FALSE
          && $fs->exists($root . '/profiles/varbase/src/assets/htaccess_extra')) {
          $lines = array_merge($lines, file($root . '/profiles/varbase/src/assets/htaccess_extra'));
        }
      }
      file_put_contents($htaccess_path, $lines);
    }
  }
}