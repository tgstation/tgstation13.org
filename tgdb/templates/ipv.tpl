Hello {USERCKEY} ({USERRANK}).
<hr/>

{#IFDEF:POLL_ROWS}
<div class="panel panel-default">
	<div class="panel-body">
		<table class="table table-bordered table-hover table-condensed table-striped floatthead">
			<thead>
				<th style="width:auto;padding:0px;text-align:center">Poll</th>
			</thead>
			<tbody>
				{#ARRAY:POLL_ROWS}
				<tr>
					<td style="text-overflow:nowrap;overflow:nowrap;padding:0px">
						<a href="?id={ID}">{QUESTION}</a>
					</td>
				</tr>
				{#ENDIF}
			</tbody>
		</table>
	</div>
</div>
{#ENDIF}

{#IFDEF:IPV_ROUNDS}
{#IFDEF:USERRANK}
<div class="panel panel-default" id="searchpanel">
	<div class="panel-heading" >
		<h4 class="panel-title" >
			<button class="btn btn-default btn-xs" data-toggle="collapse" data-parent="searchpanel" href="#collapseOne" role="button">Filter Panel</button>
		</h4>

	</div>
	<div id="collapseOne" class="panel-collapse {PANELOPEN}">
		<div class="panel-body">
			<form action='?id={ID}' method='get' class="form-inline" role="form">
				<input type='hidden' name='id' value='{ID}'>
				<div class="row">
					<div class="form-group col-md-2 col-xl-1">
						<label class="control-label" for='firstseen'>First Seen:</label>
						<input id='firstseen' class="form-control input-sm" type='text' placeholder='YYYY-MM-DD' value='{FIRSTSEEN}' name='firstseen'/>
					</div>
					<div class="form-group col-md-2 col-xl-1">
						<label class="control-label" for='connectioncount'>With</label>
						<input id='connectioncount' class="form-control input-sm" type='text' placeholder="Connections" value='{CONNECTIONCOUNT}' name='connectioncount'/>
					</div>
					<div class="form-group col-md-2 col-xl-1">
						<label class="control-label" for='connectionsstart'>Between:</label>
						<input id='connectionsstart' class="form-control input-sm" type='text' placeholder='YYYY-MM-DD' value='{CONNECTIONSSTART}' name='connectionsstart'/>
					</div>
					<div class="form-group col-md-2 col-xl-1">
						<label class="control-label" for='connectionsend'>And:</label>
						<input id='connectionsend' class="form-control input-sm" type='text' placeholder='YYYY-MM-DD' value='{CONNECTIONSEND}' name='connectionsend'/>
					</div>
				</div>
				<div class="row">
					<div class="form-group col-md-2 col-xl-1">
						<label class="control-label" for='jobminutes'>Must have:</label>
						<input id='jobminutes' class="form-control input-sm" type='text' placeholder='Minutes' value='{JOBMINUTES}' name='jobminutes'/>
					</div>
					<div class="form-group col-md-2 col-xl-1">
						<label class="control-label" for='jobname'>As<sup>1<sup>: &nbsp; &nbsp;</label>
						<input id='jobname' class="form-control input-sm" type='text' placeholder='Job/Role Name' value='{JOBNAME}' name='jobname'/>
					</div>
					<div class="form-group col-md-2 col-xl-1">
							<label class="control-label" for="playeronly">Limit Ranks:</label><br>
							{#IFDEF}<input type="radiobutton" id="playeronly" name="playeronly" value="yes" {PLAYERONLYCHECKED}/>Players Only{#ENDIF}
							<select id="rankfilter" name="rankfilter">
								<option value="all">All</option>
								<option {#IFDEF:RANK_PLAYERS}selected="selected"{#ENDIF} value="player">Players Only</option>
								<option {#IFDEF:RANK_ADMINS}selected="selected"{#ENDIF} value="admin">Admins Only</option>
							</select>
					</div>
					<div class="form-group col-md-2 col-xl-1">
						<label class="control-label" for="filtersubmit">Filter:</label>
						<button role="submit" id="filtersubmit" type="submit" class="btn btn-sm btn-block btn-primary" >GO!</button>
					</div>
				</div>
			</form>
			<br>
			<sub>
				<p>
					<sup>1</sup>You may use | for mutiple jobs
				</p>
			</sub>
		</div>
	</div>
</div>
{#ENDIF}
<div class="panel panel-default">
	<div class="panel-body">
		{#ARRAY:IPV_ROUNDS}
		<div class="panel panel-default">
			<div class="panel-body">
				Round #{ROUND_NUMBER}
				<div class="panel panel-default">
					<div class="panel-body">
						<table class="table table-bordered table-hover table-condensed table-striped floatthead">
							<thead>
								<th style="width:auto;padding:0px;text-align:center">Candidate</th>
								<th style="width:auto;padding:0px;text-align:center">First Pick Votes</th>
								<th style="width:auto;padding:0px;text-align:center">Total Vote Value</th>
							</thead>
							<tbody>
								{#ARRAY:IPV_ROUND_RES}
								<tr>
									<td style="text-overflow:nowrap;overflow:nowrap;padding:0px">
										<b>{CANDIDATE}</b>
									</td>
									<td style="text-overflow:nowrap;overflow:nowrap;padding:0px">
										<b>{VOTES}</b>{#IFDEF:DIFF}<span style="position:absolute"> &nbsp; (+{DIFF})</span>{#ENDIF}
									</td>
									<td style="text-overflow:nowrap;overflow:nowrap;padding:0px">
										<b>{VALUE_STR}</b>
									</td>
								</tr>
								{#ENDIF}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		{#ENDIF}
	</div>
</div>

{#ENDIF}