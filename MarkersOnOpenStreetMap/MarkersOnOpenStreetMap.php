<?php
/**
Plugin Name: Markers On OpenStreetMap
Plugin URI: https://github.com/oscaralderete/markers-on-openstreetmap
Description: Let's put multiple markers on an OpenStreetMap, the free to use interactive maps provider, on your WordPress page. Define all markers you need on its modern reactive admin page (powered by Vue 3). No keys no fees!
Version: 1.0
Author: Oscar Alderete <wordpress@oscaralderete.com>
Author URI: https://oscaralderete.com
*/
if(!defined('WPINC')){
	die;
}

require plugin_dir_path(__FILE__) . 'includes/MarkersOnOpenStreetMap.php';

MarkersOnOpenStreetMap::$dir = __DIR__;
MarkersOnOpenStreetMap::$uri = plugin_dir_url(__FILE__);
MarkersOnOpenStreetMap::$path = plugin_dir_path(__FILE__);

//Add shortcode
add_shortcode(MarkersOnOpenStreetMap::$code, function(){
	return MarkersOnOpenStreetMap::getView();
});

//Register scripts to use 
add_action('wp_enqueue_scripts', function(){
	//load scripts + styles
	MarkersOnOpenStreetMap::loadScripts();
});

//Admin page
add_action('admin_menu', function(){
	MarkersOnOpenStreetMap::adminMenu();
});

//Register plugin settings
add_action('admin_init', function(){
	MarkersOnOpenStreetMap::adminInit();
});

//Register scripts to use 
add_action('admin_enqueue_scripts', function(){
	MarkersOnOpenStreetMap::loadScripts('admin');
});

//Ajax request listener
add_action('wp_ajax_' . MarkersOnOpenStreetMap::$slug . MarkersOnOpenStreetMap::$ajaxAdminListener, function(){
	MarkersOnOpenStreetMap::processAjaxRequest();
});
