Hello {USERCKEY} ({USERRANK}).
<hr/>
<div class="container">
	<div class="panel panel-default" id="memotable">
		<div class="panel-heading">
			<h4 class="panel-title">
				<button class="btn btn-default btn-xs" data-toggle="collapse" data-parent="memotable" href="#memotablecollapse" role="button">
					Admin Memos ({MEMOCOUNT})
				</button>
			</h4>
		</div>
		<div class="memotablecollapse" class="panel-collapse {MEMOTABLEOPEN}">
			<div class="panel-body">
				<table class="table table-bordered table-hover table-condensed table-striped floatthead">
					<thead>
						<th style="min-width:170px;padding:0px;text-align:center">Details</th>
						<th style="padding:0px;text-align:center">Memo</th>
					</thead>
					<tbody>
						{#ARRAY:MEMOS}
							<tr>
								<td style="text-overflow:nowrap;overflow:nowrap;padding:0px">
									{DATE}<br/>
									<b>{ADMIN}</b><br/>
								</td>
								<td style="padding:0px">{MEMO}</td>
							</tr>
						{#ENDIF}
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>