<?php

// Set scripts to add
function cwr_add_scripts() {
	wp_enqueue_style('cwr-main-style', plugins_url() . '/city-weather-report/css/style.css');
	wp_enqueue_script('cwr-main-script', plugins_url() . '/city-weather-report/js/main.js', array('jquery'));
}

// Add the scripts to wordpress
add_action('wp_enqueue_scripts', 'cwr_add_scripts');