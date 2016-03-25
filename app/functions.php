<?php

if (!defined('BASE_URL')) {
	header('HTTP/1.0 404 not found');
	echo '<h1>404 - Page not found.</h1>';
	exit;
}

function message($str, $type = 'success', $dismissable = true){

	if ($dismissable) {
		switch ($type) {
			case 'success':
				$return = '<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Luk"><span aria-hidden="true">&times;</span></button>'.$str.'</div>';
				break;

			case 'info':
				$return = '<div class="alert alert-info alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Luk"><span aria-hidden="true">&times;</span></button>'.$str.'</div>';
				break;

			case 'warning':
				$return = '<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Luk"><span aria-hidden="true">&times;</span></button>'.$str.'</div>';
				break;

			case 'danger':
				$return = '<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Luk"><span aria-hidden="true">&times;</span></button>'.$str.'</div>';
				break;
		}
		return $return;
	}

	switch ($type) {
		case 'success':
			$return = '<div class="alert alert-success" role="alert">'.$str.'</div>';
			break;

		case 'info':
			$return = '<div class="alert alert-info" role="alert">'.$str.'</div>';
			break;

		case 'warning':
			$return = '<div class="alert alert-warning" role="alert">'.$str.'</div>';
			break;

		case 'danger':
			$return = '<div class="alert alert-danger" role="alert">'.$str.'</div>';
			break;
	}

	return $return;
}

function niceurl($str, $delimiter = '-') {
	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$clean = explode('.', $clean)[0];
	$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

	return $clean;
}

function change_settings($next_invoice, $last_pull_date, $base_url, $base_path){
	global $db, $db_settings;
	if (empty($next_invoice) || empty($last_pull_date) || empty($base_url) || empty($base_path)) {
		$_SESSION['form_error'] = true;
		
		return message('Alle felter skal udfyldes.', 'danger');
	} else if (!is_numeric($next_invoice)) {
		$_SESSION['form_error'] = true;

		return message('"next_invoice" skal være et tal.', 'danger');
	}

	$next_invoice = intval($next_invoice);

	//Not nessecary to update and make a db call.
	if ($next_invoice == $db_settings['next_invoice'] && $last_pull_date == $db_settings['last_pull_date'] && $base_url == $db_settings['base_url'] && $base_path == $db_settings['base_path']) {
		return true;
	}

	if ($next_invoice != $db_settings['next_invoice']) {
		$sth = $db->prepare("UPDATE settings SET setting_value = :value WHERE setting_name = 'next_invoice' ");
		$sth->bindParam(':value', $next_invoice);
		$result = $sth->execute();
		if (!$result){
			return message('Noget gik galt.', 'danger');
		}
		$db_settings['next_invoice'] = $next_invoice;
	}

	if ($last_pull_date != $db_settings['last_pull_date']) {
		$sth = $db->prepare("UPDATE settings SET setting_value = :value WHERE setting_name = 'last_pull_date' ");
		$sth->bindParam(':value', $last_pull_date);
		$result = $sth->execute();
		if (!$result){
			return message('Noget gik galt.', 'danger');
		}
		$db_settings['last_pull_date'] = $last_pull_date;
	}

	if ($base_url != $db_settings['base_url']) {
		$sth = $db->prepare("UPDATE settings SET setting_value = :value WHERE setting_name = 'base_url' ");
		$sth->bindParam(':value', $base_url);
		$result = $sth->execute();
		if (!$result){
			return message('Noget gik galt.', 'danger');
		}
		$db_settings['base_url'] = $base_url;
	}

	if ($base_path != $db_settings['base_path']) {
		$sth = $db->prepare("UPDATE settings SET setting_value = :value WHERE setting_name = 'base_path' ");
		$sth->bindParam(':value', $base_path);
		$result = $sth->execute();
		if (!$result){
			return message('Noget gik galt.', 'danger');
		}
		$db_settings['base_path'] = $base_path;
	}

	return true;
}

