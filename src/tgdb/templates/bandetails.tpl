<div class="container">
	<div class="core-content">
		{#IFDEF:ERROR_MSG}
		<div class="alert alert-danger">
			<strong>ERROR</strong> {ERROR_MSG}
		</div>
		{#ENDIF}{#IFNDEF:ERROR_MSG}
{#IFDEF:COMMENTHIDEPLZ}
id
bantime
server_ip
round_id
bantype
reason
job
duration
rounds
expiration_time
ckey
computerid
ip
a_ckey
a_computerid
a_ip
who
adminwho
edits
unbanned
unbanned_datetime
unbanned_ckey
unbanned_computerid
unbanned_ip
{#ENDIF}
		<div class="panel panel-default">
			<div class="panel-heading">Ban Details</div>
			<div class="panel-body">
				<table class="inlinetable">
					<tr>
						<td>Status</td>
						<td>{BAN_STATUS}</td>
					</tr>
					<tr>
						<td>Ban Date</td>
						<td>{BANTIME}</td>
					</tr>
					{#IFDEF:DURATION}
					<tr>
						<td>Ban length</td>
						<td>{DURATION}</td>
					</tr>
					{#ENDIF}
					
					<tr>
						<td>{#IFDEF:DURATION}Real {#ENDIF}Ban length</td>
						<td>{REAL_BAN_TIME}</td>
					</tr>
					<tr>
						<td>Expire Time</td>
						<td>{EXPIRATION_TIME}</td>
					</tr>
					<tr>
						<td>Ban Type</td>
						<td>{BANTYPE}</td>
					</tr>
					{#IFDEF:JOB}
					<tr>
						<td>Banned From</td>
						<td>{JOB}</td>
					</tr>
					{#ENDIF}
					<tr>
						<td>Server</td>
						<td>{SERVER_IP}</td>
					</tr>
					<tr>
						<td>Round ID</td>
						<td>{ROUND_ID}</td>
					</tr>
				</table>
			</div>
		</div>
		{#IFDEF:UNBANNED}
		<div class="panel panel-default">
			<div class="panel-heading">Unban Details</div>
			<div class="panel-body">
				<table class="inlinetable">
					<tr>
						<td>Unban time</td>
						<td>{UNBANNED_DATETIME}</td>
					</tr>
					<tr>
						<td>Admin ckey</td>
						<td>{UNBANNED_CKEY}</td>
					</tr>
					<tr>
						<td>Admin CID</td>
						<td>{UNBANNED_COMPUTERID}</td>
					</tr>
					<tr>
						<td>Admin IP</td>
						<td>{UNBANNED_IP}</td>
					</tr>
					
				</table>
			</div>
		</div>
		{#ENDIF}
		<div class="panel panel-default">
			<div class="panel-heading">Player Details</div>
			<div class="panel-body">
				<table class="inlinetable">
					{#IFDEF:CKEY}
					<tr>
						<td>Player Ckey</td>
						<td>{CKEY}</td>
					</tr>
					{#ENDIF}{#IFDEF:COMPUTERID}
					<tr>
						<td>Player CID</td>
						<td>{COMPUTERID}</td>
					</tr>
					{#ENDIF}{#IFDEF:IP}
					<tr>
						<td>Player IP</td>
						<td>{IP}</td>
					</tr>
					{#ENDIF}
				</table>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">Banning Admin Details</div>
			<div class="panel-body">
				<table class="inlinetable">
					<tr>
						<td>Banning Admin Ckey</td>
						<td>{A_CKEY}</td>
					</tr>
					<tr>
						<td>Banning Admin CID</td>
						<td>{A_COMPUTERID}</td>
					</tr>
					<tr>
						<td>Banning Admin IP</td>
						<td>{A_IP}</td>
					</tr>
				</table>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">Ban reason</div>
			<div class="panel-body">
				<p>{REASON}</p>
			</div>
		</div>
		{#IFDEF:EDITS}
		<div class="panel panel-default">
			<div class="panel-heading">Edits</div>
			<div class="panel-body">
				<p>{EDITS}</p>
			</div>
		</div>
		{#ENDIF}
		<div class="panel panel-default">
			<div class="panel-heading">List of online users at the time the ban was issued</div>
			<div class="panel-body">
				<table class="inlinetable">
					{WHO}
				</table>
			</div>
		</div>
		
		<div class="panel panel-default">
			<div class="panel-heading">List of online admins at the time the ban was issued</div>
			<div class="panel-body">
				<table class="inlinetable">
					{ADMINWHO}
				</table>
			</div>
		</div>
		{#ENDIF}
	</div>

</div>