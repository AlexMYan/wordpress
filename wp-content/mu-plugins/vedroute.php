<?php
/*
 * Plugin name: VED ajax
 * Description: Настройки путей ajax запросов для vue (без темы fea-agency работать не будет)
 * Version: 1.0
 * Author: Yanovich Alexandr
 * Text Domain:  vedroute
*/
if (!defined('ABSPATH')) {
	die;
}
define('VED_ROUTE_VERSION', '1.0');
define('VED_ROUTE_FOLDER', "vedroute");
define('VED_ROUTE__PLUGIN_DIR', plugin_dir_path(__FILE__) . VED_ROUTE_FOLDER);

/**
 * autoload class
 */
spl_autoload_register(function ($classname) {
	// Regular
	$class = str_replace('\\', DIRECTORY_SEPARATOR, strtolower($classname));
	$classpath = VED_ROUTE__PLUGIN_DIR . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $class . '.php';

	if (file_exists($classpath)) {

		include_once $classpath;
	}
});

//$classVedSettingsPage = new \Helper\VedRestApi();