function change_site_details($site_id, $name, $old_url, $new_url, $ck, $cs){
	global $db;

	if (empty($site_id)) {
		return message('Noget gik galt.', 'danger');
	}

	if(!strstr($new_url, 'http://') && !strstr($new_url, 'https://')){
		$url = 'http://'.$new_url;
	}

	$new_url = rtrim($new_url, '/');

	if ($old_url != $new_url) {
		$sth = $db->prepare("SELECT id,name,url FROM sites WHERE url = :url LIMIT 1");
		$sth->bindParam(':url', $new_url);
		$sth->execute();

		$site = $sth->fetch(PDO::FETCH_ASSOC);

		if (!empty($site) && $site !== false) {
			return message('En side med denne url eksisterer allerede. Du kan redigere den her: <a href="?site_id='.$site['id'].'&action=edit">'.$site['name'].'</a>.', 'danger');
		}
	}

	if ( empty($name) && empty($new_url) && empty($ck) && empty($cs) ) {
		return true;
	}
	
	$sql = "UPDATE IGNORE sites SET ";
	if (!empty($name)) {
		$sql .= 'name = :name, ';
	}
	if (!empty($new_url)) {
		$sql .= 'url = :url, ';
	}
	if (!empty($ck)) {
		$sql .= 'consumer_key = :ck, ';
	}
	if (!empty($cs)) {
		$sql .= 'consumer_secret = :cs, ';
	}

	$sql = rtrim($sql, ' ,');

	$sql .= " WHERE id = :site_id";

	$sth = $db->prepare($sql);
	$sth->bindParam(':site_id', $site_id);

	if (!empty($name)) {
		$sth->bindParam(':name', $name);
	}
	if (!empty($new_url)) {
		$sth->bindParam(':url', $new_url);
	}
	if (!empty($ck)) {
		$sth->bindParam(':ck', $ck);
	}
	if (!empty($cs)) {
		$sth->bindParam(':cs', $cs);
	}

	$result = $sth->execute();

	if ($result) {
		return true;
	}

	return message('Noget gik galt.', 'danger');
}

function add_site($name, $url, $ck, $cs){
	global $db;
	if ( empty($name) || empty($url) || empty($ck) || empty($cs) ) {
		$_SESSION['form_error'] = true;
		
		return message('Alle felter skal udfyldes.', 'danger');
	}

	if(!strstr($url, 'http://') && !strstr($url, 'https://')){
		$url = 'http://'.$url;
	}

	$url = rtrim($url, '/');

	$sth = $db->prepare("SELECT id,name,url FROM sites WHERE url = :url LIMIT 1");
	$sth->bindParam(':url', $url);
	$sth->execute();

	$site = $sth->fetch(PDO::FETCH_ASSOC);

	if (!empty($site) && $site !== false) {
		return message('En side med denne url eksisterer allerede. Du kan redigere den her: <a href="?site_id='.$site['id'].'&action=edit">'.$site['name'].'</a>', 'danger');
	}

	$sth = $db->prepare("INSERT IGNORE INTO sites SET name = :name, url = :url, consumer_key = :ck, consumer_secret = :cs");
	$sth->bindParam(':name', $name);
	$sth->bindParam(':url', $url);
	$sth->bindParam(':ck', $ck);
	$sth->bindParam(':cs', $cs);

	$result = $sth->execute();

	if ($result) {
		$_SESSION['sites_count'] = $_SESSION['sites_count'] + 1;
		return true;
	}

	return message('Noget gik galt.', 'danger');
}

function update_setting($key, $value){
	global $db;

	$sth = $db->prepare("UPDATE `settings` SET `setting_value` = :value WHERE `setting_name` = :key");
	$sth->bindParam(':value', $value);
	$sth->bindParam(':key', $key);
	
	if ($sth->execute()) {
		return true;
	}

	return false;
}

function get_setting($setting){
	global $db;

	$sth = $db->prepare("SELECT * FROM `settings` WHERE `setting_name` = :name LIMIT 1");
	$sth->bindParam(':name', $setting);
	$sth->execute();
	$res = $sth->fetch(PDO::FETCH_ASSOC);
	return $res['setting_value'];
}

function get_sites(){
	global $db;

	$sth = $db->prepare("SELECT * FROM sites");
	$sth->execute();
	$results = $sth->fetchAll(PDO::FETCH_ASSOC);

	$sites = [];

	foreach ($results as $result => $value) {
		$sites[$value['id']] = $value;
	}

	return $sites;
}

function get_settings(){
	global $db;

	$sth = $db->prepare("SELECT * FROM settings");
	$sth->execute();
	$results = $sth->fetchAll(PDO::FETCH_ASSOC);

	$settings = [];

	foreach ($results as $result => $value) {
		$settings[$value['name']] = $value;
	}

	return $settings;
}

function array_assoc_reverse(array $arr){
	return array_combine( array_reverse(array_keys( $arr )), array_reverse( array_values( $arr ) ) );
}

function pred($arr){
	echo '<pre>';
	var_dump($arr);
	echo '</pre>';
}