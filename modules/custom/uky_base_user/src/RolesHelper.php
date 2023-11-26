<?php

namespace Drupal\uky_base_user;

/**
 * Class to store default toles
 */
class RolesHelper {

    const CONTENT_EDITOR = 'content_editor';
    const CONTENT_MANAGER = 'content_manager';
    const SITE_BUILDER = 'site_builder';

    public static function assignModuleRoles($module){
        if ($roles = self::getModuleRoles($module)){
            foreach ($roles as $role => $permissions){
                \user_role_grant_permissions($role, $permissions);
            }
        }
    }

    public static function getModuleRoles($module){
        if (function_exists($module . '_uky_roles_default')){
            return \call_user_func($module . '_uky_roles_default');
        }
    }
}