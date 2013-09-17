<?php
// ItIsSletManagerModule

$module = 'welcome';

if ( !defined("RequestModule") || RequestModule !== 'core' ) die;

//$modules[$module]['name'] = 'Приветствие системы';
$modules[$module]['action'][0] = 'welcome';
$modules[$module]['title'][0] = 'Лицейский слет 2013';

$modules[$module]['groups'][] = 'guest'; // группы, которым разрешено пользоваться модулем

function show_welcome () {
	global $user;
	
	if (check_user_access ('admin', $user)) 
	echo '
	<h2>Добро&nbsp;пожаловать&nbsp;в&nbsp;Слётоуправлятор!</h2>
	';
	
	else echo '
	<p>Привет! Вы попали на ресурс подготовки Лицейского Слета 2013</p>
	<p>Вы можете ознакомиться с информацией с помощью меню слева</p>
	<p><i>Если Вы хотите ознакомиться со списками звеньев, вам <a href="/teams">сюда</a></i></p> 
	';
}
?>
