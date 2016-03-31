<?php


require '../app/db.php';
require '../app/init.php';


if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
	header('Location: '.BASE_URL);
	exit;
}

check_user_abilities_min_admin(true);

if (isset($_GET['message'])) {
	if (!empty($_GET['message'])) {
		$message = message($_GET['message'], 'warning');
	}
}

if (isset($_POST['add_user'])) {
	$result = add_user($_POST['name'], $_POST['email'], $_POST['role'], $_POST['password'], $_POST['confirm_password']);
	
	if ($result === true) {
		unset($_POST);
		$message = message('Brugeren blev tilføjet succesfuldt.');
	} else {
		$message = $result;
	}

	if (isset($_SESSION['form_error'])) {
		$form_error = true;
		unset($_SESSION['form_error']);
	}
} elseif (isset($_POST['change_user_details']) && $_GET['user_id'] != $_SESSION['user_id'] ) {
	$result_1 = change_user_details($_GET['user_id'], $_POST['email'], $_POST['name'], $_POST['role']);

	if (!empty($_POST['password'])) {
		$result_2 = change_user_password($_GET['user_id'], $_POST['password'], $_POST['confirm_password']);
	} else {
		$result_2 = true;
	}

	if ($result_1 === true && $result_2 === true ) {
		unset($_POST);
		$message = message('Ændringer blev gemt succesfuldt.', 'success');
	} else if ($result_1 && $result_2 === true) {
		$message = $result_1;
	} else if ($result_2 && $result_1 === true) {
		$message = $result_2;
	} else {
		$message = $result_1;
		$message .= $result_2;
	}

	if (isset($_SESSION['user_password_error'])) {
		$user_password_error = true;
		unset($_SESSION['user_password_error']);
	}
	if (isset($_SESSION['user_email_error'])) {
		$user_email_error = true;
		unset($_SESSION['user_email_error']);
	}
	if (isset($_SESSION['user_name_error'])) {
		$user_name_error = true;
		unset($_SESSION['user_name_error']);
	}
	if (isset($_SESSION['user_role_error'])) {
		$user_role_error = true;
		unset($_SESSION['user_role_error']);
	}
}

$is_action = false;


if (isset($_GET['action'])){
	if (!empty($_GET['action'])){
		if ($_GET['action'] != 'edit' && $_GET['action'] != 'delete') {
			header('Location: '.BASE_URL.'/'.$global['current_url']);
			exit;
		}

		if ($_SESSION['user_role'] != 'superadmin') {
			header('Location: '.BASE_URL.'/'.$global['current_url']);
			exit;
		}

		if (isset($_GET['user_id'])) {
			if (!empty($_GET['user_id'])) {
				$user_id = $_GET['user_id'];

				if ($user_id == $_SESSION['user_id']) {
					header('Location: '.BASE_URL.'/'.$global['current_url'].'?message=Din egen profil kan redigeres under "Profil"!');
					exit;
				}

				$user = get_user_by_id($user_id);

				if (!$user) {
					header('Location: '.BASE_URL.'/'.$global['current_url']);
					exit;
				} else if($user['role'] == 'superadmin'){
					header('Location: '.BASE_URL.'/'.$global['current_url'].'?message=En superadministrator kan ikke redigeres!');
					exit;
				}

				if ($_GET['action'] == 'edit') {
					$action = 'edit';
					$is_action = true;
				} else {

					$result = delete_user_by_id($user_id);

					if (!$result) {
						echo message('Noget gik galt.', 'danger');
					} else {
						$_SESSION['users_count'] = $_SESSION['users_count'] - 1;
						header('Location: '.BASE_URL.'/'.$global['current_url']);
						exit;
					}
				}
			}
		} else {
			$is_action = message('En fejl opstod, gå tilbage til Dashboard.', 'danger', false);
		}
	} else {
		$is_action = message('En fejl opstod, gå tilbage til Dashboard.', 'danger', false);
	}
}

$users = get_users();

require '../templates/admin/header.php';

?>

