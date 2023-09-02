<?php
/*
 * @version $Id: setup.php 313 2018-02-10 08:52:58Z liangjia $
 -------------------------------------------------------------------------
 dingding - dingding Report&Print plugin for GLPI
 Copyright (C) 2018-2118 by the dingding Development Team.

 https://www.odoo123.com
 -------------------------------------------------------------------------

 LICENSE

 This file is part of dingding.

 dingding is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 dingding is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with dingding. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Init the hooks of the plugins -Needed
 **/
function plugin_init_dingding() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['dingding'] = true;

   Plugin::registerClass('PlugindingdingPreference',
                         array('addtabon' => array('Preference')));

   Plugin::registerClass('PlugindingdingProfile',
                         array('addtabon' => array('Profile')));

   $PLUGIN_HOOKS['change_profile']['dingding'] = array('PlugindingdingProfile','changeprofile');

   if (isset($_SESSION["glpi_plugin_dingding_profile"])
       && $_SESSION["glpi_plugin_dingding_profile"]["dingding"]) {

      $PLUGIN_HOOKS['menu_toadd']['dingding']['tools'] = 'PlugindingdingPreference';

      $PLUGIN_HOOKS['pre_item_purge']['dingding'] = array('Profile' => array('PlugindingdingProfile',
                                                                             'cleanProfiles'));

      $PLUGIN_HOOKS['change_entity']['dingding'] = 'plugin_change_entity_dingding';

      if (isset($_SESSION["glpi_plugin_dingding_loaded"])
          && $_SESSION["glpi_plugin_dingding_loaded"] == 1
          && class_exists('PlugindingdingConfig')) {

         foreach (PlugindingdingConfig::getTypes() as $type) {
            $PLUGIN_HOOKS['item_update']['dingding'][$type]  = 'plugin_item_update_dingding';
            $PLUGIN_HOOKS['item_delete']['dingding'][$type]  = 'plugin_dingding_reload';
            $PLUGIN_HOOKS['item_restore']['dingding'][$type] = 'plugin_dingding_reload';
         }
      }

      if ($_SERVER['PHP_SELF'] == $CFG_GLPI["root_doc"]."/front/central.php"
          && (!isset($_SESSION["glpi_plugin_dingding_loaded"])
              || $_SESSION["glpi_plugin_dingding_loaded"] == 0)
          && isset($_SESSION["glpi_plugin_dingding_preference"])
          && $_SESSION["glpi_plugin_dingding_preference"] == 1) {

            Html::redirect($CFG_GLPI["root_doc"]."/plugins/dingding/index.php");
      }

      if ($_SERVER['PHP_SELF'] == $CFG_GLPI["root_doc"]."/front/logout.php"
          && (isset($_SESSION["glpi_plugin_dingding_loaded"])
          && $_SESSION["glpi_plugin_dingding_loaded"] == 1
          && class_exists('PlugindingdingConfig'))) {

         $config = new PlugindingdingConfig();
         $config->hidedingding();
      }
      // Add specific files to add to the header : javascript or css
      $PLUGIN_HOOKS['add_javascript']['dingding']  = "dtree.js";
      $PLUGIN_HOOKS['add_css']['dingding']         = "dtree.css";
      $PLUGIN_HOOKS['add_javascript']['dingding']  = "functions.js";
      $PLUGIN_HOOKS['add_css']['dingding']         = "style.css";
      $PLUGIN_HOOKS['add_javascript']['dingding']  = "dingding.js";
      $PLUGIN_HOOKS['add_css']['dingding']         = "dingding.css";
   }

   // Config page
   if (Session::haveRight("config", UPDATE) || Session::haveRight("profile", UPDATE)) {
      $PLUGIN_HOOKS['config_page']['dingding'] = 'front/config.form.php';
   }
}


/**
 * Get the name and the version of the plugin - Needed
**/
function plugin_version_dingding() {

   return array('name'           => __('钉钉插件forGLPI', '开源之家'),
                'version'        => '0.0.1',
                'license'        => 'GPLv2+',
                'author'         => '开源之家',
                'homepage'       => 'https://github.com/leangjia/glpi_plugins_dingding',
                'minGlpiVersion' => '0.84'); // For compatibility / no install in version < 0.78
}


function plugin_dingding_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.1', 'lt') || version_compare(GLPI_VERSION, '9.2', 'ge')) {
      echo 'This plugin requires GLPI >= 9.1';
      return false;
   }
   return true;
}


function plugin_dingding_check_config() {
   return true;
}