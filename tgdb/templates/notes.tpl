Hello {USERCKEY} ({USERRANK}).
<hr/>
<div class="container">
	<div class="panel panel-default" id="searchpanel">
		<div class="panel-heading" >
			<h4 class="panel-title" >
				<button class="btn btn-default btn-xs" data-toggle="collapse" data-parent="searchpanel" href="#collapseOne" role="button">Search Panel</button>
			</h4>
			(use % to match any character as a wild card)
		</div>
		
		<div id="collapseOne" class="panel-collapse {PANELOPEN}">
			<div class="panel-body">
				<form action='?' method='get' class="form" role="form">
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
							<label class="control-label" for='text'>Text:</label>
							<input id='text' class="form-control input-sm" type='text' value='{TEXT}' name='text'/>
						</div>
						<div class="form-group col-md-2">
							<label class="control-label" for="searchtype">Search type:</label><br>
							<input type="radio" id="searchtype" name="searchtype" value="any" {SEARCHTYPEANYCHECKED}/>Any
							<input type="radio" id="searchtype" name="searchtype" value="all" {SEARCHTYPEALLCHECKED}/>All
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
</div>
<div class="panel panel-default">
	<div class="panel-body">
		<table class="table table-bordered table-hover table-condensed table-striped floatthead">
			<thead>
				<th style="min-width:200px;padding:0px;text-align:center">Details</th>
				<th style="padding:0px;text-align:center">Note</th>
			</thead>
			<tbody>
				{#ARRAY:NOTES}
					<tr>
						<td style="text-overflow:nowrap;overflow:nowrap;padding:0px">
							<b>{CKEY}</b><br/>
							{DATE}<br/>
							<b>{ADMIN}</b><br/>
							{SERVER}<br/>
						</td>
						<td style="padding:0px">{NOTE}</td>
					</tr>
				{#ENDIF}
			</tbody>
		</table>
	</div>
</div>