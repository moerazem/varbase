#!//bin/bash
################################################################################
## Create new vartheme subtheme.
################################################################################
##
## Quick tip on how to use this script command file.
##
## Create new Vartheme sub theme for a project.
## By Bash:
## -----------------------------------------------------------------------------
## cd PROJECT_DIR_NAME/docroot/profiles/varbase/scripts
## bash ./create-new-vartheme.sh "THEME_NAME" "ltr"
##------------------------------------------------------------------------------
##
## For right to left themes.
## By Bash:
## -----------------------------------------------------------------------------
## cd PROJECT_DIR_NAME/docroot/profiles/varbase/scripts
## bash ./create-new-vartheme.sh "THEME_NAME" "rtl"
## -----------------------------------------------------------------------------
##
## To create a new theme in the PROJECT_DIR_NAME/docroot/themes/custom
## By Bash:
## -----------------------------------------------------------------------------
## cd PROJECT_DIR_NAME/docroot/profiles/varbase/scripts
## bash ./create-new-vartheme.sh "THEME_NAME"
## -----------------------------------------------------------------------------
##
################################################################################

# Basic yaml parser.
parse_yaml() {
   local s='[[:space:]]*' w='[a-zA-Z0-9_]*' fs=$(echo @|tr @ '\034')
   sed -ne "s|^\($s\)\($w\)$s:$s\"\(.*\)\"$s\$|\1$fs\2$fs\3|p" \
        -e "s|^\($s\)\($w\)$s:$s\(.*\)$s\$|\1$fs\2$fs\3|p"  $1 |
   awk -F$fs '{
      indent = length($1)/2;
      vname[indent] = $2;
      for (i in vname) {if (i > indent) {delete vname[i]}}
      if (length($3) > 0) {
         vn=""; for (i=0; i<indent; i++) {vn=(vn)(vname[i])("_")}
         printf("%s%s%s=\"%s\"\n", "",vn, $2, $3);
      }
   }'
}

current_path=$(pwd);
drupal_root="$current_path";

if [[ "${drupal_root: -1}" == "/" ]]; then
  drupal_root="${drupal_root::-1}";
fi

if [[ "${drupal_root: -24}" == "profiles/varbase/scripts" ]]; then
  drupal_root="${drupal_root::-24}";
fi

if [[ "${drupal_root: -16}" == "profiles/varbase" ]]; then
  drupal_root="${drupal_root::-16}";
fi

if [[ "${drupal_root: -8}" == "profiles" ]]; then
  drupal_root="${drupal_root::-8}";
fi

if [[ "${drupal_root: -1}" == "/" ]]; then
  drupal_root="${drupal_root::-1}";
fi

echo "Current path: $current_path";
echo "Drupal root: $drupal_root";

# Read scripts.settings.yml file
eval $(parse_yaml $drupal_root/profiles/varbase/scripts/scripts.settings.yml);

# Default theme name.
theme_name=$default_theme_name;

# Grape the theme name argument.
if [ ! -z "$1" ]; then
  arg1="$1";
  if [[ $arg1 =~ ^[A-Za-z][A-Za-z0-9_]*$ ]]; then
    theme_name="$arg1";
  else
    echo "---------------------------------------------------------------------------";
    echo "   Theme name is not a valid theme name!                                   ";
    echo "---------------------------------------------------------------------------";
    exit 1;
  fi
else
  echo "---------------------------------------------------------------------------";
  echo "   Please add the name of your theme!                                      ";
  echo "---------------------------------------------------------------------------";
  exit 1;
fi

# Default direction.
direction=$default_direction;

# Grape the direction argument. only if we have arg #2.
if [ ! -z "$2" ]; then
  arg2="$2";
  if [[ "$arg2" == "rtl" || "$arg2" == "RTL" ]]; then
    direction=$arg2;
  elif [[ "$arg2" == "ltr" || "$arg2" == "LTR" ]]; then
    direction=$arg2;
  else
    echo "---------------------------------------------------------------------------";
    echo "   Direction of language is not valid!                                     ";
    echo "    ltr - for (left to right) languages.                                   ";
    echo "    rtl - for (right to left) languages.                                   ";
    echo "---------------------------------------------------------------------------";
    exit 1;
  fi
fi

# Default themes creation path.
theme_path=$drupal_root/$default_themes_creation_path;
mkdir -p ${theme_path};

