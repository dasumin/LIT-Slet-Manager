<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link type="text/css" rel="stylesheet" media="all" href="style/style.css" />
		<style type="text/css">
		body {
			background: transparent;
			margin: 0; padding: 0;
			width: 330px;
		}
		body * {
			font-size: 11pt;

		}
		#filters, #hfilters {
			background: #fff;
			top:0px;
			font-size: 13pt;
			right:1px;
			left:0px;
			margin: 0px;
			padding-top: 5px;
			position: fixed !important;
			position: absolute;
			z-index: 10;
			width: 100%;
		}
		#participanttable {
			position: absolute;
			top:34px;
			left: 1px !important;
			left: 0px;
			min-width: 325px;
			width: 330px;
		}
		#note {
			background: #fff;
			bottom:0px;
			font-size: 11pt;
			left:0px;
			right:1px;
			margin: 0px;
			padding-top: 5px;
			position: absolute;
			position: fixed;
			text-align: center;
			z-index: 10;
		}
		</style>

		<script type="text/javascript">
			function SetId (id) {
				parent.document.getElementById('participant_id').value = id ;
			}
		</script>
	</head>
	<body>

	<div style="display:none">
<?php
include ("./config.php");
include ("./functions.php");

if (!mysql_connect($mysql_server,$mysql_user,$mysql_password))
	report_error ('Не удалось подключиться к серверу баз данных.');

if (!mysql_select_db($mysql_db))
	report_error ('Не удалось подключиться к базе данных.');

define ("RequestModule", 'participantlist');
include ("./modules/1participantlists.php");
echo '</div>';

if (empty($_GET['action'])) $action = 'show_participant_list';
	else $action = $_GET['action'];

show_participantlists ( $action );

?>
	</body>
</html>
