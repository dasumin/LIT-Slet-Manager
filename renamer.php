<?php
/*include ("./mysql_config.php");
include ("./functions.php");

if (!mysql_connect($mysql_server,$mysql_user,$mysql_password)) 
	exit ('Произошла ошибка подключения к базе данных. Повторите попытку и сообщите, пожалуйста, о случившемся правительству Crazy Week.');
	
mysql_select_db($mysql_db);

$q = mysql_query ("
	SELECT * FROM `participants` WHERE `photo_url` LIKE '%lit.msu.ru%' AND `photo_url` NOT LIKE '%85%';");
for ($i=0; $i<mysql_num_rows($q); $i++) {
	$f = mysql_fetch_array($q);
	echo $f['id'].'<br>';
	echo $f['photo_url'].'<br>';
	$new_photo_url = str_replace ( 'http://www.lit.msu.ru/ru/new/', './images/old_', $f['photo_url'] );
	echo $new_photo_url.'<br>';
	
	if ( mysql_query ("
	UPDATE `participants` SET `photo_url` = '$new_photo_url'
	WHERE `id` = '".$f['id']."' LIMIT 1 ;") ) echo 'Успешно переименовали';
	echo '<p>';
}
*/
?>