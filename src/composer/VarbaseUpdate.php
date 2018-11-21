<?php

namespace Varbase\composer;

use Composer\Semver\Constraint\Constraint;
use Composer\Package\Loader\JsonLoader;
use Composer\Package\Loader\ArrayLoader;
use Composer\Package\Dumper\ArrayDumper;
use Composer\Package\Link;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Composer\EventDispatcher\Event;
use Composer\Json\JsonFile;
/**
 * Varbase Composer Script Handler.
 */
class VarbaseUpdate {

  /**
   * Get the Drupal root directory.
   *
   * @param string $project_root
   *    Project root.
   *
   * @return string
   *    Drupal root path.
   */
  protected static function getDrupalRoot($project_root, $rootPath = "docroot") {
    return $project_root . '/' . $rootPath;
  }

  protected static function getPaths(Event $event) {
    $paths = [];
    $composer = $event->getComposer();
    $projectExtras = $event->getComposer()->getPackage()->getExtra();;

    $paths["rootPath"] = "docroot";
    if(isset($projectExtras["install-path"])){
      $paths["rootPath"] = $projectExtras["install-path"];
    }
    $paths["contribModulesPath"] = VarbaseUpdate::getDrupalRoot(getcwd(), $paths["rootPath"]) . "/modules/contrib/";
    $paths["customModulesPath"] = VarbaseUpdate::getDrupalRoot(getcwd(), $paths["rootPath"]) . "/modules/custom/";
    $paths["contribThemesPath"] = VarbaseUpdate::getDrupalRoot(getcwd(), $paths["rootPath"]) . "/themes/contrib/";
    $paths["customThemesPath"] = VarbaseUpdate::getDrupalRoot(getcwd(), $paths["rootPath"]) . "/themes/custom/";
    $paths["librariesPath"] = VarbaseUpdate::getDrupalRoot(getcwd(), $paths["rootPath"]) . "/libraries/";
    $paths["profilesPath"] = VarbaseUpdate::getDrupalRoot(getcwd(), $paths["rootPath"]) . "/profiles/";

    if(isset($projectExtras["installer-paths"])){
      foreach($projectExtras["installer-paths"] as $path => $types){
        foreach($types as $type){
          if($type == "type:drupal-module"){
            $typePath = preg_replace('/\{\$.*\}$/', "", $path);
            $paths["contribModulesPath"] = VarbaseUpdate::getDrupalRoot(getcwd(), "") . $typePath;
            continue;
          }
          if($type == "type:drupal-custom-module"){
            $typePath = preg_replace('/\{\$.*\}$/', "", $path);
            $paths["customModulesPath"] = VarbaseUpdate::getDrupalRoot(getcwd(), "") . $typePath;
            continue;
          }
          if($type == "type:drupal-theme"){
            $typePath = preg_replace('/\{\$.*\}$/', "", $path);
            $paths["contribThemesPath"] = VarbaseUpdate::getDrupalRoot(getcwd(), "") . $typePath;
            continue;
          }
          if($type == "type:drupal-custom-theme"){
            $typePath = preg_replace('/\{\$.*\}$/', "", $path);
            $paths["customThemesPath"] = VarbaseUpdate::getDrupalRoot(getcwd(), "") . $typePath;
            continue;
          }
          if($type == "type:drupal-profile"){
            $typePath = preg_replace('/\{\$.*\}$/', "", $path);
            $paths["profilesPath"] = VarbaseUpdate::getDrupalRoot(getcwd(), "") . $typePath;
            continue;
          }
          if($type == "type:drupal-library" || $type == "type:bower-asset" || $type == "type:npm-asset" ){
            $typePath = preg_replace('/\{\$.*\}$/', "", $path);
            $paths["librariesPath"] = VarbaseUpdate::getDrupalRoot(getcwd(), "") . $typePath;
            continue;
          }
        }
      }
    }

    return $paths;
  }

  public static function handleTags(Event $event) {

    $loader = new JsonLoader(new ArrayLoader());
    $varbaseConfigPath = VarbaseUpdate::getDrupalRoot(getcwd()) . "/profiles/varbase/composer.json";
    $varbaseConfig = JsonFile::parseJson(file_get_contents($varbaseConfigPath), $varbaseConfigPath);
    if(!isset($varbaseConfig['version'])){
      $varbaseConfig['version'] = "6.2.0";
    }
    $varbaseConfig = JsonFile::encode($varbaseConfig);
    $varbasePackage = $loader->load($varbaseConfig);
    $varbasePackageRequires = $varbasePackage->getRequires();

    $paths = VarbaseUpdate::getPaths($event);

    $modulePath = $paths["contribModulesPath"];
    $themePath = $paths["contribThemesPath"];


    foreach ($varbasePackageRequires as $name => $packageLink) {
      $version = $packageLink->getPrettyConstraint();
      $projectName = preg_replace('/.*\//', "", $name);
      if(preg_match('/\#/', $version)){
        $commitId = preg_replace('/.*\#/', "", $version);
        $result = [];
        print $projectName . ": \n";
        if(file_exists($modulePath . $projectName)){
          exec('cd ' . $modulePath . $projectName . '; git checkout ' . $commitId, $result);
          foreach ($result as $line) {
              print($line . "\n");
          }
        }else if(file_exists($themePath . $projectName)){
          exec('cd ' . $themePath . $projectName . '; git checkout ' . $commitId, $result);
          foreach ($result as $line) {
              print($line . "\n");
          }
        }
      }
    }
  }

