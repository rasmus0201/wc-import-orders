<?php
require '../app/db.php';
require '../app/init.php';

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
	header('Location: '.BASE_URL);
	exit;
}

if (isset($_POST['make_label'])) {

	if (isset($_POST['save_as_html'])) {
		header('Content-type: application/pdf');
		header('Content-disposition: attachment; filename='.$_POST['name'].' - Ingrediens label.html');
		header('Pragma: no-cache');
		header('Expires: 0');
	}

	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title><?php echo $_POST['name'].' - Ingrediens label'; ?></title>
		<style>
			@page { size: auto;  margin: 0mm; max-height: 297mm; page-break-after: auto; page-break-before: auto; }
			@media print { html, body { height: auto; page-break-after: auto; page-break-before: auto; } #back{ display: none; } }
			html{ max-height: 296mm; }
			body{ max-width: 210mm; max-height: 296mm; margin: 3mm 2mm 0mm 2mm; font-family: Calibri;}
			.container {
				width: 33%;
				max-height: 296mm;
				display: inline-block;
			}

			.container .flex-container .flex-item:first-of-type{
				margin-top: 15px;
			}
			.container .flex-container .flex-item:last-of-type{
				margin-bottom: 0;
			}

			.flex-container{
				display: -webkit-flex;
				display: flex;
				-webkit-flex-direction: column; 
				flex-direction: column;
				-webkit-align-items: stretch;
				align-items: stretch;
			}

			.flex-item {
				font-size: 8px;
				margin: <?php echo (str_replace('3by', '', $_POST['format'] == 7)) ? '10' : '12' ; ?>px 3px 3px 3px;
				height: calc(<?php echo (str_replace('3by', '', $_POST['format'] == 7)) ? '250' : '240' ; ?>mm/<?php echo str_replace('3by', '', $_POST['format']); ?>);
				position: relative;
				display: block;
				/*display: -webkit-box;
				display: -moz-box;
				display: -ms-flexbox;
				display: -webkit-flex;
				display: flex;
				-webkit-box-orient: vertical;
				-moz-box-orient: vertical;
				-webkit-box-direction: normal;
				-moz-box-direction: normal;
				-webkit-flex-direction: column;
				-ms-flex-direction: column;
				flex-direction: column;
				-webkit-align-items: stretch;
				align-items: stretch;*/
			}
			.flex-item .name{
				display: block;
			}
			#back{
				position: absolute;
				top: 2px;
				left: 10px;
			}
			<?php if($_POST['format'] == '3by8'): ?>
				body{
					margin-left: 0 !important;
					margin-right: 0 !important;
				}

				.container {
					width: 32%;
				}

				.container:nth-child(3){
					margin-left: 5.9mm;
					/*padding-left: 5mm;*/
				}
			<?php elseif($_POST['format'] == '3by7'): ?>
				body{
					margin-top: 10mm;
					margin-left: 0;
					margin-right: 0;
					width: 210mm;
				}
				.container {
					width: 62mm;
				}
				.container:nth-child(1){
					margin-left: 7mm;
				}
				.container:nth-child(2){
					margin-left: 3mm;
					margin-right: 3mm;
				}
				.container:nth-child(3){
					margin-right: 7mm;
				}
				.container:nth-child(3) .flex-container{
					padding-left: 4mm;
				}

				.container .flex-container .flex-item:nth-child(1){
					margin-bottom: 4mm;
				}
				.container .flex-container .flex-item:nth-child(2){
					margin-bottom: 6mm;
				}
				.container .flex-container .flex-item:nth-child(3){
					margin-bottom: 4mm;
				}
				.container .flex-container .flex-item:nth-child(4){
					margin-bottom: 6mm;
				}
			<?php endif; ?>
		</style>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
		<script>
			$(document).ready(function(){
				if ($('body').innerHeight() > 1100 ) {
					$('.flex-item').css('font-size', '8px');

					if ($('body').innerHeight() > 1100) {
						$('.flex-item').css('font-size', '7px');

						if ($('body').innerHeight() > 1100) {
							$('.flex-item').css('font-size', '6px');
						}
					}
				}
			});
		</script>
	</head>
	<body>
	<?php
	if ($_POST['format'] == '3by7') {
		?>
				<div class="container">
					<div class="flex-container">
						<?php for ($i=1; $i <= 7 ; $i++): ?>
							<div class="flex-item">
								<strong class="name"><?php echo $_POST['name']; ?></strong>
								<strong>Ingredienser: </strong><?php echo trim(htmlspecialchars_decode($_POST['ingredients'])); ?>
							</div>
						<?php endfor; ?>
					</div>
				</div>
				<div class="container">
					<div class="flex-container">
						<?php for ($i=1; $i <= 7 ; $i++): ?>
							<div class="flex-item">
								<strong class="name"><?php echo $_POST['name']; ?></strong>
								<strong>Ingredienser: </strong><?php echo trim(htmlspecialchars_decode($_POST['ingredients'])); ?>
							</div>
						<?php endfor; ?>
					</div>
				</div>
				<div class="container">
					<div class="flex-container">
						<?php for ($i=1; $i <= 7 ; $i++): ?>
							<div class="flex-item">
								<strong class="name"><?php echo $_POST['name']; ?></strong>
								<strong>Ingredienser: </strong><?php echo trim(htmlspecialchars_decode($_POST['ingredients'])); ?>
							</div>
						<?php endfor; ?>
					</div>
				</div>
				<?php if(!isset($_POST['save_as_html'])): ?>
					<div id="back"><a href="<?php echo BASE_URL.'/'.$global['current_url']; ?>">Tilbage?</a></div>
				<?php endif; ?>
			</body>
		</html>
		<?php
		exit;
	} else if ($_POST['format'] == '3by8') {
		?>
				<div class="container">
					<div class="flex-container">
						<?php for ($i=1; $i <= 8 ; $i++): ?>
							<div class="flex-item">
								<strong class="name"><?php echo $_POST['name']; ?></strong>
								<strong>Ingredienser: </strong><?php echo trim(htmlspecialchars_decode($_POST['ingredients'])); ?>
							</div>
						<?php endfor; ?>
					</div>
				</div>
				<div class="container">
					<div class="flex-container">
						<?php for ($i=1; $i <= 8 ; $i++): ?>
							<div class="flex-item">
								<strong class="name"><?php echo $_POST['name']; ?></strong>
								<strong>Ingredienser: </strong><?php echo trim(htmlspecialchars_decode($_POST['ingredients'])); ?>
							</div>
						<?php endfor; ?>
					</div>
				</div>
				<div class="container">
					<div class="flex-container">
						<?php for ($i=1; $i <= 8 ; $i++): ?>
							<div class="flex-item">
								<strong class="name"><?php echo $_POST['name']; ?></strong>
								<strong>Ingredienser: </strong><?php echo trim(htmlspecialchars_decode($_POST['ingredients'])); ?>
							</div>
						<?php endfor; ?>
					</div>
				</div>
				<?php if(!isset($_POST['save_as_html'])): ?>
					<div id="back"><a href="<?php echo BASE_URL.'/'.$global['current_url']; ?>">Tilbage?</a></div>
				<?php endif; ?>
			</body>
		</html>
		<?php
		exit;
	}
}