# Create the new Vartheme subtheme if we do not have a folder with that name yet.
if [[ ! -d "$theme_path/$theme_name" ]]; then

  # 1. Copy the VARTHEME_BS4_SUBTHEME folder to your custom theme location.
  cp -r ${drupal_root}/themes/contrib/vartheme_bs4/VARTHEME_BS4_SUBTHEME ${theme_path}/${theme_name};

  # 2. Rename VARTHEME_BS4_SUBTHEME.starterkit.yml your_subtheme_name.info.yml
  mv ${theme_path}/${theme_name}/VARTHEME_BS4_SUBTHEME.starterkit.yml ${theme_path}/${theme_name}/VARTHEME_BS4_SUBTHEME.info.yml ;
  mv ${theme_path}/${theme_name}/VARTHEME_BS4_SUBTHEME.info.yml ${theme_path}/${theme_name}/${theme_name}.info.yml ;

  # 3. Rename VARTHEME_BS4_SUBTHEME.libraries.yml your_subtheme_name.libraries.yml
  mv ${theme_path}/${theme_name}/VARTHEME_BS4_SUBTHEME.libraries.yml ${theme_path}/${theme_name}/${theme_name}.libraries.yml ;

  # 4. Rename VARTHEME_BS4_SUBTHEME.theme your_subtheme_name.theme
  mv ${theme_path}/${theme_name}/VARTHEME_BS4_SUBTHEME.theme ${theme_path}/${theme_name}/${theme_name}.theme ;

  # 5. Rename VARTHEME_BS4_SUBTHEME.settings.yml
  mv ${theme_path}/${theme_name}/config/install/VARTHEME_BS4_SUBTHEME.settings.yml ${theme_path}/${theme_name}/config/install/${theme_name}.settings.yml ;

  # 6. Rename VARTHEME_BS4_SUBTHEME.schema.yml
  mv ${theme_path}/${theme_name}/config/schema/VARTHEME_BS4_SUBTHEME.schema.yml ${theme_path}/${theme_name}/config/schema/${theme_name}.schema.yml ;

  # 6. Rename VARTHEME_BS4_SUBTHEME.base.scss and VARTHEME_BS4_SUBTHEME.base.css
  mv ${theme_path}/${theme_name}/scss/base/VARTHEME_BS4_SUBTHEME.base.scss ${theme_path}/${theme_name}/scss/base/${theme_name}.base.scss ;
  mv ${theme_path}/${theme_name}/css/base/VARTHEME_BS4_SUBTHEME.base.css ${theme_path}/${theme_name}/css/base/${theme_name}.base.css ;

  # 6. Rename VARTHEME_BS4_SUBTHEME.base.scss and VARTHEME_BS4_SUBTHEME.base.css
  mv ${theme_path}/${theme_name}/scss/rtl/base/VARTHEME_BS4_SUBTHEME-rtl.base.scss ${theme_path}/${theme_name}/scss/rtl/base/${theme_name}.base.scss ;
  mv ${theme_path}/${theme_name}/css/rtl/base/VARTHEME_BS4_SUBTHEME-rtl.base.css ${theme_path}/${theme_name}/css/rtl/base/${theme_name}.base.css ;

  # 7. Replace all VARTHEME_BS4_SUBTHEME with the machine name of your theme.
  grep -rl 'VARTHEME_BS4_SUBTHEME' ${theme_path}/${theme_name} | xargs sed -i '' "s/VARTHEME_BS4_SUBTHEME/${theme_name}/g" ;

  if [[ "$direction" == "ltr" || "$direction" == "LTR" ]]; then
    rm -rf ${theme_path}/${theme_name}/scss/rtl
    rm -rf ${theme_path}/${theme_name}/css/rtl
  fi;
  # 8. install needed libraries
  cd ${theme_path}/${theme_name};
  npm install;

  generated_datetime="$(date '+%Y/%m/%d - %H:%M:%S')";
  generated_log=" Generated by -- create-new-vartheme ${theme_name} ${direction} ${theme_path} -- on ${generated_datetime}";
  echo "${generated_log}"  >> ${theme_path}/${theme_name}/README.md;

  echo "---------------------------------------------------------------------------";
  echo "   The new Vartheme bs4 subtheme were created at \"${theme_path}/${theme_name} :)\" ";
  echo "---------------------------------------------------------------------------";
  exit 0;

else
  echo "---------------------------------------------------------------------------";
  echo "   The folder \"${theme_path}/${theme_name}\" is already in the site!";
  echo "---------------------------------------------------------------------------";
  exit 1;
fi
