 Hello {USERCKEY} ({USERRANK}).
<hr/>

<div class="container">
	<div class="panel panel-default">
		<div class="panel-heading" >
			<h4 class="panel-title" >
				User Details for {PLAYERCKEY}
			</h4>
		</div>
		<div class="panel">
			<div class="panel-body">
				<div class="row">
					<div class="col-sm-2">
						First seen:
					</div>
					<div class="col-sm-2">
						{FIRSTSEEN}
					</div>
				</div>
				<div class="row">
					<div class="col-sm-2">
						Last seen:
					</div>
					<div class="col-sm-2">
						{LASTSEEN}
					</div>
				</div>
				<div class="row">
					<div class="col-sm-2">
						Total Rounds:
					</div>
					<div class="col-sm-2">
						{ROUNDCOUNT}
					</div>
				</div>

			</div>
		</div>
	</div>
</div>

<div class="container">
	<div class="panel panel-default" id="cidtable">
		<div class="panel-heading" >
			<h4 class="panel-title" >
				<button class="btn btn-default btn-xs" data-toggle="collapse" data-parent="cidtable" href="#cidtablecollapse" role="button">View Computer IDs</button>
			</h4>
		</div>
		<div id="cidtablecollapse" class="panel-collapse {CIDTABLEOPEN}">
			<div class="panel-body">
				{CIDTABLE}
			</div>
		</div>
	</div>
	<div class="panel panel-default" id="iptable">
		<div class="panel-heading" >
			<h4 class="panel-title" >
				<button class="btn btn-default btn-xs" data-toggle="collapse" data-parent="iptable" href="#iptablecollapse" role="button">View IPs</button>
			</h4>
		</div>
		<div id="iptablecollapse" class="panel-collapse {IPTABLEOPEN}">
			<div class="panel-body">
				{IPTABLE}
			</div>
		</div>
	</div>
	<div class="panel panel-default" id="contable">
		<div class="panel-heading" >
			<h4 class="panel-title" >
				<button class="btn btn-default btn-xs" data-toggle="collapse" data-parent="contable" href="#contablecollapse" role="button">View Connections</button>
			</h4>
		</div>
		<div id="contablecollapse" class="panel-collapse collapse">
			<div class="panel-body">
				{CONTABLE}
			</div>
		</div>
	</div>
</div>