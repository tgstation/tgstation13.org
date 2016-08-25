<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="container-fluid">
		<ul class="nav navbar-nav">
		{NAVBARITEMS}
		</ul>
		<form class="navbar-form navbar-right" role="search" action='playerdetails.php' method='get'>
			<div class="form-group" style="padding-top:4px" >
			  <input type="text" name="ckey" id="usersearch" autocomplete="off" class="form-control input-sm" placeholder="ckey quick search" autocomplete="off">
			</div>
			<input  type="submit" name="update" value="search" 
    style="position: absolute; height: 0px; width: 0px; border: none; padding: 0px;"
    hidefocus="true" tabindex="-1"/>
		</form>
	</div>
</div>