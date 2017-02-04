<?php
/**
 * Plugin Name: City Weather Report
 * Description: Show weather report for specified city, or users geo-location
 * Version: 1.0
 * Author: Benjamin Mercer
 *
 **/

/**
 * API endpoint example
 * http://api.wunderground.com/api/8abaf1d660df37ed/conditions/q/CA/San_Francisco.json
 **/

// Exit if Accessed Directly
if(!defined('ABSPATH')){
	exit;
}

// Load Scripts
require_once(plugin_dir_path(__FILE__) . '/includes/city-weather-report-scripts.php');

// Load GeoPlugin
require_once(plugin_dir_path(__FILE__) . '/includes/geoplugin.class.php');

// Load Class
require_once(plugin_dir_path(__FILE__) . '/includes/city-weather-report-class.php');

// Register Widget
function register_city_weather_report() {
	register_widget('City_Weather_Report_Widget');
}

// Add Action
add_action('widgets_init', 'register_city_weather_report');