  public static function generate(Event $event) {
    $paths = VarbaseUpdate::getPaths($event);
    $loader = new JsonLoader(new ArrayLoader());
    $varbaseConfigPath = $paths['profilesPath'] . "varbase/composer.json";
    $varbaseConfig = JsonFile::parseJson(file_get_contents($varbaseConfigPath), $varbaseConfigPath);
    if(!isset($varbaseConfig['version'])){
      $varbaseConfig['version'] = "6.2.0";
    }
    $varbaseConfig = JsonFile::encode($varbaseConfig);
    $varbasePackage = $loader->load($varbaseConfig);

    $composer = $event->getComposer();
    $projectPackage = $event->getComposer()->getPackage();
    $io = $event->getIO();

    $varbasePackageRequires = $varbasePackage->getRequires();
    $projectPackageRequires = $projectPackage->getRequires();
    $varbaseLink = $projectPackageRequires["vardot/varbase"];
    $requiredPackages = [];
    $requiredPackageLinks = [$varbaseLink];

    foreach (glob($paths['contribModulesPath'] . "*/*.info.yml") as $file) {
      $yaml = Yaml::parse(file_get_contents($file));
      if(isset($yaml["project"]) && isset($yaml["version"]) && $yaml["project"] != "varbase"){
        $composerRepo = "drupal";
        $composerName = $composerRepo . "/" . $yaml["project"];
        $composerVersion = str_replace("8.x-", "", $yaml["version"]);
        if(!isset($varbasePackageRequires[$composerName])){
          $requiredPackages[$composerName] = ["name"=> $composerName, "version" => $composerVersion];
        }
      }
    }

    foreach (glob($paths['contribThemesPath'] . "*/*.info.yml") as $file) {
      $yaml = Yaml::parse(file_get_contents($file));
      if(isset($yaml["project"]) && isset($yaml["version"]) && $yaml["project"] != "varbase"){
        $composerRepo = "drupal";
        $composerName = $composerRepo . "/" . $yaml["project"];
        $composerVersion = str_replace("8.x-", "", $yaml["version"]);
        if(!isset($varbasePackageRequires[$composerName])){
          $requiredPackages[$composerName] = ["name"=> $composerName, "version" => $composerVersion];
        }
      }
    }

    foreach (glob($paths['contribModulesPath'] . "*/composer.json") as $file) {
      $pluginConfig = JsonFile::parseJson(file_get_contents($file), $file);
      if(!isset($pluginConfig['version'])){
        $pluginConfig['version'] = "6.2.0";
      }
      $pluginConfig = JsonFile::encode($pluginConfig);
      $pluginPackage = $loader->load($pluginConfig);
      $pluginPackageRequires = $pluginPackage->getRequires();

      foreach ($requiredPackages as $name => $package) {
        if(isset($pluginPackageRequires[$name])){
          unset($requiredPackages[$name]);
        }
      }
    }

    foreach (glob($paths['contribThemesPath'] . "*/composer.json") as $file) {
      $pluginConfig = JsonFile::parseJson(file_get_contents($file), $file);
      if(!isset($pluginConfig['version'])){
        $pluginConfig['version'] = "6.2.0";
      }
      $pluginConfig = JsonFile::encode($pluginConfig);
      $pluginPackage = $loader->load($pluginConfig);
      $pluginPackageRequires = $pluginPackage->getRequires();

      foreach ($requiredPackages as $name => $package) {
        if(isset($pluginPackageRequires[$name])){
          unset($requiredPackages[$name]);
        }
      }
    }

    foreach ($requiredPackages as $name => $package) {
      if(isset($projectPackageRequires[$name])){
        $requiredPackageLinks[] = $projectPackageRequires[$name];
      }else{
        $link = new Link("vardot/varbase-project", $package["name"], new Constraint(">=", $package["version"]), "", "^".$package["version"]);
        $requiredPackageLinks[$name] = $link;
      }
    }

    foreach ($projectPackageRequires as $projectName => $projectPackageLink) {
      if(!isset($varbasePackageRequires[$projectName]) && !isset($requiredPackageLinks[$projectName])){
        $requiredPackageLinks[] = $projectPackageLink;
      }
    }

    $projectPackage->setRequires($requiredPackageLinks);
    $dumper = new ArrayDumper();
    $projectConfig = JsonFile::encode($dumper->dump($projectPackage));
    print_r($projectConfig);
  }
}
