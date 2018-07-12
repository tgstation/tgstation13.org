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

{#IFDEF:BAN_ROWS}
<div class="panel panel-default">
	<div class="panel-body">
		<table class="table table-bordered table-hover table-condensed table-striped floatthead">
			<thead>
				<th style="width:auto;padding:0px;text-align:center">User Details</th>
				<th style="min-width:170px;padding:0px;text-align:center">Ban Details</th>
				<th style="max-width:10%;padding:0px;text-align:center">Ban Reason</th>
				<th style="min-width:150px;padding:0px;text-align:center">Ban Status</th>
			</thead>
			<tbody>
				{#ARRAY:BAN_ROWS}
				<tr>
					<td style="width:auto;padding:0px">
						{#IFDEF:BANNED_CKEY}<b>{BANNED_CKEY}</b><br/>{#ENDIF}
						{#IFDEF:BANNED_CID}{BANNED_CID}<br/>{#ENDIF}
						{#IFDEF:BANNED_IP}{BANNED_IP}{#ENDIF}
					</td>
					<td style="text-overflow:nowrap;overflow:nowrap;padding:0px">
						{BAN_DATE}
						<br/>{BANNING_ADMIN}
						<br/>{#IFDEF:BAN_JOB}<b>{BAN_JOB}</b>{#ENDIF}
								{#IFNDEF:BAN_JOB}<b>Server</b>{#ENDIF}
						<br/>{#IFDEF:BAN_LENGTH}{BAN_LENGTH}{#ENDIF}
								{#IFNDEF:BAN_LENGTH}Permanent{#ENDIF}
						<br/>
						<a class="easymodal" href="bandetails.php?id={BAN_ID}">More details</a>
					</td>
					<td style="max-width:10%;padding:0px">
						{BAN_REASON}
					</td>
					<td style="padding:0px">
						<b>{BAN_STATUS}</b><p/>
						{#IFDEF:UNBANNED}
							{UNBANNING_ADMIN}
							<p/>
							{UNBAN_TIME}
						{#ENDIF}
						{#IFNDEF:UNBANNED}
							{#IFDEF:EXPIRE_TIME}
								{EXPIRE_TIME}
							{#ENDIF}
						{#ENDIF}
					</td>
				</tr>
				{#ENDIF}
			</tbody>
		</table>
	</div>
</div>
{#ENDIF}
