<?php
// ItIsSletManagerModule

$module = 'manage_participants';

if ( !defined("RequestModule") || RequestModule !== 'core' ) die;

$modules[$module]['name'] = 'Управление участниками'; // человеческое название модуля

$modules[$module]['action'][] = 'mass-add-students'; // работает полностью
$modules[$module]['menu'][] = 'Массовое добавление лицеистов';
$modules[$module]['title'][] = 'Массовое добавление лицеистов';

$modules[$module]['action'][] = 'edit_participant';
$modules[$module]['menu'][] = 'Редактировать участника';
$modules[$module]['title'][] = 'Изменить информацию';

$modules[$module]['groups'][] = 'admin';
$modules[$module]['groups'][] = 'registrar';

function show_manage_participants ($action) {
	global $modules, $account, $participantlist, $litgroupSet;	
	$module = $modules['manage_participants'];
	
	switch ($action) {
		case 'mass-add-students':
			if ( empty ( $_POST ) ) {
				$group = $litgroupSet;
				echo '
				<form method="POST" action="'.$_SERVER["PHP_SELF"].'?action='.$action.'">
					Выберите, пожалуйста, группу, которую Вы будете добавлять: 
					<select name="litgroup" size="1">
				';
				
				foreach ($group as $key=>$value) echo '<option value="'.$value.'">'.$value.'</option>';
					
				echo '
					</select>
					<input type="submit" name="stage1" value="Дальше" />
				</form>
				';
			}
			elseif ( isset ($_POST['stage1']) ) {
				echo '
				<form method="POST" action="'.$_SERVER["PHP_SELF"].'?action='.$action.'">
				';
				
				for ($i=0; $i<30; $i++) {
					echo '
					<p>'.($i+1).'<br />
					<table>
						<tr><td>Имя</td><td><input type="text" maxlength="255" name="name'.$i.'" /></td></tr>
						<tr><td>Фамилия</td><td><input type="text" maxlength="255" name="surname'.$i.'" /></td></tr>
					';
					echo '
						<tr><td>URL фото</td><td><input type="text" maxlength="255" size="40" name="photo_url'.$i.'" /></td></tr>
					';
					echo '
						<tr><td>Пол</td><td><select name="sex'.$i.'">
							<option value="m">Мужской</option>
							<option value="f">Женский</option>
						</select>
						</td></tr>
					</table>
					</p>
					';
				}
				
				echo '	
					<input type="hidden" name="litgroup" value="'.$_POST['litgroup'].'" />
					<input type="submit" name="stage2" value="Добавить группу '.$_POST['litgroup'].'" />
				</form>
				';
			}
			elseif ( isset ($_POST['stage2']) ) {
				for ($i=0; $i<30; $i++) {
					if ( $_POST['name'.$i]=="" && $_POST['surname'.$i]=="" && $_POST['photo_url'.$i]=="" ) continue;
					$participant['name'] = $_POST['name'.$i];
					$participant['surname'] = $_POST['surname'.$i];
					$participant['litgroup'] = $_POST['litgroup'];
					$participant['photo_url'] = $_POST['photo_url'.$i];
					$participant['sex'] = $_POST['sex'.$i];
					$participant['team'] = '0';					
					if ( addParticipant ( $participant ) )
					echo $_POST['name'.$i].' '.$_POST['surname'.$i].' успешно добавлен<br />';
				}
				echo '<p><a href="'.$_SERVER["PHP_SELF"].'?action='.$action.'">Добавить еще</a></p>';
			}
			break;
			
		case 'edit_participant':
			
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
				
				if ( editParticipant ( $_POST ) )  {
					echo '
					<p>Пользователь успешно изменен</p><p>';
					print_participant_info ( $_POST['id'] );
					echo '</p>';
				}	
				else echo 'Ошибка изменения пользователя';
			}
			
			if ( isset ($_POST['getid']) || isset ($_GET['id']) ) {
				
				if ( isset ($_POST['id'])) $id = $_POST['id'];
					else $id = $_GET['id'];
				
				if ( isset ($new_account) && !$new_account ) {
					$name = $_POST['name'];
					$surname = $_POST['surname'];
					$litgroup = $_POST['litgroup'];
					$photo_url = $_POST['photo_url'];
				}
				
				$editUser = get_participant_info ($id, 1);
			
				$group = $litgroupSet;
	
				echo '
				<div>
				<form method="post" action="'.$_SERVER["PHP_SELF"].'?action='.$action.'">
				<p><img src="'.$editUser['photo_url'].'" align="left" style="border: 1px solid #ccc; display: block; margin: -1px 6px 0 0;" />
				<table class="userinfo">
					<tr>
						<td>Имя</td> 
						<td><input type="text" name="name" value="'.$editUser['name'].'" /></td>
					</tr>	
					<tr>
						<td>Фамилия  </td> 
						<td><input type="text" name="surname" value="'.$editUser['surname'].'" /></td>
					</tr>
					<tr><td>Группа</td> 
						<td><select name="litgroup" size="1">
					';
				
					foreach ($group as $key=>$value) {
						if ($value == $editUser['litgroup']) $atr = 'selected';
							else $atr = '';
						echo '<option value="'.$value.'" '.$atr.'>'.$value.'</option>';
					}
					
					echo '
						</select>
					</tr>
					';
					
					$disable_teams = FALSE;
					foreach (formTeamsArray() as $key=>$value) {
						if ($value['id'] == $editUser['team']) $atr = 'selected';
							else $atr = '';
						if ($value['id']==$editUser['team'] && $value['leader'] == $editUser['name'].' '.$editUser['surname']) $disable_teams = TRUE;
						@$out .= '<option value="'.$value['id'].'" '.$atr.'>'.$value['id'].' ('.$value['leader'].')</option>'."\n";
					}
					
					echo '
					<tr><td>Звено</td> 
						<td><select name="team" id="team" size="1"';
					if ($disable_teams) echo ' disabled ';
					echo '>
					';
					echo $out;
					if ($disable_teams) echo '<input type="hidden" name="team" value="'.$editUser['team'].'" />';
					echo '
						</select>
					</tr>
					';
					
					$black_array = array (0=>'нет', 1=>'да');
					echo '
					<tr><td>Blacklist</td> 
						<td><select name="blacklist" size="1">
					';
				
					foreach ($black_array as $key=>$value) {
						if ($key == $editUser['blacklist']) $atr = 'selected';
							else $atr = '';
						echo '<option value="'.$key.'" '.$atr.'>'.$value.'</option>';
					}
					
					echo '
						</select>
					</tr>';

					$sex_array = array ('m'=>'мужской', 'f'=>'женский');
					echo '
					<tr><td>Пол</td>
                                                <td><select name="sex" size="1">
                                        ';

                                        foreach ($sex_array as $key=>$value) {
                                                if ($key == $editUser['sex']) $atr = 'selected';
                                                        else $atr = '';
                                                echo '<option value="'.$key.'" '.$atr.'>'.$value.'</option>';
                                        }

                                        echo '
                                                </select>
                                        </tr>
					';
					echo '
					<tr><td>Фотография</td> 
						<td><input type="text" name="photo_url" value="'.$editUser['photo_url'].'" /></td>
					</tr>
					';
					echo '
					<tr><td>&nbsp;</td> 
						<td><input type="hidden" name="id" value="'.$editUser['id'].'" />
						<input type="submit" name="'.$action.'" value="Изменить" /></td>
					</tr>		
				</table></p>
				</form>
				</div>';
			}
			
			if ( !empty ($_POST) || isset($_GET['id']) ) echo '<p style="margin: 40px 0 0 0;"><i>Изменить другого пользователя</i></p>';
			$participantlist = TRUE;
			echo '
			<div>
			Введите id участника:
			<form method="post">
				<input type="text" name="id" id="id" />
				<input type="submit" name="getid" value="Редактировать">
			</form>
			</div>
			<script language="text/javascript">
				SetFocus("id");
			</script>';
			
			break;
		
	}
}

/////////////////////////////////////////////////////////////////////////////////////////////////
?>
