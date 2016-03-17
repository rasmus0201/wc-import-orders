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