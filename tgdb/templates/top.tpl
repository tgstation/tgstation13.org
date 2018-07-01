 Hello {USERCKEY} ({USERRANK}).
<hr/>
<div class="container">
	<div class="panel panel-default" id="searchpanel">
		<div class="panel-heading" >
			<h4 class="panel-title" >
				<button class="btn btn-default btn-xs" data-toggle="collapse" data-parent="searchpanel" href="#collapseOne" role="button">Filter Panel</button>
			</h4>
		</div>
		<div id="collapseOne" class="panel-collapse {PANELOPEN}">
			<div class="panel-body">
				<form action='?' method='get' class="form" role="form">
					<div class="row">
						<div class="form-group col-md-3">
							<input type="radio" id="filtertype" name="filtertype" value="all" {FILTERNONECHECKED}/>All
						</div><div class="form-group col-md-3">
							<input type="radio" id="filtertype" name="filtertype" value="players" {FILTERPLAYERCHECKED}/>Players only
						</div><div class="form-group col-md-3">
							<input type="radio" id="filtertype" name="filtertype" value="admins" {FILTERADMINCHECKED}/>Admins only
						</div>
						<div class="form-group col-md-3">
							<button role="submit" id="filtersubmit" type="submit" class="btn btn-sm btn-primary" >Apply</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<table class="table table-bordered table-hover table-condensed table-striped floatthead">
		<thead>
			<th>Rank</th>
			<th>Ckey</th>
			<th>Connections</th>
		</thead>
		<tbody>
			{#ARRAY:CKEYS}
				<tr>
					<td>#{RANK}</td>
					<td>{CKEY}</td>
					<td>{CONNECTIONS}</td>
				</tr>
			{#ENDIF}
		</tbody>
	</table>
</div>