require '../templates/admin/header.php';
?>

<div class="row">
	<?php require '../templates/admin/sidebar.php'; ?>
	<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
		<h1 class="page-header"><?php echo $titles['admin/tools.php'].' / '.$global['site_title']; ?></h1>
		<form method="post">
			<div class="col-sm-7">
				<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
					<label for="name" class="control-label">Produkt Navn (Overskrift):</label>
					<input required type="text" class="form-control" name="name" id="name" placeholder="Eks.: Bean Boozled 45gr." value="<?php echo (isset($_POST['name'])) ? $_POST['name'] : ''; ?>">
				</div>
				<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
					<label for="ingredients" class="control-label">Ingredienser:</label>
					<textarea required class="form-control" name="ingredients" id="ingredients" cols="30" rows="10"><?php echo (isset($_POST['ingredients'])) ? $_POST['ingredients'] : '' ;?></textarea>
				</div>
			</div>
			<div class="col-sm-5">
				<div class="form-group <?php echo (isset($form_error)) ? 'has-error' : '' ;?>">
					<label for="format" class="control-label">Vælg format</label>
					<select required class="form-control" name="format" id="format">
						<?php if(isset($_POST['format'])): ?>
							<option <?php echo ($_POST['format'] == '3by7') ? 'selected' : '' ;?> value="3by7">3 x 7</option>
							<option <?php echo ($_POST['format'] == '3by8') ? 'selected' : '' ;?> value="3by8">3 x 8</option>
						<?php else: ?>
							<option value="3by7">3 x 7</option>
							<option value="3by8">3 x 8</option>
						<?php endif; ?>
					</select>
				</div>
				<div class="form-group">
					<label for="save_as_html">Gem som HTML</label>
					<input type="checkbox" id="save_as_html" name="save_as_html" value="on">
				</div>
				<div class="form-group">
					<button type="submit" class="btn btn-primary" name="make_label" style="width:100%;">Lav label</button>
				</div>
				<div class="form-group">
					<?php echo message('Kode til tyk tekst: <code>'.htmlspecialchars('<strong> </strong>').'</code>', 'info', false); ?>
				</div>
			</div>
		</form>
	</div>
</div>

<?php

require '../templates/admin/footer.php';

?>