<?php

if (!defined('BASE_URL')) {
	header('HTTP/1.0 404 not found');
	exit;
}

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin']) {
	$body_class = 'loggedin';
}else {
	$body_class = 'loggedout';
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.5">
		<meta name="apple-mobile-web-app-title" content="<?php echo $global['project_name']; ?>">
		<meta name="application-name" content="<?php echo $global['project_name']; ?>">
		<meta name="description" content="<?php echo $global['project_name']; ?>">
		<meta name="robots" content="noodp">
		<link rel="canonical" href="<?php echo $global['current_url']; ?>">
		<link rel="shortlink" href="<?php echo $global['current_url']; ?>">

		<link rel="stylesheet" href="<?php echo STATIC_URL; ?>/css/font-awesome.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

		<link rel="stylesheet" href="<?php echo STATIC_URL; ?>/css/main.css">


		<title><?php echo $global['site_title']; ?></title>
	</head>
	<body class="<?php echo $body_class.' '.$global['site_niceurl']; ?>">












