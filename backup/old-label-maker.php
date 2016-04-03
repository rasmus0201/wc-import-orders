	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title> Labels </title>
		<style>
			@page { size: auto;  margin: 0mm; max-height: 297mm; }
			@media print { html, body { height: auto; page-break-after: avoid; } }
			html{ max-height: 297mm; }
			td{ width: 65mm; }

			<?php if($_POST['format'] == '3by7'): ?>
				body{ max-height: 297mm; margin: 0.5mm 2mm 5mm 2mm; font-family: Calibri;}
				tr{ height: <?php echo round(270/(int)str_replace('3by', '', $_POST['format']), 4); ?>mm; font-size: 9px; overflow: hidden;}
				
				tr:last-of-type td{
					padding-top: 30px;
					height: 100%;
				}
			<?php elseif($_POST['format'] == '3by8'): ?>
				body{ max-height: 297mm; margin: 0mm 2mm 3mm 2mm; font-family: Calibri;}
				tr{ height: <?php echo round(270/(int)str_replace('3by', '', $_POST['format']), 4); ?>mm; font-size: 9px; overflow: hidden;}
				
				tr:last-of-type td{
					padding-top: 20px;
					height: 100%;
				}
			<?php endif; ?>
		</style>
		<script src="<?php echo STATIC_URL; ?>/js/jquery.min.js"></script>
		<script>
			$(document).ready(function(){
				console.log($('body').innerHeight() > 1120);
				console.log($('body').innerHeight());
				if ($('body').innerHeight() > 1060 ) {
					$('tr').css('font-size', '8px');

					if ($('body').innerHeight() > 1060) {
						$('tr').css('font-size', '7px');

						if ($('body').innerHeight() > 1060) {
							$('tr').css('font-size', '6px');
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
				<table>
					<?php for ($i=1; $i <= 7 ; $i++): ?>
						<tr>
							
							<td>
								<strong><?php echo $_POST['name']; ?></strong><br>
								<strong>Ingredienser:</strong> <?php echo htmlspecialchars_decode($_POST['ingredients']); ?>
							</td>
							<td>
								<strong><?php echo $_POST['name']; ?></strong><br>
								<strong>Ingredienser:</strong> <?php echo htmlspecialchars_decode($_POST['ingredients']); ?>
							</td>
							<td>
								<strong><?php echo $_POST['name']; ?></strong><br>
								<strong>Ingredienser:</strong> <?php echo htmlspecialchars_decode($_POST['ingredients']); ?>
							</td>
						</tr>
					<?php endfor; ?>
				</table>
			</body>
		</html>
		<?php
		exit;
	} else if ($_POST['format'] == '3by8') {
		?>
				<table>
					<?php for ($i=1; $i <= 8 ; $i++): ?>
						<tr>
							<td>
								<strong>Ingredienser:</strong> <?php echo htmlspecialchars_decode($_POST['ingredients']); ?>
							</td>
							<td>
								<strong>Ingredienser:</strong> <?php echo htmlspecialchars_decode($_POST['ingredients']); ?>
							</td>
							<td>
								<strong>Ingredienser:</strong> <?php echo htmlspecialchars_decode($_POST['ingredients']); ?>
							</td>
						</tr>
					<?php endfor; ?>
				</table>
			</body>
		</html>
		<?php
		exit;
	}