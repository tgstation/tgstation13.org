Hello {USERCKEY} ({USERRANK}).
<hr/>
<div class="panel panel-default" id="searchpanel">
	<div class="panel-heading" >
		<h4 class="panel-title" >
			<button class="btn btn-default btn-xs" data-toggle="collapse" data-parent="searchpanel" href="#collapseOne" role="button">Search Panel</button>
		</h4>
		(use % to match any character as a wild card)
	</div>
	<div id="collapseOne" class="panel-collapse {PANELOPEN}">
		<div class="panel-body">
			<form action='?' method='get' class="form-inline" role="form">
				<div class="row">
					<div class="form-group col-md-2">
						<label class="control-label" for='adminckey'>Admin ckey:</label>
						<input id='adminckey' class="form-control input-sm" type='text' value='{ADMINCKEY}' name='adminckey'/>
					</div>
					<div class="form-group col-md-2">
						<label class="control-label" for='playerckey'>Player ckey:</label>
						<input id='playerckey' class="form-control input-sm" type='text' value='{PLAYERCKEY}' name='playerckey'/>
					</div>
					<div class="form-group col-md-2">
						<label class="control-label" for='playercid'>Player cid:</label>
						<input id='playercid' class="form-control input-sm" type='text' value='{PLAYERCID}' name='playercid'/>
					</div>
					<div class="form-group col-md-2">
						<label class="control-label" for='playerip'>Player ip:</label>
						<input id='playerip' class="form-control input-sm" type='text' value='{PLAYERIP}' name='playerip'/>
					</div>
					<div class="form-group col-md-1">
						<label class="control-label" for="searchsubmit">Search:</label>
						<button role="submit" id="searchsubmit" type="submit" class="btn btn-sm btn-primary" >GO!</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

{BANTABLE}