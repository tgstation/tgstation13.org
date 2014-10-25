<!doctype html>
<html>
<head>
	<title>{PAGE_TITLE}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!--[if lt IE 9]>
    <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
	<link href="tg.css" rel="stylesheet"/>
	<style type="text/css">
		/*can't remember why I added this or what it does*/
		.container {
			margin: 0 auto;
			max-width: 960px;
		}
		/*removes bootstraps top border when floated.(for prettiness)*/
		/*
		thead {
			-webkit-border-top-style: solid;
			-webkit-border-top-width: 2px;
			-webkit-border-top-color: inherit;
		}
		table.floatThead-table thead {
			border-top: none;
		}
		
		table.floatthead tr.size-row {
		z-index: 1002;
		border-top-style: solid;
		border-top-width: 1px;
		border-top-color: inherit;
		}
		table.floatThead-table {
			border-top: none;
		}
		*/
		/*fixes theads having transparent backgrounds when floated(because of bootstrap)
			also makes table backgrounds static white regardless of background of containing element.*/
		table {
			background-color: #FFF;
		}
		/*makes panels less blocky*/
		.panel-heading {
			padding:10px;
		}
		.panel-body {
			padding: 10px;
		}
		table th {
		padding:0px !important;
		text-align:center !important;
		}
		table td {
		text-align:center !important;
		}
		table a { /*style links inside of tables*/
			text-decoration: underline;
			color:inherit;
		}
		table.inlinetable {
			display:inline-table;
			align:center;
		}
		table.inlinetable td {
			text-align: center;
			display: table-row;
		}
		table.inlinetable tr {
			display:inline-block;
			padding:10px;
		}

	</style>
</head>
<body>
	{HEADER}
	<div class="container-fluid">
	{#IFNDEF:DEBUG}
	<div class="alert alert-info">
			<strong>Hey! Listen!</strong>
				Please report any bugs or display quirks <a href="/phpBB/viewtopic.php?t=1110">here</a>
		</div>{#ENDIF}
	{CONTENT}
	</div>
	{FOOTER}
</body>
<html>