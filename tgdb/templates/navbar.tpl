<div class="navbar navbar-inverse">
	<div class="container-fluid">
		<ul class="nav navbar-nav">
		{NAVBARITEMS}
		</ul>
		{#IFDEF:DEBUG}
		<form class="navbar-form navbar-right" role="search" action='playerdetails.php' method='get'>
			<div class="form-group" style="padding-top:4px" >
			  <input type="text" name="ckey" class="form-control input-sm" placeholder="ckey quick search">
			</div>
		</form>{#ENDIF}
	</div>
</div>