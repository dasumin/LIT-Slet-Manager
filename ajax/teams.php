<?php
session_name("SletManager");
session_start();

include ("../config.php");
include ("../functions.php");

if (!mysql_connect($mysql_server,$mysql_user,$mysql_password))
	report_error ('Не удалось подключиться к серверу баз данных.');

if (!mysql_select_db($mysql_db))
	report_error ('Не удалось подключиться к базе данных.');

// Загружаем данные о пользователе
if ( isset ($_SESSION['userid']) ) $user = get_participant_info ($_SESSION['userid']);
else $user['group'][] = 'guest';

$userIsAdmin = FALSE;
$userIsLeader= FALSE;
foreach (@$user['group'] as $key=>$value) {
	if ($value=='admin' or $value=='organizer') $userIsAdmin = TRUE;
	if ($value=='leader') $userIsLeader = TRUE;
}

define ("RequestModule", 'team-ajax');
include ("../modules/2teams.php");

if (empty($_GET['action'])) $action = 'show_team';
	else $action = $_GET['action'];

if ($userIsAdmin || $userIsLeader) {
	if ( !$userIsAdmin && $_GET['team'] != $user['team'] ) report_error ("Вы можете редактировать список только своего звена");
	else {
    	if ( !$userIsAdmin && $teams_state_closed ) report_error ("Списки закрыты. Вас же просили не редактировать списки звеньев ;-)");
		if ( isset ($_GET['add'] ) || isset ($_GET['add_confirmed'] )) {
			if ( isset ($_GET['add'] ) ) $participant = get_participant_info ( $_GET['add'] );
				else $participant = get_participant_info ( $_GET['add_confirmed'] );

			if (!$userIsAdmin && $participant['team']!=0 && $participant['team']!=$_GET['team']) report_error ("Вы не можете добавить участника в свое звено, так как он уже находится в другом звене.");
			elseif ($userIsAdmin && $participant['team']!=0 && $participant['team']!=$_GET['team'] && !isset($_GET['add_confirmed'])) {
				echo '
				Этот участник уже состоит в '.$participant['team'].' звене.<br />Вы уверены, что хотите перенести его в '.$_GET['team'].' звено?<br />
				<input type="button" value="Да" onclick="TeamManage (\''.$participant['id'].'\', \'add_confirmed\')" /> <input type="button" value="Нет" onclick="TeamManage (\''.$participant['id'].'\', \'nothing\')" /> <br />
				';
			}
			else {
				$participant['team'] = $_GET['team'];
				editParticipant ( $participant );
			}
		}
		if ( isset ($_GET['delete'] )) {
			$participant = get_participant_info ( $_GET['delete'] );
			$participant['team'] = '0';
			editParticipant ( $participant );

		}

	}
	show_teams ( $action );
}
?>
