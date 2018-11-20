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
  protected static function getDrupalRoot($project_root) {
    return $project_root . '/docroot';
  }

  public static function handleTags(Event $event) {
    print("WIP");
  }

  public static function generate(Event $event) {
    $loader = new JsonLoader(new ArrayLoader());
    $varbaseConfigPath = VarbaseUpdate::getDrupalRoot(getcwd()) . "/profiles/varbase/composer.json";
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

    foreach (glob(VarbaseUpdate::getDrupalRoot(getcwd())."/modules/contrib/*/*.info.yml") as $file) {
      $yaml = Yaml::parse(file_get_contents($file));
      if(isset($yaml["project"]) && isset($yaml["version"]) && $yaml["project"] != "varbase"){
        $composerRepo = "drupal";
        $composerName = $composerRepo . "/" . $yaml["project"];
        $composerVersion = str_replace("8.x-", "", $yaml["version"]);
        if(!isset($varbasePackageRequires[$composerName])){
          $requiredPackages[] = ["name"=> $composerName, "version" => $composerVersion];
        }
      }
    }

    foreach (glob(VarbaseUpdate::getDrupalRoot(getcwd())."/themes/contrib/*/*.info.yml") as $file) {
      $yaml = Yaml::parse(file_get_contents($file));
      if(isset($yaml["project"]) && isset($yaml["version"]) && $yaml["project"] != "varbase"){
        $composerRepo = "drupal";
        $composerName = $composerRepo . "/" . $yaml["project"];
        $composerVersion = str_replace("8.x-", "", $yaml["version"]);
        if(!isset($varbasePackageRequires[$composerName])){
          $requiredPackages[] = ["name"=> $composerName, "version" => $composerVersion];
        }
      }
    }

    foreach ($requiredPackages as $package) {
      if(isset($projectPackageRequires[$package["name"]])){
        $requiredPackageLinks[] = $projectPackageRequires[$package["name"]];
      }else{
        $link = new Link("vardot/varbase-project", $package["name"], new Constraint(">=", $package["version"]), "", "^".$package["version"]);
        $requiredPackageLinks[$package["name"]] = $link;
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
