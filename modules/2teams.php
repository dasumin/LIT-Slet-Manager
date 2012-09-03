<?php
// ItIsSletManagerModule

$module = 'teams';

if ( !defined("RequestModule") || ( RequestModule !== 'core' && RequestModule !== 'team-ajax' ) ) die;

$modules[$module]['name'] = 'Управление звеньями'; // человеческое название модуля

$modules[$module]['action'][] = 'add_team';
$modules[$module]['menu'][] = 'Добавить звено';
$modules[$module]['title'][] = 'Новое звено';

$modules[$module]['action'][] = 'team_list';
$modules[$module]['menu'][] = 'Список звеньев';
$modules[$module]['title'][] = 'Звенья';

$modules[$module]['action'][] = 'show_team';
#$modules[$module]['menu'][] = 'Список звена';
$modules[$module]['title'][] = 'Список звена';

$modules[$module]['action'][] = 'show_team_photos';
#$modules[$module]['menu'][] = 'Список звена';
$modules[$module]['title'][] = 'Список звена';

$modules[$module]['action'][] = 'edit_team';
#$modules[$module]['menu'][] = 'Список звена';
$modules[$module]['title'][] = 'Редактирование тройки';

$modules[$module]['action'][] = 'delete_team';
#$modules[$module]['menu'][] = 'Список звена';
$modules[$module]['title'][] = 'Удаление звена';

$modules[$module]['groups'][] = 'admin';
$modules[$module]['groups'][] = 'leader';

