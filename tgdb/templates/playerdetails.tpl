 Hello {USERCKEY} ({USERRANK}).
<hr/>

<div class="container">
	{#IFDEF:ERROR_MSG}
		<div class="alert alert-danger">
			<strong>ERROR</strong> {ERROR_MSG}
		</div>
	{#ENDIF}{#IFNDEF:ERROR_MSG}
	<div class="panel panel-default">
		<div class="panel-heading" >
			<h4 class="panel-title" >
				User Details for {PLAYERCKEY} (<a href="http://www.byond.com/members/{CKEY}">Byond account</a>)
			</h4>
		</div>
		<div class="panel">
			<div class="panel-body">
				<div class="row">
					<div class="col-md-2 col-sm-6">
						First seen:
					</div>
					<div class="col-md-3 col-sm-6">
						{FIRSTSEEN}
					</div>
					<div class="col-md-2 col-sm-6">
						Last seen:
					</div>
					<div class="col-md-3 col-sm-6">
						{LASTSEEN}
					</div>
				</div>
				<div class="row">
					<div class="col-md-2 col-sm-6">
						Last IP:
					</div>
					<div class="col-md-3 col-sm-6">
						{LASTIP}
					</div>
					<div class="col-md-2 col-sm-6">
						IPs Seen:
					</div>
					<div class="col-md-3 col-sm-6">
						{IPCOUNT}
					</div>
				</div>
				<div class="row">
					<div class="col-md-2 col-sm-6">
						Last ComputerID:
					</div>
					<div class="col-md-3 col-sm-6">
						{LASTCOMPUTERID}
					</div>
					<div class="col-md-2 col-sm-6">
						CIDs Seen:
					</div>
					<div class="col-md-3 col-sm-6">
						{CIDCOUNT}
					</div>
				</div>
				<div class="row">
					<div class="col-md-2 col-sm-6">
						Total Connections:
					</div>
					<div class="col-md-3 col-sm-6">
						{ROUNDCOUNT}
					</div>
				</div>
				<div class="row">
					<div class="col-md-2 col-sm-6">
						Last Rank:
					</div>
					<div class="col-md-3 col-sm-6">
						{LASTADMINRANK}
					</div>
				</div>
				<div class="row">
					<div class="col-md-2 col-sm-6">
						Active Bans:
					</div>
					<div class="col-md-3 col-sm-6">
						{ACTIVEBANS}
					</div>
					<div class="col-md-2 col-sm-6">
						Total Bans:
					</div>
					<div class="col-md-3 col-sm-6">
						{REALBANS}
					</div>
				</div>
				<div class="row">
					<div class="col-md-2 col-sm-6">
						Account Standing:
					</div>
					<div class="col-md-10 col-sm-12">
						{#IFDEF:CLEANSTANDING}Not banned{#ENDIF}
						{#IFNDEF:CLEANSTANDING}
							{#IFDEF:PERMABANNED}PermaBanned {#ENDIF}
							{#IFNDEF:PERMABANNED}{#IFDEF:BANNED}Banned {#ENDIF}{#ENDIF}
							{#IFDEF:JOBBANNED}Job Banned {#ENDIF}
							{#IFDEF:IDBANNED}Identity Banned {#ENDIF}
							{#IFDEF:ADMINBANNED}Admin Banned {#ENDIF}
						{#ENDIF}
					</div>
				</div>
			</div>
		</div>
	</div>
	
</div>

<div class="container">
	<div class="panel panel-default" id="notetable">
		<div class="panel-heading" >
			<h4 class="panel-title" >
				<button class="btn btn-default btn-xs" data-toggle="collapse" data-parent="notetable" href="#notetablecollapse" role="button">View Notes ({NOTECOUNT})</button>
			</h4>
		</div>
		<div id="notetablecollapse" class="panel-collapse {NOTETABLEOPEN}">
			<div class="panel-body">
				<table class="table table-bordered table-hover table-condensed table-striped floatthead">
					<thead>
						<th style="min-width:170px;padding:0px;text-align:center">Details</th>
						<th style="padding:0px;text-align:center">Note</th>
					</thead>
					<tbody>
						{#ARRAY:NOTES}
							<tr>
								<td style="text-overflow:nowrap;overflow:nowrap;padding:0px">
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
	</div>
	<div class="panel panel-default" id="bantable">
		<div class="panel-heading" >
			<h4 class="panel-title" >
				<button class="btn btn-default btn-xs" data-toggle="collapse" data-parent="bantable" href="#bantablecollapse" role="button">View Bans (only ckey matches) ({BANCOUNT})</button>
			</h4>
		</div>
		<div id="bantablecollapse" class="panel-collapse {BANTABLEOPEN}">
			<div class="panel-body">
				<table class="table table-bordered table-hover table-condensed table-striped floatthead">
					<thead>
						<th style="min-width:170px;padding:0px;text-align:center">Ban Details</th>
						<th style="padding:0px;text-align:center">Ban Reason</th>
						<th style="min-width:150px;padding:0px;text-align:center">Ban Status</th>
					</thead>
					<tbody>
						{#ARRAY:BANS}
							<tr>
								<td style="text-overflow:nowrap;overflow:nowrap;padding:0px">
									<a class="easymodal" href="bandetails.php?id={BAN_ID}">{BAN_DATE}</a><br>
									{BANNING_ADMIN}<br/>
									<b>{#IFDEF:BAN_JOB}{BAN_JOB}{#ENDIF}{#IFNDEF:BAN_JOB}Server{#ENDIF}</b><br/>
									{#IFDEF:BAN_LENGTH}{BAN_LENGTH}{#ENDIF}{#IFNDEF:BAN_LENGTH}Permanent{#ENDIF}
								</td>
								<td style="padding:0px">
									{BAN_REASON}
								</td>
								<td style="padding:0px">
									<b>{BAN_STATUS}</b><p/>
									{#IFDEF:UNBANNED}
										{UNBANNING_ADMIN}<p/>{UNBAN_TIME}
									{#ENDIF}{#IFNDEF:UNBANNED}
										{#IFDEF:EXPIRE_TIME}{EXPIRE_TIME}{#ENDIF}
									{#ENDIF}
								</td>
							</tr>
						{#ENDIF}
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="panel panel-default" id="mesagetable">
		<div class="panel-heading">
			<h4 class="panel-title">
				<button class="btn btn-default btn-xs" data-toggle="collapse" data-parent="messagetable" href="#messagetablecollapse" role ="button">View Messages ({MESSAGECOUNT})</button>
			</h4>
		</div>
		<div id="messagetablecollapse" class="panel-collapse {MESSAGETABLEOPEN}">
			<div class="panel-body">
				<table class="table table-bordered table-hover table-condensed table-striped floatthead">
					<thead>
						<th style="min-width:170px;padding:0px;text-align:center">Details</th>
						<th style="padding:0px;text-align:center">Message</th>
					</thead>
					<tbody>
						{#ARRAY:MESSAGES}
							<tr>
								<td style="text-overflow:nowrap;overflow:nowrap;padding:0px">
									{DATE}<br/>
									<b>{ADMIN}</b><br/>
									{SERVER}<br/>
									{#IFDEF:READ}Message read{#ENDIF}
								</td>
								<td style="padding:0px">{MESSAGE}</td>
							</tr>
						{#ENDIF}
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="panel panel-default" id="cidtable">
		<div class="panel-heading" >
			<h4 class="panel-title" >
				<button class="btn btn-default btn-xs" data-toggle="collapse" data-parent="cidtable" href="#cidtablecollapse" role="button">View Computer IDs ({CIDCOUNT})</button>
			</h4>
		</div>
		<div id="cidtablecollapse" class="panel-collapse {CIDTABLEOPEN}">
			<div class="panel-body">
				<table class="table table-bordered table-hover table-condensed table-striped floatthead">
					<thead>
						<th style="padding:0px;text-align:center">Computer ID</th>
						<th style="padding:0px;text-align:center">Rounds Matched</th>
					</thead>
					<tbody>
						{#ARRAY:CIDS}
							<tr>
								<td style="padding:0px">{CID}</td>
								<td style="padding:0px">{ROUNDS}</td>
							</tr>
						{#ENDIF}
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="panel panel-default" id="iptable">
		<div class="panel-heading" >
			<h4 class="panel-title" >
				<button class="btn btn-default btn-xs" data-toggle="collapse" data-parent="iptable" href="#iptablecollapse" role="button">View IPs ({IPCOUNT})</button>
			</h4>
		</div>
		<div id="iptablecollapse" class="panel-collapse {IPTABLEOPEN}">
			<div class="panel-body">
					<table class="table table-bordered table-hover table-condensed table-striped floatthead">
					<thead>
						<th style="padding:0px;text-align:center">IP</th>
						<th style="padding:0px;text-align:center">Rounds Matched</th>
					</thead>
					<tbody>
						{#ARRAY:IPS}
							<tr>
								<td style="padding:0px">{IP}</td>
								<td style="padding:0px">{ROUNDS}</td>
							</tr>
						{#ENDIF}
					</tbody>
				</table>
			</div>
		</div>
	</div>
	{#ENDIF}
</div>
