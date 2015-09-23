<?php
// ItIsSletManagerModule

$module = 'admin';

if ( !defined("RequestModule") || RequestModule !== 'core' ) die;

$modules[$module]['name'] = 'Управление'; // человеческое название модуля

$modules[$module]['action'][] = 'admin_add_user'; // работает полностью
$modules[$module]['menu'][] = 'Добавить пользователя системы';
$modules[$module]['title'][] = 'Новый пользователь системы';



$modules[$module]['groups'][] = 'admin';

function show_admin ($action) {
	global $modules, $user, $participantlist, $litgroupSet;
	$module = $modules['admin'];

	switch ($action) {
// Добавить пользователя
		case 'admin_add_user':
			$participantlist = TRUE;
			echo '
			<script type="text/javascript">
				function participant(id) {
					document.getElementById(\'id\').value=id;
				}
			</script>
			';

			if ( isset ($_POST[$action]) ) {
				if (empty ($_POST['group'])) $sysgroup = Array();	
				 else $sysgroup =  $_POST['group'];
				if ( addUser ( $_POST['id'], $_POST['userid'], $_POST['password'], $sysgroup ) )  {
					echo '
					<p>Пользователь успешно добавлен</p>
					';
				}
				else echo 'Ошибка добавления пользователя';
			}

			echo '
			<div>
			<form method="post" action="'.$_SERVER["PHP_SELF"].'?action='.$action.'">
			<p>
			<table class="userinfo">
				<tr>
					<td>id лицеиста</td>
					<td><input type="text" name="id" id="id" value="" /></td>
				</tr>
				<tr>
					<td>Логин</td>
					<td><input type="text" name="userid" value="" /></td>
				</tr>
				<tr>
					<td>Пароль</td>
					<td><input type="password" name="password" value="" /></td>
				</tr>
				<tr><td>Системная<br />группа</td>
					<td><select name="group[]" size="3" multiple>
				';

				foreach ( getGroupsList() as $sysname=>$name) {
					$atr = '';
					/*foreach ( $editUser['group'] as $value ) {
						if ($value == $sysname) $atr = 'selected';
					}*/
					echo '<option value="'.$sysname.'" '.$atr.'>'.$name.'</option>';
				}

				echo '</select>
					</td>
				</tr>
				<tr><td>&nbsp;</td>
					<td> <input type="submit" name="'.$action.'" value="Добавить" /></td>
				</tr>
			</table></p>
			</form>
			</div>';
			break;

	}
}

/////////////////////////////////////////////////////////////////////////////////////////////////
?>
