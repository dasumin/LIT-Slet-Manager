<?php
include ("../config.php");
include ("../functions.php");

if (!mysql_connect($mysql_server,$mysql_user,$mysql_password)) 
	report_error ('Не удалось подключиться к серверу баз данных.');
	
if (!mysql_select_db($mysql_db)) 
	report_error ('Не удалось подключиться к базе данных.');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Слет&ndash;2013: списки звеньев</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<style type="text/css">
			body {
				font-family: Times New Roman;
				margin: 0;
				padding: 50px 150px 200px;
			}
			td {
				padding-right: 10px;
			}
			h1 {
				margin-bottom: 30px;
			}
			h2 {
				margin: 20px auto 5px;
			}
		</style>
		<style type="text/css" media="print, paged">
			body {
				padding-left: 30px;
			}
			h1 {
				page-break-after: avoid;
			}
			.noprint { display: none; }
		</style>
	</head>
	<body>
		<h1>Слет&ndash;2013: списки звеньев</h1>
<?php
$teams = formTeamsArray();
foreach ($teams as $key=>$value) {
	if ( $value['id'] != 0 ) {			
		$team = $value['id'];					
				
		$members = formTeamArray ( $team );
				
		echo '
		<h2>Звено '.$team.'</h2>
		
		<table>
		';
		$k = 1;
		for ($i=0; $i<count($members); $i++) {
			if ( $members[$i] != 0 ) {
			$participant = get_participant_info( $members[$i] );
					
			if ($i > 2) $delete = '<a style="color:red" href="javascript:TeamManage(\''.$participant['id'].'\', \'delete\')">X</a>';
				else $delete = '<span style="color:gray">X</span>';
					
			echo '
			<tr>
				<td style="text-align: right; padding-right: 5px;">'.$k.'.</td>
				<td style="min-width: 250px;">'.$participant['surname'].' '.$participant['name'].'</td>
				<td>'.$participant['litgroup'].'</td>
			</tr>
			';
			$k++;
			}
		}
		echo '
		</table>
		';
	}
}		

?>
	</body>
</html>
