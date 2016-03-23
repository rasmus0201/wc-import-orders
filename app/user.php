<?php

if (!defined('BASE_URL')) {
	header('HTTP/1.0 404 not found');
	echo '<h1>404 - Page not found.</h1>';
	exit;
}

function login($email, $password){
	global $db;
	$bcrypt = new Bcrypt(12);

	//$hash = $bcrypt->hash($password);
	$sth = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
	$sth->bindParam(':email', $email);
	$sth->execute();
	$result = $sth->fetch(PDO::FETCH_ASSOC);
	$user_id = $result['id'];
	$user_email = $result['email'];
	$user_name = $result['name'];
	$user_password = $result['password'];
	$user_role = $result['role'];

	$isGood = $bcrypt->verify($password, $user_password);

	if ($isGood) {
		$sth_1 = $db->prepare("SELECT COUNT(*) FROM orders");
		$sth_2 = $db->prepare("SELECT COUNT(*) FROM invoices");
		$sth_3 = $db->prepare("SELECT COUNT(*) FROM sites");

		$result_1 = $sth_1->execute();
		$orders_count = $sth_1->fetchColumn();

		$result_2 = $sth_2->execute();
		$invoices_count = $sth_2->fetchColumn();
		
		$result_3 = $sth_3->execute();
		$sites_count = $sth_3->fetchColumn();

		$_SESSION['loggedin'] = true;
		$_SESSION['user_id'] = $user_id;
		$_SESSION['user_email'] = $user_email;
		$_SESSION['user_name'] = $user_name;
		$_SESSION['user_role'] = $user_role;

		$_SESSION['orders_count'] = $orders_count;
		$_SESSION['invoices_count'] = $invoices_count;
		$_SESSION['sites_count'] = $sites_count;
		return true;
	}

	$_SESSION['loggedin'] = false;

	return false;
}

function logout(){
	if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
		header('Location: '.BASE_URL);
		exit;
	}
	unset($_SESSION['loggedin']);
	unset($_SESSION['user_id']);
	unset($_SESSION['user_email']);
	unset($_SESSION['user_name']);
	unset($_SESSION['user_role']);

	unset($_SESSION['orders_count']);
	unset($_SESSION['invoices_count']);
	unset($_SESSION['sites_count']);
	session_destroy();

	header('Location: '.BASE_URL);
	exit;
}

function change_user_details($email, $name){
	global $db;
	if (empty($email) || empty($name)) {
		if (empty($email)) {
			$_SESSION['user_email_error'] = true;
		}
		if (empty($name)) {
			$_SESSION['user_name_error'] = true;
		}
		
		return message('Alle felter skal udfyldes.', 'danger');
	} else if (strlen($name) > 100) {
		return message('"Navn" må ikke overstige 100 tegn.', 'danger');
	} else if (strlen($email) > 100) {
		return message('"E-mail" må ikke overstige 100 tegn.', 'danger');
	} else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return message('"E-mail" skal være en rigtigt e-mail.', 'danger');
	}

	//Not nessecary to update and make a db call.
	if ($email == $_SESSION['user_email'] && $name == $_SESSION['user_name']) {
		return true;
	}

	$sth = $db->prepare("UPDATE users SET email = :email, name = :name WHERE id = :user_id AND email = :email");
	$sth->bindParam(':email', $email);
	$sth->bindParam(':name', $name);
	$sth->bindParam(':user_id', $_SESSION['user_id']);

	$result = $sth->execute();

	$_SESSION['user_name'] = $name;
	$_SESSION['user_email'] = $email;

	if ($result) {
		return true;
	}

	return message('Noget gik galt.', 'danger');
}

function change_user_password($password, $password_again){
	global $db;
	if (empty($password) || empty($password_again)) {
		$_SESSION['user_password_error'] = true;
		return message('Alle felter skal udfyldes.', 'danger');
	} else if ($password_again != $password) {
		$_SESSION['user_password_error'] = true;
		return message('Passwords skal være ens.', 'danger');
	} else if (strlen($password) > 50) {
		$_SESSION['user_password_error'] = true;
		return message('"Password" må ikke overstige 50 tegn.', 'danger');
	} else if (strlen($password) < 8) {
		$_SESSION['user_password_error'] = true;
		return message('"Password" skal være minimum 8 tegn.', 'danger');
	}

	$bcrypt = new Bcrypt(12);

	$password = $bcrypt->hash($password);

	$sth = $db->prepare("UPDATE users SET password = :password WHERE id = :user_id AND email = :email");
	$sth->bindParam(':password', $password);
	$sth->bindParam(':user_id', $_SESSION['user_id']);
	$sth->bindParam(':email', $_SESSION['user_email']);

	$result = $sth->execute();

	if ($result) {
		return true;
	}

	return message('Noget gik galt.', 'danger');
}

class Bcrypt {
	private $rounds;
	private $randomState;

	public function __construct($rounds = 12) {
		if(CRYPT_BLOWFISH != 1) {
			throw new Exception("bcrypt not supported in this installation. See http://php.net/crypt");
		}

		$this->rounds = $rounds;
	}

	public function hash($input) {
		$hash = crypt($input, $this->getSalt());

		if(strlen($hash) > 13)
		  return $hash;

		return false;
	}

	public function verify($input, $existingHash) {
		$hash = crypt($input, $existingHash);

		return $hash === $existingHash;
	}

	private function getSalt() {
		$salt = sprintf('$2a$%02d$', $this->rounds);

		$bytes = $this->getRandomBytes(16);

		$salt .= $this->encodeBytes($bytes);

		return $salt;
	}

	private function getRandomBytes($count) {
		$bytes = '';

		if(function_exists('openssl_random_pseudo_bytes') && (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')) { // OpenSSL is slow on Windows
			$bytes = openssl_random_pseudo_bytes($count);
		}

		if($bytes === '' && is_readable('/dev/urandom') &&($hRand = @fopen('/dev/urandom', 'rb')) !== FALSE) {
			$bytes = fread($hRand, $count);
			fclose($hRand);
		}

		if(strlen($bytes) < $count) {
			$bytes = '';

			if($this->randomState === null) {
				$this->randomState = microtime();
				if(function_exists('getmypid')) {
					$this->randomState .= getmypid();
				}
			}

			for($i = 0; $i < $count; $i += 16) {
				$this->randomState = md5(microtime() . $this->randomState);

				if (PHP_VERSION >= '5') {
					$bytes .= md5($this->randomState, true);
				} else {
					$bytes .= pack('H*', md5($this->randomState));
				}
			}

			$bytes = substr($bytes, 0, $count);
		}

		return $bytes;
	}

	private function encodeBytes($input) {
		// The following is code from the PHP Password Hashing Framework
		$itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

		$output = '';
		$i = 0;
		do {
			$c1 = ord($input[$i++]);
			$output .= $itoa64[$c1 >> 2];
			$c1 = ($c1 & 0x03) << 4;
			if ($i >= 16) {
				$output .= $itoa64[$c1];
				break;
			}

			$c2 = ord($input[$i++]);
			$c1 |= $c2 >> 4;
			$output .= $itoa64[$c1];
			$c1 = ($c2 & 0x0f) << 2;

			$c2 = ord($input[$i++]);
			$c1 |= $c2 >> 6;
			$output .= $itoa64[$c1];
			$output .= $itoa64[$c2 & 0x3f];
		} while (1);

		return $output;
	}
}

?>