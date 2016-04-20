<?php

require 'app/db.php';
require 'app/init.php';

require 'templates/head.php';

?>
<div class="container" style="margin-bottom: 60px;">
<?php echo $message; ?>
<div class="jumbotron">
	<h1>Ulvemosens Handelsselskab ApS</h1>
	<h3>- Forside</h3>
	<p>Her kan du se alle de projekter vi har gang i lige pt. Skriv eller ring til os, hvis du vil høre mere.</p>
</div>
<div class="row">
	<div class="col-md-12"><p class="text-muted text-center">Se vores igangværende projekter nedenunder</p><hr></div>
</div>
<div class="row">
	<div class="col-md-4 text-center"><a href="http://jellybeans.dk"><img width="300" height="120" src="static/images/jellybeans.png" alt="Jellybeans.dk"></a></div>
	<div class="col-md-4 text-center"><a href="https://it-lease.dk"><img width="300" height="100" src="static/images/it-lease.png" alt="IT-Lease.dk"></a></div>
	<div class="col-md-4 text-center"><a href="http://slikworld.dk"><img width="300" height="100" src="static/images/slikworld.png" alt="SlikWorld.dk"></a></div>
	<div class="col-md-12 text-center"><a href="http://julemosen.dk" style="font-size: 40px; font-family: Arial; font-weight: 300; color: #222;">Julemosen.dk</a></div>
</div>
<?php /*<div id="slider" style="position: relative; margin: 0 auto; top: 0px; left: 0px; width: 980px; height: 100px; overflow: hidden; visibility: hidden;">
	<div data-u="loading" style="position: absolute; top: 0px; left: 0px;">
		<div style="filter: alpha(opacity=70); opacity: 0.7; position: absolute; display: block; top: 0px; left: 0px; width: 100%; height: 100%;"></div>
		<div style="position:absolute;display:block;background:url('static/images/loading.gif') no-repeat center center;top:0px;left:0px;width:100%;height:100%;"></div>
	</div>
	<div data-u="slides" style="cursor: default; position: relative; top: 0px; left: 0px; width: 980px; height: 100px; overflow: hidden;">
		<div style="display: none;">
			<img data-u="image" src="static/images/jellybeans.png" />
		</div>
		<div style="display: none;">
			<img data-u="image" src="static/images/it-lease.png" />
		</div>
		<div style="display: none;">
		 	<img data-u="image" src="static/images/slikworld.png" />
		</div>
	</div>
</div>*/?>
<?php

require 'templates/footer.php';

?>