function show_teams ($action) {
	global $modules, $user, $participantlist, $litgroupSet;	
	$module = $modules['teams'];
	
	$userIsAdmin = FALSE;
	foreach (@$user['group'] as $key=>$value) {
		if ($value=='admin') $userIsAdmin = TRUE;
	}
	
	switch ($action) {
// Добавить звено
		case 'add_team':
			
			if ( isset ($_POST[$action]) ) {
				if ($userIsAdmin) {
					$newTeam = saveTeam ( 0, $_POST['leader'], $_POST['graduate'], $_POST['teacher'] );
					echo '
					<p>Звено успешно добавлено</p>
					<script language="javascript">
						setTimeout("location.href=\''.$_SERVER['PHP_SELF'].'?action=show_team&team='.$newTeam.'\'", 500);
					</script>
					';
				}
				else report_error ("У вас недостаточно прав для добавления звена");
			}
			
			else {
				echo '
				<div>
				<form method="post" action="'.$_SERVER["PHP_SELF"].'?action='.$action.'">
				<p>
				<table class="userinfo">
					<tr>
						<td>Звеньевой</td> 
						<td><select name="leader" size="1">
				';
			
				foreach ( $litgroupSet as $key=>$value ) {
					if ( $value == 'Выпускник' || $value == 'Преподаватель') {}
					else {
						echo '<optgroup label="'.$value.'">';
						foreach ( formParticipantArray('surname', 'ASC', '', $value)  as $key1=>$value1) {
							//if ($value == @$litgroup) $atr = 'selected';
							//	else $atr = '';
							echo '<option value="'.$value1['id'].'">'.$value1['surname'].' '.$value1['name'].'</option>';
						}
						echo '</optgroup>';
					}
				}
					
				echo '
						</select></td>
					</tr>	
					<tr>
						<td>Выпускник</td> 
						<td><select name="graduate" size="1"><option value="">нет</option>
				';
			
				foreach ( $litgroupSet as $key=>$value ) {
					if ( $value !== 'Выпускник' && $value !== 'Преподаватель') {}
					else {
						echo '<optgroup label="'.$value.'">';
						foreach ( formParticipantArray('surname', 'ASC', '', $value)  as $key1=>$value1) {
							//if ($value == @$litgroup) $atr = 'selected';
							//	else $atr = '';
							echo '<option value="'.$value1['id'].'">'.$value1['surname'].' '.$value1['name'].'</option>';
						}
						echo '</optgroup>';
					}
				}
					
				echo '
						</select></td>
					</tr>
					<tr><td>Преподаватель</td> 
						<td><select name="teacher" size="1"><option value="">нет</option>
				';
			
				foreach ( $litgroupSet as $key=>$value ) {
					if ( $value !== 'Выпускник' && $value !== 'Преподаватель') {}
					else {
						echo '<optgroup label="'.$value.'">';
						foreach ( formParticipantArray('surname', 'ASC', '', $value)  as $key1=>$value1) {
							//if ($value == @$litgroup) $atr = 'selected';
							//	else $atr = '';
							echo '<option value="'.$value1['id'].'">'.$value1['surname'].' '.$value1['name'].'</option>';
						}
						echo '</optgroup>';
					}
				}
					
				echo '
						</select></td>
					</tr>
					<tr><td>&nbsp;</td> 
						<td> <input type="submit" name="'.$action.'" value="Добавить" /></td>
					</tr>
				</table></p>
				</form>
				</div>';
			}
			
			break;
			
		case 'team_list':
			$teams = formTeamsArray();
			echo '
			
			<script language="javascript" type="text/javascript">
				function highlight(id) {
					document.getElementById(id).style.background = "#eee";
				}
				function nohighlight(id) {
					document.getElementById(id).style.background = "white";
				}
				function viewInfo(id) {
					location.href = "?action=show_team&team="+id;
				}
			</script>
			
			<style type="text/css">
				table th, table td {
					padding-left: 8px;
					padding-right: 6px;
				}
			</style>
			
			<table style="font-size: 90%; white-space:nowrap;">
				<tr>
					<th rowspan="2">&nbsp;</th>
					<th rowspan="2">Звеньевой</th>
					<th rowspan="2">Выпускник</th>
					<th rowspan="2" style="border-right-color: #444;">Преподаватель</th>
					<th rowspan="2" style="border-right-color: #444; font-size: 70%;">Кол-во<br />человек</th>
					<th colspan="7" style="font-size: 70%; text-align:center">лицеисты</th>
					
				</tr>
				<tr>
					<th style="text-align: center;">8</th>
					<th style="text-align: center;">9</th>
					<th style="text-align: center;">10</th>
					<th style="text-align: center;">11</th>
					<th style="text-align: center;">&sum;</th>
					<th style="text-align: center;">М</th>
					<th style="text-align: center;">Ж</th>
				</tr>
			';
			$sum = array('num_people'=>0,'num_8'=>0,'num_9'=>0,'num_10'=>0,'num_11'=>0,'num_students'=>0,'num_m'=>0,'num_f'=>0);
			
			foreach ($teams as $key=>$value) {
				if ( $value['id'] != 0 ) {
					echo '
				<tr style="cursor:pointer" onclick="viewInfo(\''.$value['id'].'\')" id="'.$value['id'].'" onmouseover="highlight(\''.$value['id'].'\')" onmouseout="nohighlight(\''.$value['id'].'\')">
					<td align="right">'.$value['id'].'</td>
					<td>'.$value['leader'].'</td>
					<td>'.@$value['graduate'].'</td>
					<td style="border-right-color: #444;">'.@$value['teacher'].'</td>
					<td style="border-right-color: #444; text-align: center;">'.$value['num_people'].'</td>
					<td style="text-align: center;">'.$value['num_8'].'</td>
					<td style="text-align: center;">'.$value['num_9'].'</td>
					<td style="text-align: center;">'.$value['num_10'].'</td>
					<td style="text-align: center;">'.$value['num_11'].'</td>
					<td style="text-align: center;">'.$value['num_students'].'</td>
					<td style="text-align: center;">'.$value['num_m'].'</td>
					<td style="text-align: center;">'.$value['num_f'].'</td>
				</tr>
					';
				}
				foreach ($sum as $key1=>$value1) {
					@$sum[$key1]+=$value[$key1];
				}
			}
			echo '
				<tr style="border-top: #444;">
					<td></td>
					<td></td>
					<td></td>
					<td style="border-right-color: #444;"></td>
					<td style="border-right-color: #444; text-align: center;">'.$sum['num_people'].'</td>
					<td style="text-align: center;">'.$sum['num_8'].'</td>
					<td style="text-align: center;">'.$sum['num_9'].'</td>
					<td style="text-align: center;">'.$sum['num_10'].'</td>
					<td style="text-align: center;">'.$sum['num_11'].'</td>
					<td style="text-align: center;">'.$sum['num_students'].'</td>
					<td style="text-align: center;">'.$sum['num_m'].'</td>
					<td style="text-align: center;">'.$sum['num_f'].'</td>
				</tr>
			';
			echo '
			</table>
			';
			
			break;
			
		case 'show_team':
			$participantlist = TRUE;
			
			if ( isset ($_GET['team']) ) {
				$team = $_GET['team'];
				
				echo '<script type="text/javascript">
				function participant(id) {
					TeamManage ( id, "add" )
				}
			
				function TeamManage ( id, action ) {
					httpObjectTeam = getHTTPObject();
					if (httpObjectTeam != null) {
						httpObjectTeam.open("GET", "./ajax/teams.php?team='.$team.'&"+action+"="+id, true);
						httpObjectTeam.send(null);
						httpObjectTeam.onreadystatechange = function () {
							if(httpObjectTeam.readyState == 4) {
								res = httpObjectTeam.responseText;
								document.getElementById("main").innerHTML = \'<h1>Список звена</h1>\' + res;
							}
						}
					}
				}
				
				function highlight(id) {
					document.getElementById(id).style.background = "#eee";
				}
				function nohighlight(id) {
					document.getElementById(id).style.background = "white";
				}
				function viewInfo(id) {
					location.href = "?action=participant_info&id="+id+"&fromlist";
				}				
			</script>';

				$members = formTeamArray ( $team );
				
				echo '<h2>Звено '.$team.'</h2>
			<p style="font-size: 80%;">редактирование списка | <a href="?action=show_team_photos&team='.@$_GET['team'].'">звено в лицах</a></p>
			<table>
				';
				$k = 1;
				for ($i=0; $i<count($members); $i++) {
					
					if ( $members[$i] != 0 ) {
						$member = get_participant_info( $members[$i] );
					
						if ($i > 2) $delete = '<a style="color:red" href="javascript:TeamManage(\''.$member['id'].'\', \'delete\')">X</a>';
						else $delete = '<span style="color:gray">X</span>';
						
						echo '
				<tr style="cursor:pointer" id="'.$member['id'].'" onmouseover="highlight(\''.$member['id'].'\')" onmouseout="nohighlight(\''.$member['id'].'\')">
					<td align="right" onclick="viewInfo('.$member['id'].')">'.$k.'</td>
					<td onclick="viewInfo('.$member['id'].')">'.$member['surname'].' '.$member['name'].'</td>
					<td onclick="viewInfo('.$member['id'].')">'.$member['litgroup'].'</td>
					<td>'.$delete.'</td>
				</tr>
						';
						$k++;
					}
				}
				echo '
				</table>
				';
				$teams = formTeamsArray();
				echo '
				<h3 style="margin-bottom: 5px;">Информация о звене:</h3>
				<table style="float: left; font-size: 80%; margin-bottom: 20px; margin-right: 20px; text-align: center;">
					<tr>
						<th>Лицеистов</th><th>8</th><th>9</th><th>10</th><th>11</th><th>М</th><th>Ж</th>
					</tr>
					<tr>
						<td>'.$teams[$team]['num_students'].'</td><td>'.$teams[$team]['num_8'].'</td><td>'.$teams[$team]['num_9'].'</td><td>'.$teams[$team]['num_10'].'</td><td>'.$teams[$team]['num_11'].'</td><td>'.$teams[$team]['num_m'].'</td><td>'.$teams[$team]['num_f'].'</td>
					</tr>
				</table>
				<p style="clear: both"><a href="?action=edit_team&team='.$team.'"><i>Редактировать</a> &laquo;тройку&raquo;</i></p>
				';
			}
			break;
			
		case 'show_team_photos':
			
			if ( isset ($_GET['team']) ) {
				$team = $_GET['team'];

				$members = formTeamArray ( $team );
				
				echo '<h2>Звено '.$team.'</h2>
				<p style="font-size: 80%;"><a href="?action=show_team&team='.@$_GET['team'].'">редактирование списка</a> | звено в лицах</p>
				<div>';
				
				for ($i=0; $i<count($members); $i++) {
					
					if ( $members[$i] != 0 ) {
						$member = get_participant_info( $members[$i], 1 );
						
						echo '
						<div style="float: left; font-size: 90%; height: 230px; text-align: center; width: 150px;">
							<img src="'.$member['photo_url'].'" style="max-height: 150px; max-width: 125px;" /><br />
							'.$member['name'].'<br />
							'.$member['surname'].'<br />
							<span style="font-size: 70%; position: relative; top: -5px;">'.$member['litgroup'].'</span>
						</div>
						';
					}
				}
				echo '</div>';				
			}
			break;
			
		case 'edit_team':
			
			if ( isset ($_POST[$action]) && $_POST['team']>0) {
				if ( !$userIsAdmin && $_POST['team'] != $user['team'] ) report_error ("Вы можете редактировать список только своего звена");
				else {
					saveTeam ( $_POST['team'], $_POST['leader'], $_POST['graduate'], $_POST['teacher'] );
					echo '
					<p>Звено успешно сохранено</p>
					<script language="javascript">
						setTimeout("location.href=\''.$_SERVER['PHP_SELF'].'?action=show_team&team='.$_POST['team'].'\'", 500);
					</script>
					';
				}
			}
			
			else {
				$teams = formTeamArray($_GET['team']);
			
				echo '
				<div>
				<form method="post" action="'.$_SERVER["PHP_SELF"].'?action='.$action.'">
				<p>
				<table class="userinfo">
					<tr>
					<td>Звеньевой</td> 
					<td><select name="leader" size="1">
			';
			
			foreach ( $litgroupSet as $key=>$value ) {
				if ( $value == 'Выпускник' || $value == 'Преподаватель') {}
				else {
					echo '<optgroup label="'.$value.'">';
					foreach ( formParticipantArray('surname', 'ASC', '', $value)  as $key1=>$value1) {
						if ($value1['id'] == @$teams[0]) $atr = 'selected';
							else $atr = '';
						echo '<option value="'.$value1['id'].'" '.$atr.'>'.$value1['surname'].' '.$value1['name'].'</option>';
					}
					echo '</optgroup>';
				}
			}
					
			echo '
					</select></td>
				</tr>	
				<tr>
					<td>Выпускник</td> 
					<td><select name="graduate" size="1"><option value="">нет</option>
			';
			
			foreach ( $litgroupSet as $key=>$value ) {
				if ( $value !== 'Выпускник' && $value !== 'Преподаватель') {}
				else {
					echo '<optgroup label="'.$value.'">';
					foreach ( formParticipantArray('surname', 'ASC', '', $value)  as $key1=>$value1) {
						if ($value1['id'] == @$teams[1]) $atr = 'selected';
							else $atr = '';
						echo '<option value="'.$value1['id'].'" '.$atr.'>'.$value1['surname'].' '.$value1['name'].'</option>';
					}
					echo '</optgroup>';
				}
			}
					
			echo '
					</select></td>
				</tr>
				<tr><td>Преподаватель</td> 
					<td><select name="teacher" size="1"><option value="">нет</option>
			';
			
			foreach ( $litgroupSet as $key=>$value ) {
				if ( $value !== 'Выпускник' && $value !== 'Преподаватель') {}
				else {
					echo '<optgroup label="'.$value.'">';
					foreach ( formParticipantArray('surname', 'ASC', '', $value)  as $key1=>$value1) {
						if ($value1['id'] == @$teams[2]) $atr = 'selected';
							else $atr = '';
						echo '<option value="'.$value1['id'].'" '.$atr.'>'.$value1['surname'].' '.$value1['name'].'</option>';
					}
					echo '</optgroup>';
				}
			}
					
			echo '
					</select></td>
				</tr>
					<tr><td><input type="hidden" name="team" value="'.$_GET['team'].'" /></td> 
						<td> <input type="submit" name="'.$action.'" value="Сохранить" /></td>
					</tr>
				</table></p>
				</form>
				</div>
			';
			if ($userIsAdmin) echo '
				<p><a href="?action=delete_team&team='.$_GET['team'].'"><i>Удалить звено</i></a></p>';
			}
			
			break;
			
		case 'delete_team':
			
			if ( isset ($_POST[$action]) && $_POST['team']>0) {
				deleteTeam ( $_POST['team'] );
				echo '
				<p>Звено успешно удалено</p>
				<script language="javascript">
					setTimeout("location.href=\''.$_SERVER['PHP_SELF'].'?action=team_list\'", 500);
				</script>
				';
			}
			
			else {			
				echo '
				<p>Вы уверены, что хотите удалить '.$_GET['team'].' звено?<br />Восстановление будет невозможно</p>
				<div>
				<form method="post" action="'.$_SERVER["PHP_SELF"].'?action='.$action.'">
				<input type="hidden" name="team" value="'.$_GET['team'].'" /><input type="submit" name="'.$action.'" value="Удалить" />
				</form>
				</div>
				';
			}
			
			break;
	}
}

/////////////////////////////////////////////////////////////////////////////////////////////////
?>
