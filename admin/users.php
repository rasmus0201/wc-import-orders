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

				$sth = $db->prepare("SELECT * FROM users WHERE id = :user_id LIMIT 1");
				$sth->bindParam(':user_id', $user_id);
				$sth->execute();

				$user = $sth->fetch(PDO::FETCH_ASSOC);

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

					$sth = $db->prepare("DELETE FROM users WHERE id = :user_id");
					$sth->bindParam(':user_id', $user_id);

					$result = $sth->execute();
					$result = true;

					if (!$result) {
						echo message('Noget gik galt.', 'danger');
					} else {
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
					<h1 class="page-header"><?php echo $global['site_title']; ?> / Rediger</h1>
				<?php endif; ?>
			<?php else: ?>
				<?php echo $is_action; ?>
			<?php endif; ?>
		<?php else: ?>
			<?php echo $message; ?>
			<h1 class="page-header"><?php echo $global['site_title']; ?></h1>
			<div class="col-sm-7">
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
									<?php if($user['role'] != 'superadmin'): ?>
										<tr>
											<td><?php echo $user['id']; ?></td>
											<td><?php echo $user['name']; ?></td>
											<td><?php echo $user['email']; ?></td>
											<td><?php echo $user['role']; ?></td>
											<?php if(check_user_abilities_superadmin()): ?>
												<td><a class="btn btn-default" href="?user_id=<?php echo $user['id']; ?>&action=edit" role="button">Rediger</a></td>
												<td><a class="btn btn-danger" href="?user_id=<?php echo $user['id']; ?>&action=delete" role="button">Slet</a></td>
											<?php endif; ?>
										</tr>	
									<?php endif; ?>
								<?php endforeach; ?>

							</tbody>
						</table>
					</div>
				<?php else: ?>
					<?php echo message('Der er ingen brugere endnu.', 'warning', false); ?>
				<?php endif; ?>
			</div>
			<div class="col-sm-5">
				<h4 class="page-header">Tilføj bruger</h4>
				<form class="form-horizontal" method="post">
					<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
						<label for="name" class="col-sm-3 control-label">Navn</label>
						<div class="col-sm-9">
							<input required type="text" class="form-control" name="name" id="name" placeholder="Navn" value="<?php echo (isset($_POST['name'])) ? $_POST['name'] : ''; ?>">
						</div>
					</div>
					<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
						<label for="email" class="col-sm-3 control-label">E-mail</label>
						<div class="col-sm-9">
							<input required type="email" class="form-control" name="email" id="email" placeholder="E-mail" value="<?php echo (isset($_POST['email'])) ? $_POST['email'] : ''; ?>">
						</div>
					</div>
					<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
						<label for="email" class="col-sm-3 control-label">Rolle</label>
						<div class="col-sm-9">
							<select name="role" id="role" class="form-control">
								<?php if (isset($_POST['role'])): ?>
									<option <?php echo ($_POST['role'] == 'viewer') ? 'selected' : '' ; ?> value="viewer">Seer</option>
									<option <?php echo ($_POST['role'] == 'accountant') ? 'selected' : '' ; ?> value="accountant">Bogholder / Revisor</option>
									<?php if(check_user_abilities_superadmin()) : ?>
										<option <?php echo ($_POST['role'] == 'admin') ? 'selected' : '' ; ?> value="admin">Administrator</option>
										<option <?php echo ($_POST['role'] == 'superadmin') ? 'selected' : '' ; ?> value="superadmin">Super Administrator</option>
									<?php endif; ?>
								<?php else: ?>
									<option selected value="viewer">Seer</option>
									<option value="accountant">Bogholder / Revisor</option>
									<?php if(check_user_abilities_superadmin()) : ?>
										<option value="admin">Administrator</option>
										<option value="superadmin">Super Administrator</option>
									<?php endif; ?>
								<?php endif; ?>
							</select>
						</div>
					</div>
					<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
						<label for="password" class="col-sm-3 control-label">Password</label>
						<div class="col-sm-9">
							<input required type="password" class="form-control" name="password" id="password" placeholder="Password">
						</div>
					</div>
					<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
						<label for="confirm_password" class="col-sm-3 control-label">Bekræft password</label>
						<div class="col-sm-9">
							<input required type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Bekræft password">
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-offset-3 col-sm-9">
							<button type="submit" class="btn btn-primary" name="add_user">Tilføj</button>
						</div>
					</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php

require '../templates/admin/footer.php';

?>