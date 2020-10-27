<?php

define('PLUGIN_ADVBALANCER_VERSION', '1.0');
define('PLUGIN_ADVBALANCER_NAME', 'advbalancer');
/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_advbalancer() {
   global $PLUGIN_HOOKS;

   $pluginClasses = [
       PluginAdvbalancerBalancer::class,
       PluginAdvbalancerConfigs::class,
       PluginAdvbalancerEvent::class,
       PluginAdvbalancerHelper::class
   ];

    foreach ($pluginClasses as $class) {
        Plugin::registerClass($class);
   }


   $PLUGIN_HOOKS['csrf_compliant'][PLUGIN_ADVBALANCER_NAME] = true;
   $PLUGIN_HOOKS['config_page'][PLUGIN_ADVBALANCER_NAME] = 'front/config.php';

   $PLUGIN_HOOKS['add_javascript'][PLUGIN_ADVBALANCER_NAME] = [
       'js/vue.js',
       'js/advbalancer.js'
   ];
   $PLUGIN_HOOKS['add_css'][PLUGIN_ADVBALANCER_NAME] = 'advbalancer.css';

   $PLUGIN_HOOKS['item_add'][PLUGIN_ADVBALANCER_NAME] = [
       Ticket::class => 'plugin_advbalancer_item_add_ticket'
   ];
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_advbalancer() {
   return [
      'name'           => PLUGIN_ADVBALANCER_NAME,
      'version'        => PLUGIN_ADVBALANCER_VERSION,
      'author'         => 'Roman Gonyukov',
      'license'        => '',
      'homepage'       => '',
      'requirements'   => [
         'glpi' => [
            'min' => '9.2',
         ]
      ]
   ];
}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_advbalancer_check_prerequisites() {

   //Version check is not done by core in GLPI < 9.2 but has to be delegated to core in GLPI >= 9.2.
   $version = preg_replace('/^((\d+\.?)+).*$/', '$1', GLPI_VERSION);
   if (version_compare($version, '9.2', '<')) {
      echo "This plugin requires GLPI >= 9.2";
      return false;
   }
   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_advbalancer_check_config($verbose = false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      echo __('Installed / not configured', PLUGIN_ADVBALANCER_NAME);
   }
   return false;
}
