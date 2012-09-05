<?php
// ItIsSletManagerModule

$module = 'participantlists';

if ( !defined("RequestModule") || ( RequestModule !== 'core' && RequestModule !== 'participantlist' ) ) die;

$modules[$module]['name'] = 'Списки';

$modules[$module]['action'][] = 'show_participant_list';
$modules[$module]['menu'][] = 'Список всех лицеистов';
$modules[$module]['title'][] = 'Лицеисты, преподаватели, выпускники';

$modules[$module]['action'][] = 'participant_info';
#$modules[$module]['menu'][] = 'Информация об участнике';
$modules[$module]['title'][] = 'Информация об участнике';

//$modules[$module]['groups'][] = 'guest'; // группы, которым разрешено пользоваться модулем
$modules[$module]['groups'][] = 'admin';
$modules[$module]['groups'][] = 'registrar';
$modules[$module]['groups'][] = 'leader';

function show_participantlists ( $action ) {
	global $modules, $user, $participantlist, $litgroupSet;	
	$module = $modules['participantlists'];

	switch ($action) {
		case $module['action'][0]: // Посмотреть список участников слета
		
			if ( RequestModule == 'core' && check_user_access ('participantlists', $user) )  { 
				$field = array ( 'surname'=>'Фамилия', 'name'=>'Имя', 'litgroup'=>'Группа', 'team'=>'Звено' ); }
			else $field = array ( 'surname'=>'Фамилия', 'name'=>'Имя', 'litgroup'=>'' );
		
			if ( empty ($_GET['sortField']) ) $sortField = 'surname'; else $sortField = $_GET['sortField'];
			if ( empty ($_GET['sortDir']) ) $sortDir = 'ASC'; else $sortDir = $_GET['sortDir'];
			if ( empty ($_GET['filterSurname']) ) $filterSurname = ''; else $filterSurname = $_GET['filterSurname'];
			if ( empty ($_GET['filterGroup']) ) $filterGroup = ''; else $filterGroup = $_GET['filterGroup'];
			
			if ( isset($_GET['blacklist'])) $blacklist = '1'; else $blacklist = '0';
			$users = formParticipantArray ( $sortField, $sortDir, $filterSurname, $filterGroup, $blacklist );			
// Начало фильтров			
			$alphabet = array
				('А','Б','В','Г','Д','Е','Ж','З','И','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Э','Ю','Я');
			$group = $litgroupSet;
				
			echo "<script>
				function hidefilters() {
					window.document.getElementById('filters').style.display = 'none';
					window.document.getElementById('hfilters').style.display = 'block';
				}

				function showfilters() {
					window.document.getElementById('filters').style.display = 'block';
					window.document.getElementById('hfilters').style.display = 'none';
				}
			</script>";
				
			echo '<div id="hfilters"><a href="javascript:showfilters()">Фильтры</a> 
			<a href="'.$_SERVER["PHP_SELF"].'?action='.$action.'" style="font-size: 75%;">Сбросить</a><span style="font-size: 75%;"> все фильтры</span></div>
			<div id="filters">
			<a href="javascript:hidefilters()">Фильтры</a> 
			<a href="'.$_SERVER["PHP_SELF"].'?action='.$action.'" style="font-size: 75%;">Сбросить</a><span style="font-size: 75%;"> все фильтры</span>		
			<div>
			<p>Фамилия:<br />';
				
			for ($i=0; $i<count($alphabet); $i++)
			echo '
				<a href="'.$_SERVER["PHP_SELF"].'?action='.$action.'&sortField='.$sortField.'
				&sortDir='.$sortDir.'&filterSurname='.$alphabet[$i].'&filterGroup='.$filterGroup.'">'.$alphabet[$i].'</a> ';
			echo '
			</p><p>
			Группа:<br />';
			
			for ($i=0; $i<count($group); $i++) {
			echo '
				<a href="'.$_SERVER["PHP_SELF"].'?action='.$action.'&sortField='.$sortField.'
				&sortDir='.$sortDir.'&filterSurname='.$filterSurname.'&filterGroup='.$group[$i].'">'.$group[$i].'</a> ';
				
			if ( !empty ($group[$i+1]) && ( ( $group[$i][0] != '1' && $group[$i][0] != $group[$i+1][0] ) || ( $group[$i][1] != $group[$i+1][1] ) ) ) echo '<br />';
			}

			echo '
			<br /><a href="'.$_SERVER["PHP_SELF"].'?action='.$action.'&blacklist">Чёрный список</a>
			</p>
			</div>
			</div>';
// Конец фильтров
// Начало вывода таблицы
			echo '
			<script language="javascript" type="text/javascript">
				function highlight(id) {
					document.getElementById(id).style.background = "#eee";
				}
				function nohighlight(id) {
					document.getElementById(id).style.background = "white";
				}
				function viewInfo(id) {
					location.href = "?action=participant_info&id="+id+"&fromlist";
				}
			</script>
			
			<table id="participanttable">
				<tr>';
			if ( RequestModule == 'participantlist' ) echo '
					<th></th>
				';
				
			foreach ($field as $f=>$text) {
				echo '
					<th>'.$text.' 
					<a title="По возрастанию" href="'.$_SERVER["PHP_SELF"].'?action='.$action.'&sortField='.$f.'&sortDir=ASC&filterSurname='.$filterSurname.'&filterGroup='.$filterGroup.'" class="sort-arrow">&uarr;</a><a title="По убыванию" href="'.$_SERVER["PHP_SELF"].'?action='.$action.'&sortField='.$f.'&sortDir=DESC&filterSurname='.$filterSurname.'&filterGroup='.$filterGroup.'" class="sort-arrow">&darr;</a></th>
			';
			}
			echo '</tr>';
			
			if ( count ($users) == 0 ) echo '<tr><td colspan="'.count ($field).'">Ничего не найдено</td></tr>';
			foreach ($users as $key=>$user) {
				echo '<tr style="cursor:pointer" id="'.$user['id'].'" onmouseover="highlight(\''.$user['id'].'\')" onmouseout="nohighlight(\''.$user['id'].'\')">';
				if ( RequestModule == 'participantlist' ) echo '
					<td align="center"><a href="javascript:parent.participant(\''.$user['id'].'\')"><</a></td>
				';
				foreach ($field as $f=>$text) {
					if ( $f=='litgroup' || $f=='team' ) $align='right'; elseif ( $f=='id' ) $align='center'; else $align='left';
					
					if ($f == 'litgroup' && $user[$f] == 'Преподаватель') echo '<td align="'.$align.'">П</td>'."\n";
					 elseif ($f == 'litgroup' && $user[$f] == 'Выпускник') echo '<td align="'.$align.'">В</td>'."\n";					
					  else {
						echo '<td align="'.$align.'" onclick="viewInfo('.$user['id'].')">';
						if ($f == 'litgroup' && $user['blacklist'] == 1) {
							echo '<span style="background: black; color: white; font-size: 10px;">&nbsp;Ч&nbsp;</span>';
						}
						echo $user[$f];
						echo '</td>'."\n";
					}
				}
				echo '</tr>';
			}
			echo '</table>';
// Конец вывода таблицы
			break;
			
			
		// Информация об участнике
		case 'participant_info':
			$participantlist = TRUE;
			
			echo '
			<script type="text/javascript">
				function participant(id) {
					document.getElementById(\'id\').value=id;
				}			
			</script>	
			';
			
			if ( ( isset ($_POST['id']) && $id = $_POST['id'] ) || ( isset ($_GET['id']) && $id = $_GET['id']  ) ) {
				if ( isset ($_GET['fromlist']) ) echo '<p><a href="javascript:history.back()">Вернуться к таблице</a></p>';
				echo '<p>';
				print_participant_info ( $id ) ;
				if (check_user_access('manage_participants')) echo '<p style="text-align:center;"><a href="?action=edit_participant&id='.$id.'"><i>изменить информацию</i></a></p>'."\n";
				echo '</p>';				
				echo '<p style="clear: both;"></p><p style="clear: both; margin: 40px 0 0 0;"><i>Другой участник</i></p>';
			}
			
			echo '
			<form method="post" action="'.$_SERVER["PHP_SELF"].'?action='.$action.'">
			<p style="margin-top: 0">Введите id участника:<br />			
				<input type="text" name="id" id="id" />
				<input type="submit" name="'.$action.'" value="Вывести информацию">
			</p>
			</form>
			';
			break;
			
		
	}
}
?>
