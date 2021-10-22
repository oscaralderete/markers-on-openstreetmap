<?php
/*
@author: Oscar Alderete <me@oscaralderete.com>
@website: https://oscaralderete.com
@editor: NetBeans IDE v11.2
*/
class MarkersOnOpenStreetMap {

public static $code = 'MarkersOnOpenStreetMap';
public static $slug = 'markersonopenstreetmap';
public static $ajaxAdminListener = '_process_ajax_request';
public static $dir;
public static $uri;
public static $path;

private static $title = 'Markers On OpenStreetMap';
private static $permission = 'administrator';
private static $icon = 'dashicons-location';
private static $version = '1.0.0';
private static $s = ['result'=>'ERROR', 'msg'=>'Error code 3001'];
private static $scripts = [
	['type'=>'remote', 'src'=>'https://unpkg.com/leaflet@1.7.1/dist/leaflet.js'],
	['type'=>'local', 'src'=>'scripts.js']
];
private static $adminScripts = [
	['type'=>'local', 'src'=>'vue.3.2.20.js', 'folder'=>'admin'],
	['type'=>'local', 'src'=>'oa-toast.js', 'folder'=>'admin'],
	['type'=>'local', 'src'=>'oa-dialogs.js', 'folder'=>'admin'],
	['type'=>'local', 'src'=>'scripts.js', 'folder'=>'admin']
];
private static $styles = [
	['type'=>'remote', 'src'=>'https://unpkg.com/leaflet@1.7.1/dist/leaflet.css'],
	['type'=>'local', 'src'=>'styles.css']
];
private static $adminStyles = [
	['type'=>'local', 'src'=>'oa-loader.css', 'folder'=>'admin'],
	['type'=>'local', 'src'=>'oa-toast.css', 'folder'=>'admin'],
	['type'=>'local', 'src'=>'oa-dialogs.css', 'folder'=>'admin'],
	['type'=>'local', 'src'=>'styles.css', 'folder'=>'admin']
];


//publics
public static function loadScripts(string $zone='public'){
	$handle = self::$code . '_js';
	$order = 1;
	$scripts = $zone == 'public' ? self::$scripts : self::$adminScripts;
	foreach($scripts as $i) {
		if($i['type'] == 'local'){
			$uri = self::$uri . (isset($i['folder']) ? $i['folder'] : 'public') . '/js/' . $i['src'];
			$version = self::$version;
			$onFooter = true;
		}
		else{
			$uri = $i['src'];
			$version = null;
			$onFooter = false;
		}
		wp_register_script($handle . $order, $uri, [], $version, $onFooter);
		wp_enqueue_script($handle . $order);
		$order++;
	}
	//styles
	$styles = $zone == 'public' ? (isset(self::$styles) ? self::$styles : []) : (isset(self::$adminStyles) ? self::$adminStyles : []);
	$handle = self::$code . '_css';
	$order = 1;
	foreach($styles as $i) {
		if($i['type'] == 'local'){
			$uri = self::$uri . (isset($i['folder']) ? $i['folder'] : 'public') . '/css/' . $i['src'];
			$version = self::$version;
			$onFooter = true;
		}
		else{
			$uri = $i['src'];
			$version = null;
			$onFooter = false;
		}
		wp_enqueue_style($handle . $order, $uri, [], $version);
		$order++;
	}
}

public static function getView(string $type = 'public'){
	// default
	$bus = ['$pageData'];
	$rem = [];
	// markers
	if($r = get_option(self::$slug . '_markers')){
		$markers = unserialize($r);
	}
	else{
		$markers = [];
	}
	// map height
	if($r = get_option(self::$slug . '_map_height')){
		$map_height = $r;
	}
	else{
		$map_height = 200;
	}
	// map's initial zoom
	if($r = get_option(self::$slug . '_initial_zoom')){
		$initial_zoom = (int)$r;
	}
	else{
		$initial_zoom = 10;
	}
	switch($type){
		case 'admin':
			$view = 'admin/template/settings';
			//preparing page data
			$rem[] = json_encode([
				'markers' => $markers,
				'ajax_action' => self::$slug . '_process_ajax_request',
				'map_height' => $map_height,
				'initial_zoom' => $initial_zoom,
			]);
			break;
		default:
			$view = 'public/template/openstreetmap';
			//preparing page data
			$rem[] = json_encode([
				'markers' => $markers,
				'marker_uri' => self::$uri . 'public/img/marker.svg',
				'marker_shadow' => self::$uri . 'public/img/marker_shadow.png',
				'map_height' => $map_height,
				'lat' => -12.0459939284752,
				'lng' => -77.0305538177490,
				'initial_zoom' => $initial_zoom
			]);
	}
	return str_replace($bus, $rem, file_get_contents(self::$dir . '/' . $view . '.html'));
}

public static function adminMenu(){
	$t = self::$title;
	add_menu_page($t, $t, self::$permission, self::$slug . '/admin-page', function(){
		echo self::getView('admin');
	}, self::$icon, 6);
}

public static function adminInit(){
	register_setting(self::$slug, self::$slug . '_markers');
	register_setting(self::$slug, self::$slug . '_map_height');
	register_setting(self::$slug, self::$slug . '_initial_zoom');
}

public static function processAjaxRequest(){
	$s = self::$s;
	switch($_POST['type']){
		case 'save_marker':
			$s = self::saveMarkers($_POST['data']);
			break;
		case 'save_initial_zoom':
			$s = self::saveInitialZoom($_POST['data']);
			break;
		case 'save_map_height':
			$s = self::saveMapHeight($_POST['data']);
			break;
		default:
			$s['msg'] = 'Error code 2001';
	}
	sleep(1);
	echo json_encode($s);
	wp_die();
}


//privates
private static function saveMarkers(array $post){
	$s = self::$s;
	$key = 'markers';
	$option = self::$slug . '_' . $key;
	//save/update option
	if(update_option($option, serialize($post[$key]))){
		$s['result'] = 'OK';
		$s['msg'] = 'Marker has been ' . $post['action'] . 'd!';
	}
	else{
		$s['msg'] = 'Error trying to save marker!';
	}
	return $s;
}

private static function saveInitialZoom(array $post){
	$s = self::$s;
	$key = 'initial_zoom';
	$option = self::$slug . '_' . $key;
	// check for changes
	$googleMapKey = get_option($option);
	if($googleMapKey == $post[$key]){
		$s['msg'] = 'Nothing to update because API key hasn\'t changed!';
		return $s;
	}
	// force values when exceeding the valid range
	$post[$key] = (int)$post[$key];
	if($post[$key] > 0 && $post[$key] < 19){
		// everything's ok
		$force = false;
	}
	else{
		$force = true;
		$post[$key] = $post[$key] < 1 ? 1 : 18;
	}
	// save/update option
	if(update_option($option, $post[$key])){
		$s['result'] = 'OK';
		$s['msg'] = 'The initial zoom has been updated!';
		$s[$key] = $post[$key];
	}
	else{
		if($force){
			$s['result'] = 'OK';
			$s['msg'] = 'The initial zoom has been updated!';
			$s[$key] = $post[$key];
		}
		else{
			$s['msg'] = 'Error trying to save the initial zoom!';
		}
	}
	return $s;
}

private static function saveMapHeight(array $post){
	$s = self::$s;
	$key = 'map_height';
	$option = self::$slug . '_' . $key;
	//check for changes
	$map_height = get_option($option);
	if($map_height == $post[$key]){
		$s['msg'] = 'Nothing to update because map height hasn\'t changed!';
		return $s;
	}
	//save/update option
	if(update_option($option, $post[$key])){
		$s['result'] = 'OK';
		$s['msg'] = 'Your map height has been updated!';
	}
	else{
		$s['msg'] = 'Error trying to save map height!';
	}
	return $s;
}

}