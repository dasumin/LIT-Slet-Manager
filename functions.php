<?php

// Проверяет пару <логин:пароль> 
// Возвращает TRUE/FALSE в случае удачного/неудачного выполнения
// Аргументы: логин, password=пароль
function check_password ($userid, $password) {
	mysql_query ("START TRANSACTION;");
	
	$time = 10; $num = 3;
	$q = mysql_query ("SELECT * FROM `logs_logins`
			WHERE `userid`='$userid' AND
			`timestamp` > DATE_SUB( now( ) , INTERVAL $time MINUTE ) AND 
			`success` != '1' AND
			`ip` = '$_SERVER[REMOTE_ADDR]'
			ORDER BY `timestamp`  DESC;			
	");
	if( mysql_num_rows ($q) >= $num ) {
		for ($i = 0; $i < $num; $i++) $f = mysql_fetch_array($q);
		report_error( "
			С этого компьютера за последние $time минут совершено более $num неудачных попыток войти в систему, используя логин $userid<br />
			Доступ к этому логину с этого компьютера временно заблокирован.<br />
			Блокировка автоматически закончится в ".date ( 'H:i:s', strtotime ($f['timestamp'])+$time*60 ) );
		return FALSE;
	}
	
	$time = 10; $num = 10;
	$q = mysql_query ("SELECT * FROM `logs_logins`
			WHERE `userid`='$userid' AND
			`timestamp` > DATE_SUB( now( ) , INTERVAL $time MINUTE ) AND 
			`success` != '1'
			ORDER BY `timestamp`  DESC;			
	");
	if( mysql_num_rows ($q) >= $num ) {
		for ($i = 0; $i < $num; $i++) $f = mysql_fetch_array($q);
		report_error( "
			За последние $time минут совершено более $num неудачных попыток войти в систему, используя логин $userid<br />
			Доступ к логину временно заблокирован.<br />
			Блокировка автоматически закончится в ".date ( 'H:i:s', strtotime ($f['timestamp'])+$time*60 ) );
		return FALSE;
	}
	
	$q = mysql_query("SELECT * FROM `users` WHERE `userid`='$userid'");
	
	if( mysql_num_rows ($q) == 0 ) {
		report_error( "Логин не найден" );
		return FALSE;
	}
	
	$f = mysql_fetch_array($q);
		if (crypt ( $password, $f['hash'] ) == $f['hash'] ) $success = TRUE;
		else $success = FALSE;	
	
	
	if ( !mysql_query("INSERT INTO `logs_logins` (`userid`,`ip`,`success`) VALUES ('$userid','$_SERVER[REMOTE_ADDR]','$success')") ) {
		report_error( "Произошла ошибка записи информации в логи. Пожалуйста, попробуйте войти еще раз" );
		return FALSE;
	}
	
	if (!mysql_query ("COMMIT;")) {
		report_error ('Произошла ошибка во время выполнения запроса. Пожалуйста, попробуйте еще раз');
		return FALSE;
	}
	
	if (!$success) report_error("Неправильный пароль.");
	return $success;
}

// Добавляет ошибку в общий массив ошибок
// Возвращает TRUE/FALSE в случае удачного/неудачного выполнения
// Аргументы: problem=описание ошибки
function report_error($problem) {
	global $errors, $user;
	$errors[] = $problem;
	
	if (empty($user['id'])) $id = '0'; else $id = $user['id'];		
	$problemtolog = explode ('<br />', $problem);
	$problemtolog = trim(strip_tags ($problemtolog[0]));	
	mysql_query ("START TRANSACTION;");
	mysql_query ("
	INSERT INTO `logs_errors` (`userid` , `error` , `ip`)
	VALUES ('$id', '$problemtolog', '$_SERVER[REMOTE_ADDR]');");
	mysql_query ("COMMIT;");
	
	echo '</div>';
	print_errors(); die();
	
	return TRUE;
}

// Выводит все зарегистрированные ошибки
// Возвращает TRUE/FALSE в случае удачного/неудачного выполнения
// Аргументов нет
function print_errors() {
	global $errors;
	if(isset($errors[0])) {
		mysql_query ("ROLLBACK;");
		echo "\n".'<div id="errors">';
		echo 'Произошли ошибки:
		<ul>';
		foreach ($errors as $key=>$value) {
			echo '<li>'.$value."</li>\n";
		}
		echo '</ul>
		<p><a href="javascript:history.back()"><i>Назад</i></a></p>
		<p>Обо всех ошибках работы системы сообщайте, пожалуйста, Денису Сумину (<a href="mailto:denis@304.ru">denis@304.ru</a>)</p>
		</div>';
		@print_footer();
		die();
	}
	return TRUE;
}

function get_participant_info ($id, $fetch_external=0) {

	$userid = $id;
	$id = idUserid ('id', $id);

	$q = mysql_query("SELECT * FROM `participants` WHERE `id`='$id'");
	if ( mysql_num_rows($q) == 0 ) { report_error("Пользователь не найден"); return FALSE; }

	$f = mysql_fetch_array($q);
	$participant['id'] = $f['id'];
	$participant['userid'] = $userid;
	
	$participant['name'] = $f['name'];
	$participant['surname'] = $f['surname'];
	$participant['litgroup'] = $f['litgroup'];
	$participant['team'] = $f['team'];
	if ($f['photo_url']=='' && $fetch_external) 
		$participant['photo_url'] = getInfoFromPeople (
			$participant['name'], $participant['surname'],
			$participant['litgroup'], 'photo_url' );
	else $participant['photo_url'] = $f['photo_url'];
	$participant['blacklist'] = $f['blacklist'];
	$participant['sex'] = $f['sex'];	

	$userid = idUserid ('userid', $id);
	$q = mysql_query("SELECT * FROM `usersgroup` WHERE userid='$userid'");
	for ($i=0; $i<mysql_num_rows($q); $i++) {
		$f = mysql_fetch_array($q);
		$participant['group'][] = $f['group'];
	}
	$participant['group'][] = 'guest';
	
	return $participant;
}

function print_participant_info ($id) {

	if ( !($participant = get_participant_info ($id, 1)) ) { return FALSE; }
	
	$arg = func_get_args();
	
	if ($participant['team'] != 0) {
		foreach (formTeamsArray() as $key=>$value) {
			if ($value['id'] == $participant['team']) $team_leader = $value['leader'];
		}
		$team_output = '<a href="?action=show_team&team='.$participant['team'].'">'.$participant['team'].' ('.@$team_leader.')</a>';
	}
	else $team_output = "нет";
	
	if ($participant['sex']=='m') $sex_output='Мужской';
	elseif ($participant['sex']=='f') $sex_output='Женский';
	else $sex_output='';

	if ( RequestModule == 'participantlist' ) $table_min_width = '';
	else $table_min_width = 'style="min-width: 300px;"'; 

	echo '
	<img src="'.$participant['photo_url'].'" align="left" style="border: 1px solid #ccc; display: block; margin: -1px 6px 0 0; width: auto !important; width: 150px; max-width: 150px;" />
	<table class="userinfo" '.$table_min_width.'>
		<tr><td>Фамилия:</td><td>'.$participant['surname'].'</td></tr>
		<tr><td>Имя:</td><td>'.$participant['name'].'</td></tr>
		<tr><td>Группа:</td><td>'.$participant['litgroup'].'</td></tr>
		<tr><td>Звено:</td><td>'.$team_output.'</a></td></tr>
		<tr><td>Пол:</td><td>'.$sex_output.'</td></tr>
	';
	if ($participant['blacklist']=='1') echo '
		<tr><td colspan="2"><b>В чёрном списке!</b></td><td></td></tr>
	';
	// лучше проверку прав текущего пользователя
	if ( @check_user_access ('admin') ) {
		
		
		/*
		echo '
			<tr><td style="vertical-align: top;">Системные группы:</td><td><ul>';
		if (!empty ($participant['group'])) foreach ($participant['group'] as $key=>$value) {
			$q = mysql_query("SELECT * FROM `groups` WHERE systemname='$value'");
			$f = mysql_fetch_array($q);
			if ($f['name']!=='') echo '<li>'.$f['name'].'</li>';	
		}
		echo '</ul></td></tr>';
		*/
	}
	echo '	
	</table>'."\n";
	
	return TRUE;
}

function formTeamsArray () {
	$array = array();
	$q = mysql_query ("SELECT * FROM `teams`");
	
	$array[0]['id'] = 0;
	$array[0]['leader'] = 'нет';
	
	for ($i=1; $i <= mysql_num_rows ($q); $i++) {
		$f = mysql_fetch_array($q);
		$array[$f['id']]['id'] = $f['id'];
		
		$fields = array (0=>'leader', 1=>'graduate', 2=>'teacher');
		
		foreach ($fields as $key=>$value) {
			if ( $f[$value] != 0) {
				$info = get_participant_info ($f[$value]);
				$array[$f['id']][$value] = $info['name'].' '.$info['surname'];
			}
		}
		$array[$f['id']]['num_people'] = mysql_num_rows( mysql_query ( "SELECT * FROM `participants` WHERE `team`='$f[id]';" ) );
		$array[$f['id']]['num_students'] = mysql_num_rows( mysql_query ( "SELECT * FROM `participants` WHERE `team`='$f[id]' AND `litgroup` != 'Преподаватель' AND `litgroup` != 'Выпускник';" ) );
		$array[$f['id']]['num_7'] = mysql_num_rows( mysql_query ( "SELECT * FROM `participants` WHERE `team`='$f[id]' AND `litgroup` LIKE '7%';" ) );
		$array[$f['id']]['num_8'] = mysql_num_rows( mysql_query ( "SELECT * FROM `participants` WHERE `team`='$f[id]' AND `litgroup` LIKE '8%';" ) );
		$array[$f['id']]['num_9'] = mysql_num_rows( mysql_query ( "SELECT * FROM `participants` WHERE `team`='$f[id]' AND `litgroup` LIKE '9%';" ) );
		$array[$f['id']]['num_10'] = mysql_num_rows( mysql_query ( "SELECT * FROM `participants` WHERE `team`='$f[id]' AND `litgroup` LIKE '10%';" ) );
		$array[$f['id']]['num_11'] = mysql_num_rows( mysql_query ( "SELECT * FROM `participants` WHERE `team`='$f[id]' AND `litgroup` LIKE '11%';" ) );
		$array[$f['id']]['num_m'] = mysql_num_rows( mysql_query ( "SELECT * FROM `participants` WHERE `team`='$f[id]' AND `litgroup` != 'Преподаватель' AND `litgroup` != 'Выпускник' AND `sex`='m';" ) );
		$array[$f['id']]['num_f'] = mysql_num_rows( mysql_query ( "SELECT * FROM `participants` WHERE `team`='$f[id]' AND `litgroup` != 'Преподаватель' AND `litgroup` != 'Выпускник' AND `sex`='f';" ) );
	}
	return $array;
}

function formTeamArray ( $id ) {
	$array = array();
	
	$q = mysql_query ("SELECT * FROM `teams` WHERE `id`='$id'");
	$f = mysql_fetch_array($q);
	
	$array[0] = $f['leader'];
	$array[1] = $f['graduate'];
	$array[2] = $f['teacher'];
	
	$q = mysql_query("SELECT * FROM `participants` WHERE `team`='$id' AND `id`!='$array[0]' AND `id`!='$array[1]' AND `id`!='$array[2]' ORDER BY  `litgroup` ASC, `surname` ASC;");
	
	for ($i=0; $i<mysql_num_rows($q); $i++) {
		$f = mysql_fetch_array($q);
		$array[$i+3] = $f['id'];
	}
	
	return $array;
}

function formParticipantArray ( $sortField, $sortDir ) {
	$arg = func_get_args();
	$array = array();
	
	if (@$arg['4']=='1') $blacklist = 'AND `blacklist` = \'1\''; else $blacklist = '';
	
	$q = mysql_query("
		SELECT * FROM `participants`
		WHERE  `surname` LIKE '$arg[2]%' AND `litgroup` LIKE '$arg[3]%' $blacklist
		ORDER BY `$sortField` $sortDir;
	");
									
	for ($i=0; $i < mysql_num_rows ($q); $i++) {
		$f = mysql_fetch_array($q);
		$array[$i] = get_participant_info ($f['id']);
	}
		
	return $array;
}

function getGroupsList () {
	$q = mysql_query ("SELECT * FROM `groups`");
	for ($i=0; $i < mysql_num_rows ($q); $i++) {
		$f = mysql_fetch_array($q);
		$list[$f['systemname']] = $f['name'];
	}
	return $list;
}

function idUserid ( $target, $id ) {
	if ( $target == "userid" ) {				// id > userid
		$q = mysql_query ("SELECT * FROM `users` WHERE `id` = '$id'");
		
		if( mysql_num_rows ($q) == 0 ) return $id;
		
		$f = mysql_fetch_array($q);
		$userid = $f['userid'];
		return $userid;
	}
	elseif ( $target == "id" ) {				// userid > id
		$q = mysql_query ("SELECT * FROM `users` WHERE `userid` = '$id'");
		
		if( mysql_num_rows ($q) == 0 ) return $id;
		
		$f = mysql_fetch_array($q);
		$id = $f['id'];
		return $id;
	}
	else return FALSE;
}

function saveTeam ( $id, $leader, $graduate, $teacher ) {
	global $user; 
	
	if ( $id == 0 ) $newTeam = TRUE; else $newTeam = FALSE;
	
	$info = get_participant_info($leader);
	if ( ( $newTeam && $info['team'] != 0 ) || ( !$newTeam && ( $info['team'] != $id && $info['team'] != 0) ) ) {
		report_error ("Звеньевой уже состоит в $info[team] звене! Звено не будет сохранено");
		return FALSE;
	}
	if ( $graduate != '' ) {
		$info = get_participant_info($graduate);
		if ( ( $newTeam && $info['team'] != 0 ) || ( !$newTeam && ( $info['team'] != $id && $info['team'] != 0) ) ) {
			report_error ("Выпускник уже состоит в $info[team] звене! Звено не будет сохранено");
			return FALSE;
		}
		elseif ( $info['litgroup'] != 'Преподаватель' && $info['litgroup'] != 'Выпускник' ) {
			report_error ("Выпускник не является выпускником или преподавателем! Звено не будет сохранено");
			return FALSE;
		}
	}
	if ( $teacher != '' ) {
		$info = get_participant_info($teacher);
		if ( ( $newTeam && $info['team'] != 0 ) || ( !$newTeam && ( $info['team'] != $id && $info['team'] != 0) ) ) {
			report_error ("Преподаватель уже состоит в $info[team] звене! Звено не будет сохранено");
			return FALSE;
		}
		elseif ( $info['litgroup'] != 'Преподаватель' && $info['litgroup'] != 'Выпускник' ) {
			report_error ("Преподаватель не является выпускником или преподавателем! Звено не будет сохранено");
			return FALSE;
		}
	}
	
	mysql_query ("START TRANSACTION;");
	
	if ( $newTeam ) {
		$q = mysql_query ("SELECT MAX(`id`) FROM `teams`");
		$f = mysql_fetch_array ($q);
		$id = $f['MAX(`id`)'] + 1;
	}
	
	if ( !$newTeam ) {
		$team_old = formTeamArray($id);
		
		if ( !mysql_query ("
		UPDATE `participants` SET `team` = '0'
		WHERE `id` = '$team_old[0]' LIMIT 1 ;") ) {
			mysql_query ("ROLLBACK;");
			report_error ("Произошла ошибка изменения участника слета"); 
			return FALSE;
		}
		if ( !mysql_query ("
		UPDATE `participants` SET `team` = '0'
		WHERE `id` = '$team_old[1]' LIMIT 1 ;") ) {
			mysql_query ("ROLLBACK;");
			report_error ("Произошла ошибка изменения участника слета"); 
			return FALSE;
		}
		if ( !mysql_query ("
		UPDATE `participants` SET `team` = '0'
		WHERE `id` = '$team_old[2]' LIMIT 1 ;") ) {
			mysql_query ("ROLLBACK;");
			report_error ("Произошла ошибка изменения участника слета"); 
			return FALSE;
		}
	}
	
	if ( !mysql_query ("
	UPDATE `participants` SET `team` = '$id'
	WHERE `id` = '$leader' LIMIT 1 ;") ) {
		mysql_query ("ROLLBACK;");
		report_error ("Произошла ошибка изменения участника слета"); 
		return FALSE;
	}
	if ( !mysql_query ("
	UPDATE `participants` SET `team` = '$id'
	WHERE `id` = '$graduate' LIMIT 1 ;") ) {
		mysql_query ("ROLLBACK;");
		report_error ("Произошла ошибка изменения участника слета"); 
		return FALSE;
	}
	if ( !mysql_query ("
	UPDATE `participants` SET `team` = '$id'
	WHERE `id` = '$teacher' LIMIT 1 ;") ) {
		mysql_query ("ROLLBACK;");
		report_error ("Произошла ошибка изменения участника слета"); 
		return FALSE;
	}
	
	if ( $newTeam ) {
		if ( !mysql_query ("
		INSERT INTO `teams` (`id`, `leader`, `graduate`, `teacher`)
		VALUES ('$id', '$leader', '$graduate', '$teacher' )") ) {
			mysql_query ("ROLLBACK;");
			report_error ("Произошла ошибка добавления звена в БД"); 
			return FALSE;
		}
	}
	else {	
		if ( !mysql_query ("
		UPDATE `teams` SET `leader`='$leader', `graduate`='$graduate', `teacher`='$teacher'
		WHERE `id` = '$id' LIMIT 1 ;") ) {
			mysql_query ("ROLLBACK;");
			report_error ("Произошла ошибка добавления звена в БД"); 
			return FALSE;
		}
	}
	if ( !mysql_query ("
	INSERT INTO `logs_admin` (`admin_id`, `id`, `action`, `ip`)
	VALUES ('$user[userid]', $id, 'Созранение звена', '$_SERVER[REMOTE_ADDR]');") ) {
		mysql_query ("ROLLBACK;");
		report_error ("Произошла ошибка записи в логи. Пользователь не был добавлен"); 
		return FALSE;
	}
	
	mysql_query ("COMMIT;");
	
	return $id;
}

function deleteTeam ( $id ) {
	mysql_query ("START TRANSACTION;");
		
	if ( !mysql_query ("
	UPDATE `participants` SET `team` = '0'
	WHERE `team` = '$id';") ) {
		mysql_query ("ROLLBACK;");
		report_error ("Не удалось обновить участников слета"); 
		return FALSE;
	}
	
	if ( !mysql_query ("
	DELETE FROM `teams` WHERE `id`='$id';") ) {
		mysql_query ("ROLLBACK;");
		report_error ("Не удалось удалить звено из БД"); 
		return FALSE;
	}
	
	$q = mysql_query ("SELECT * FROM `teams`");
	for ($i=0; $i<mysql_num_rows($q); $i++) {
		$f = mysql_fetch_array ($q);
	}
	if ( mysql_num_rows($q) > 0 && $f['id'] != mysql_num_rows($q) ) {
		$q = mysql_query ("SELECT * FROM `teams`");
		for ($i=0; $i<mysql_num_rows($q); $i++) {
			$f = mysql_fetch_array ($q);
			$k=$i+1;
			if ($k != $f['id']) {
				if ( !mysql_query ("
				UPDATE `teams` SET `id`='$k'
				WHERE `id` = '$f[id]' LIMIT 1 ;") ) {
					mysql_query ("ROLLBACK;");
					report_error ("Не удалось провести синхронизацию таблиц звеньев и участников (#1)"); 
					return FALSE;
				}
				if ( !mysql_query ("
				UPDATE `participants` SET `team` = '$k'
				WHERE `team` = '$f[id]';") ) {
					mysql_query ("ROLLBACK;");
					report_error ("Не удалось провести синхронизацию таблиц звеньев и участников (#2)"); 
					return FALSE;
				}
			}
		}
	}
	
	mysql_query ("COMMIT;");
	return TRUE;
}

function addUser ( $id, $userid, $password, $group ) {
	global $user;
	$arg = func_get_args();
	
	foreach ( $arg as $key=>$value ) {
		if ( $value == '' ) {
			report_error ("Форма заполнена не полностью");
			return FALSE;
		}
	}
	
	$q = mysql_query ("SELECT * FROM `users` WHERE `id` = '$id' OR `userid` = '$userid';");
	if ( mysql_num_rows ($q) > 0 ) {
		report_error ("Пользователь для этого участника или с таким логином уже существует. Пользователь не будет создан.");
		return FALSE;
	}
	
	$hash = crypt ($password);
	
	mysql_query ("START TRANSACTION;");
	
	if ( !mysql_query ("
	INSERT INTO `users` (`id`, `userid`, `hash`)
	VALUES ('$id', '$userid', '$hash')") ) {
		mysql_query ("ROLLBACK;");
		report_error ("Произошла ошибка добавления пользователя $userid в БД"); 
		return FALSE;
	}
	
	if ( !updateUsersGroup ( $userid, $group ) ) {
		mysql_query ("ROLLBACK;");
		return FALSE;
	}
	
	if ( !mysql_query ("
	INSERT INTO `logs_admin` (`admin_id`, `id`, `action`, `ip`)
	VALUES ('$user[userid]', '$userid', 'Создание пользователя', '$_SERVER[REMOTE_ADDR]');") ) {
		mysql_query ("ROLLBACK;");
		report_error ("Произошла ошибка записи в логи. Пользователь не был добавлен"); 
		return FALSE;
	}
	
	mysql_query ("COMMIT;");
	
	return TRUE;
}

function addParticipant ( $participant ) { 
	global $user;

	$arg = func_get_args();
	
	/*
	foreach ( $arg as $key=>$value ) {
		if ( $value == '' ) {
			report_error ("Форма заполнена не полностью");
			return FALSE;
		}
	}
	*/

	$q = mysql_query ("SELECT * FROM `participants` WHERE `name` = '".$participant['name']."' AND `surname` = '".$participant['surname']."' AND `litgroup` = '".$participant['litgroup']."';");
	if ( mysql_num_rows ($q) > 0 ) {
		report_error ("Пользователь с такими именем, фамилией и группой уже существует");
		return FALSE;
	}
	
	mysql_query ("START TRANSACTION;");
	
	$q = mysql_query ("SELECT MAX(`id`) FROM `participants`");
	$f = mysql_fetch_array ($q);
	$id = $f['MAX(`id`)'] + 1;

	if ( !mysql_query ("
	INSERT INTO `participants` (`id`, `name`, `surname`, `litgroup`, `team`, `sex`, `photo_url`)
	VALUES ('$id', '".$participant['name']."', '".$participant['surname']."', '".$participant['litgroup']."', '".@$participant['team']."', '".$participant['sex']."', '".$participant['photo_url']."')") ) {
		mysql_query ("ROLLBACK;");
		report_error ("Произошла ошибка добавления участника ".$participant['name']." ".$participant['surname']." в БД"); 
		return FALSE;
	}
	/*
	if ( !updateUsersGroup ( $id, $group ) ) {
		mysql_query ("ROLLBACK;");
		return FALSE;
	}
	*/
	if ( !mysql_query ("
	INSERT INTO `logs_admin` (`admin_id`, `id`, `action`, `ip`)
	VALUES ('$user[userid]', $id, 'Создание участника', '$_SERVER[REMOTE_ADDR]');") ) {
		mysql_query ("ROLLBACK;");
		report_error ("Произошла ошибка записи в логи. Пользователь не был добавлен"); 
		return FALSE;
	}
	
	mysql_query ("COMMIT;");
	
	return $id;
}

function editParticipant ( $participant ) {
	global $account;
  	
	$arg = func_get_args();
	
	/*
	foreach ( $arg as $key=>$value ) {
		if ( $value == '' ) {
			report_error ("Форма заполнена не полностью");
			return FALSE;
		}
	}
	*/
	
	if ($participant['blacklist']=='1' && @$participant['team']!='0') {
		report_error ("Человек находится в черном списке! Нельзя оставлять его в звене! ;-)");
		return FALSE;
	}
	
	$q = mysql_query ("SELECT * FROM `participants` WHERE `name` = '".$participant['name']."' AND `surname` = '".$participant['surname']."' AND `litgroup` = '".$participant['litgroup']."';");
	for ($i=0; $i<mysql_num_rows($q); $i++) {
		$f = mysql_fetch_array($q);
		if ( $f['id'] != $participant['id'] ) {
			report_error ("Пользователь с такими именем, фамилией и группой уже существует");
			return FALSE;
		}
	}
	
	$q = mysql_query ("SELECT * FROM `participants` WHERE `id` = '".$participant['id']."'");
	$old = mysql_fetch_array($q);
	
	if ( mysql_num_rows ( mysql_query ("
  SELECT * FROM `teams` WHERE (`leader` = '".$participant['id']."' OR `graduate` = '".$participant['id']."' OR `teacher` = '".$participant['id']."') AND `id` != '".@$participant['team']."'
  ") ) > 0 ) {
		report_error ("Руководителей звена нельзя переместить в другое звено");
		return FALSE;
	}
	mysql_query ("START TRANSACTION;");
	
	if ( !mysql_query ("
	UPDATE `participants` SET `name` = '".$participant['name']."', `surname` = '".$participant['surname']."', `litgroup` = '".$participant['litgroup']."', `sex`='".$participant['sex']."', `team` = '".@$participant['team']."', `blacklist` = '".@$participant['blacklist']."', `photo_url` = '".@$participant['photo_url']."'
	WHERE `id` = '".$participant['id']."' LIMIT 1 ;") ) {
		mysql_query ("ROLLBACK;");
		report_error ("Произошла ошибка изменения участника слета"); 
		return FALSE;
	}
	
	/*if ( !updateUsersGroup ( $id, $group ) ) {
		mysql_query ("ROLLBACK;");
		return FALSE;
	}
	*/
	$log = 'Редактирование пользователя '.$participant['id'].'. Старые значения: name:'.$old['name'].' surname:'.$old['surname'].' litgroup:'.$old['litgroup'].', group:';
	/*foreach ($group as $bankgroup) {
		$log .= $bankgroup.',';
	}
	*/
	if ( !mysql_query ("
	INSERT INTO `logs_admin` (`admin_id`, `id`, `action`, `ip`)
	VALUES ('$account[id]', '".$participant['id']."', '$log', '$_SERVER[REMOTE_ADDR]');") ) {
		mysql_query ("ROLLBACK;");
		report_error ("Произошла ошибка записи в логи. Пользователь не был изменения"); 
		return FALSE;
	}
	
	mysql_query ("COMMIT;");
	
	return TRUE;
}

function updateUsersGroup ( $userid, $group ) {
	mysql_query ("START TRANSACTION;");
	
	if ( !mysql_query ("DELETE FROM `usersgroup` WHERE `userid`='$userid';") ) {
		mysql_query ("ROLLBACK;");
		report_error ("Произошла ошибка обновления групп пользователя"); 
		return FALSE;
	}
	
	foreach ($group as $sysgroup) {
		if (!mysql_query ("
			INSERT INTO `usersgroup` (`userid` , `group`)
			VALUES ('$userid', '$sysgroup');") ) {
			
			mysql_query ("ROLLBACK;");
			report_error ("Произошла ошибка обновления групп пользователя"); 			
			return FALSE;
		}
	}
	
	mysql_query ("COMMIT;");
	return TRUE;
}


function getInfoFromPeople ( $name, $surname, $litgroup, $field ) {
	$url = "http://people.lit.msu.ru/people.php?";
	switch ($litgroup) {
		case 'Преподаватель':
			$table = 'staff';
			$url = $url.'table='.$table;
			break;
		case 'Выпускник':
			$table = 'graduate';
			$url = $url.'table='.$table;
			break;
		case '':
			return;
			break;
		default: 
			$table = 'students';
			list($grade, $group) = explode('.', $litgroup);
			$url = $url.'grade='.$grade.'&group='.$group.'&table='.$table;
			break;
	}
	$url = $url.'&name='.$name.'&surname='.$surname;
	
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 60);
	$response = curl_exec($ch);
	if ( curl_getinfo($ch,CURLINFO_HTTP_CODE) !== 200 ){
		$response = "An error occured while communicating people.lit.msu.ru. Try again later";
	}
	$return = '';
	if ( $xml = simplexml_load_string($response) ) {
		if ($xml->count() == 1) $person = (array) $xml->person[0];

		$return = @$person[$field];			
	}
	return $return;
}

?>
