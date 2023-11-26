## Description
Install profile generated with Features from exisiting UKD8 simple site install.  Currently, using to rework ukd8_install_profile and create a more stable and reliable build.

## Requirements
*  VS Code
*  uky-web/devcontainer (https://gitlab.com/uky-web/university-web-platform/drupal-8/devcontainer)

## Instructions
1.  `$ mkdir new_name_folder` on local machine.
2.  `$ cd new_name_folder`
3.  `$ git clone git@gitlab.com:uky-web/university-web-platform/drupal-8/devcontainer.git .` - Make sure to name the directory something new each time that you use the devcontainer so that there are no conflicts with other projects using devcontainer.
4.  Open new_name_folder in VS code `$ open new_name_folder -a 'visual studio code'`
5.  Reopen in new container when VS code prompts.  If it does not prompt, go to Remote Explorer and right click (on the drupal container) to reopen in container
6.  Now you should be in the remote drupal container.  `$ cd site/` run `$ composer install` - This should grab drupal as well as the simple site install profile.
7.  Run `$ apt-get mysql-client`
8.  `$ cd web/profiles/custom/`
9.  `$ git clone git@gitlab.com:uky-web/university-web-platform/drupal-8/ukd8-base.git`
10.  Run `$ drush site-install ukd8_base -vvv`
11.  When prompted change localhost from 127.0.0.1 to mysql, all other configuration can stay the same.
