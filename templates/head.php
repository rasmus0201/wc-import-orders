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
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

		<meta name="apple-mobile-web-app-title" content="<?php echo $global['project_name']; ?>">
		<meta name="application-name" content="<?php echo $global['project_name']; ?>">
		<meta name="description" content="<?php echo $global['project_name']; ?>">
		<meta name="robots" content="noodp">
		<link rel="canonical" href="<?php echo BASE_URL; ?>">

		<?php /*<!--<link rel="apple-touch-icon" sizes="57x57" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/apple-touch-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="60x60" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/apple-touch-icon-60x60.png">
		<link rel="apple-touch-icon" sizes="72x72" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/apple-touch-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="76x76" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/apple-touch-icon-76x76.png">
		<link rel="apple-touch-icon" sizes="114x114" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/apple-touch-icon-114x114.png">
		<link rel="apple-touch-icon" sizes="120x120" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/apple-touch-icon-120x120.png">
		<link rel="apple-touch-icon" sizes="144x144" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/apple-touch-icon-144x144.png">
		<link rel="apple-touch-icon" sizes="152x152" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/apple-touch-icon-152x152.png">
		<link rel="apple-touch-icon" sizes="180x180" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/apple-touch-icon-180x180.png">
		<link rel="icon" type="image/png" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/favicon-32x32.png" sizes="32x32">
		<link rel="icon" type="image/png" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/favicon-194x194.png" sizes="194x194">
		<link rel="icon" type="image/png" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/favicon-96x96.png" sizes="96x96">
		<link rel="icon" type="image/png" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/android-chrome-192x192.png" sizes="192x192">
		<link rel="icon" type="image/png" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/favicon-16x16.png" sizes="16x16">
		<link rel="manifest" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/manifest.json">
		<link rel="shortcut icon" href="http://jellybeans.dk/wp-content/themes/klasik-child/images/favicon.ico">--> */ ?>

		<link rel="shortlink" href="<?php echo BASE_URL; ?>">

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

		<link rel="stylesheet" href="<?php echo STATIC_URL; ?>css/main.css">


		<title><?php echo $global['site_title']; ?></title>
	</head>
	<body class="<?php echo $body_class; ?>">