<div class="row">
	<?php require '../templates/admin/sidebar.php'; ?>
	<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		<?php if ($is_action) : ?>
			<?php if ($is_action === true) : ?>
				<?php echo $message; ?>
				<?php if($action == 'edit'): ?>
					<h1 class="page-header"><?php echo $titles['admin/index.php'].' / '.$global['site_title']; ?> / Rediger</h1>
					<form class="form-horizontal" method="post">
					<div class="form-group <?php echo (isset($user_name_error)) ? 'has-error' : '' ;?>">
						<label for="name" class="col-sm-2 control-label">Navn</label>
						<div class="col-sm-10">
							<input required type="text" class="form-control" name="name" id="name" placeholder="Navn" value="<?php echo (isset($_POST['name'])) ? $_POST['name'] : $user['name']; ?>">
						</div>
					</div>
					<div class="form-group <?php echo (isset($user_email_error)) ? 'has-error' : '' ;?>">
						<label for="email" class="col-sm-2 control-label">E-mail</label>
						<div class="col-sm-10">
							<input required type="hidden"  name="old_email" value="<?php echo $user['email']; ?>">
							<input required type="email" class="form-control" name="email" id="email" placeholder="E-mail" value="<?php echo (isset($_POST['email'])) ? $_POST['email'] : $user['email']; ?>">
						</div>
					</div>
					<div class="form-group <?php echo (isset($user_role_error)) ? 'has-error' : '' ;?>">
						<label for="email" class="col-sm-2 control-label">Rolle</label>
						<div class="col-sm-10">
							<select name="role" id="role" class="form-control">
								<?php if (isset($_POST['role'])): ?>
									<option <?php echo ($_POST['role'] == 'viewer') ? 'selected' : '' ; ?> value="viewer">Seer</option>
									<option <?php echo ($_POST['role'] == 'accountant') ? 'selected' : '' ; ?> value="accountant">Bogholder / Revisor</option>
									<?php if(check_user_abilities_superadmin()) : ?>
										<option <?php echo ($_POST['role'] == 'admin') ? 'selected' : '' ; ?> value="admin">Administrator</option>
										<option <?php echo ($_POST['role'] == 'superadmin') ? 'selected' : '' ; ?> value="superadmin">Superadministrator</option>
									<?php endif; ?>
								<?php else: ?>
									<option <?php echo ($user['role'] == 'viewer') ? 'selected' : '' ; ?> value="viewer">Seer</option>
									<option <?php echo ($user['role'] == 'accountant') ? 'selected' : '' ; ?> value="accountant">Bogholder / Revisor</option>
									<?php if(check_user_abilities_superadmin()) : ?>
										<option <?php echo ($user['role'] == 'admin') ? 'selected' : '' ; ?> value="admin">Administrator</option>
										<option <?php echo ($user['role'] == 'superadmin') ? 'selected' : '' ; ?> value="superadmin">Superadministrator</option>
									<?php endif; ?>
								<?php endif; ?>
							</select>
						</div>
					</div>
					<div class="form-group <?php echo (isset($user_password_error)) ? 'has-error' : '' ;?>">
						<label for="password" class="col-sm-2 control-label">Nyt password</label>
						<div class="col-sm-10">
							<input type="password" class="form-control" name="password" id="password" placeholder="Efterlad tomt, hvis ikke det skal skiftes">
						</div>
					</div>
					<div class="form-group <?php echo (isset($user_password_error)) ? 'has-error' : '' ;?>">
						<label for="confirm_password" class="col-sm-2 control-label">Bekræft password</label>
						<div class="col-sm-10">
							<input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Bekræft nyt password">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<button type="submit" class="btn btn-primary" name="change_user_details">Gem</button>
						</div>
					</div>
				</form>
				<?php endif; ?>
			<?php else: ?>
				<?php echo $is_action; ?>
			<?php endif; ?>
		<?php else: ?>
			<?php echo $message; ?>
			<h1 class="page-header"><?php echo $titles['admin/index.php'].' / '.$global['site_title']; ?></h1>
			<div class="col-sm-12">
				<?php if(!empty($users)): ?>
					<div class="table-responsive">
						<table class="table table-striped">
							<thead>
								<tr>
									<th>#</th>
									<th>Navn</th>
									<th>E-mail</th>
									<th>Rolle</th>
									<?php if(check_user_abilities_superadmin()): ?>
										<th></th>
										<th></th>
									<?php endif; ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($users as $user) : ?>
									<?php
										if ($user['role'] == 'superadmin') {
											$role = 'Superadministrator';
										} else if ($user['role'] == 'admin') {
											$role = 'Administrator';
										} else if ($user['role'] == 'accountant') {
											$role = 'Bogholder / Revisor';
										} else if ($user['role'] == 'viewer') {
											$role = 'Seer';
										} else {
											$role = 'Ukendt';
										}
									?>
									<tr>
										<td><?php echo $user['id']; ?></td>
										<td><?php echo $user['name']; ?></td>
										<td><?php echo $user['email']; ?></td>
										<td><?php echo $role; ?></td>
										<?php if(check_user_abilities_superadmin() && $user['role'] != 'superadmin' && $user['id'] != $_SESSION['user_id']): ?>
											<td><a class="btn btn-default" href="?user_id=<?php echo $user['id']; ?>&action=edit" role="button">Rediger</a></td>
											<td><a class="btn btn-danger" href="?user_id=<?php echo $user['id']; ?>&action=delete" role="button">Slet</a></td>
										<?php else: ?>
											<td></td>
											<td></td>
										<?php endif; ?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<?php echo message('Der er ingen brugere endnu.', 'warning', false); ?>
				<?php endif; ?>
			</div>
			<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2">
				<hr>
				<h4 class="page-header">Tilføj bruger</h4>
			</div>
			<div class="col-sm-12">
				<form class="form-horizontal" method="post">
					<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
						<label for="name" class="col-sm-2 control-label">Navn</label>
						<div class="col-sm-10">
							<input required type="text" class="form-control" name="name" id="name" placeholder="Navn" value="<?php echo (isset($_POST['name'])) ? $_POST['name'] : ''; ?>">
						</div>
					</div>
					<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
						<label for="email" class="col-sm-2 control-label">E-mail</label>
						<div class="col-sm-10">
							<input required type="email" class="form-control" name="email" id="email" placeholder="E-mail" value="<?php echo (isset($_POST['email'])) ? $_POST['email'] : ''; ?>">
						</div>
					</div>
					<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
						<label for="email" class="col-sm-2 control-label">Rolle</label>
						<div class="col-sm-10">
							<select name="role" id="role" class="form-control">
								<?php if (isset($_POST['role'])): ?>
									<option <?php echo ($_POST['role'] == 'viewer') ? 'selected' : '' ; ?> value="viewer">Seer</option>
									<option <?php echo ($_POST['role'] == 'accountant') ? 'selected' : '' ; ?> value="accountant">Bogholder / Revisor</option>
									<?php if(check_user_abilities_superadmin()) : ?>
										<option <?php echo ($_POST['role'] == 'admin') ? 'selected' : '' ; ?> value="admin">Administrator</option>
										<option <?php echo ($_POST['role'] == 'superadmin') ? 'selected' : '' ; ?> value="superadmin">Superadministrator</option>
									<?php endif; ?>
								<?php else: ?>
									<option selected value="viewer">Seer</option>
									<option value="accountant">Bogholder / Revisor</option>
									<?php if(check_user_abilities_superadmin()) : ?>
										<option value="admin">Administrator</option>
										<option value="superadmin">Superadministrator</option>
									<?php endif; ?>
								<?php endif; ?>
							</select>
						</div>
					</div>
					<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
						<label for="password" class="col-sm-2 control-label">Password</label>
						<div class="col-sm-10">
							<input required type="password" class="form-control" name="password" id="password" placeholder="Password">
						</div>
					</div>
					<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
						<label for="confirm_password" class="col-sm-2 control-label">Bekræft password</label>
						<div class="col-sm-10">
							<input required type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Bekræft password">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-2 col-sm-10">
							<button type="submit" class="btn btn-primary" name="add_user">Tilføj</button>
						</div>
					</div>
				</form>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php

require '../templates/admin/footer.php';

?>