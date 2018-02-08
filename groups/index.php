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
		<title>Слет&ndash;<?php echo $year; ?>: списки звеньев</title>
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
		<h1>Слет&ndash;<?php echo $year; ?>: списки по группам</h1>
<?php
$teams = formTeamsArray();
foreach ($litgroupSet as $study_group) {

	$q = mysql_query ("
		SELECT * FROM `participants`
		WHERE `litgroup` = '$study_group' AND `team` != 0
		ORDER BY 'surname' ASC;
	");
	if (mysql_num_rows ($q) == 0) {
		continue;
	}

	echo '
		<h2>'.$study_group.'</h2>

		<table>
	';

	for ($i=1; $i <= mysql_num_rows ($q); $i++) {
		$f = mysql_fetch_array($q);

		$leader_s = ($teams[$f['team']]['leader']) ? ' ('.$teams[$f['team']]['leader'].')' : '';

		echo '
			<tr>
				<td style="text-align: right; padding-right: 5px;">'.$i.'.</td>
				<td style="min-width: 250px;">'.$f['surname'].' '.$f['name'].'</td>
				<td>Звено '.$f['team'].$leader_s.'</td>
			</tr>
			';
	}

	echo '
		</table>
	';
}

?>
	</body>
</html>
