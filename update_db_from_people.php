<?php
include ("./config.php");
include ("./functions.php");

if (!mysql_connect($mysql_server,$mysql_user,$mysql_password))
        report_error ('Не удалось подключиться к серверу баз данных.');

if (!mysql_select_db($mysql_db))
        report_error ('Не удалось подключиться к базе данных.');

$tables = array ('students', 'staff', 'graduate');		

foreach ($tables as $value) {
	$url = "http://people.lit.msu.ru/people.php?table=".$value;

	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 60);
	$response = curl_exec($ch);
	if ( curl_getinfo($ch,CURLINFO_HTTP_CODE) !== 200 ){
		$response = "An error occured while communicating people.lit.msu.ru. Try again later";
	}

	if ( $xml = simplexml_load_string($response) ) {
		for ($i=0; $i<$xml->count(); $i++) {
			$participant = (array) $xml->person[$i];
			
			switch ($value) {
				case 'staff':
					$participant['litgroup'] = 'Преподаватель';
					break;
				case 'graduate':
					$participant['litgroup'] = 'Выпускник';
					break;
				case 'students':
					$participant['litgroup'] = $participant['grade'].'.'.$participant['group'];
					if ($participant['group']==0) $participant['litgroup']='';
					break;
			}
			
			if (!isset($participant['sex'])) $participant['sex'] = '';
			if ($participant['litgroup']=='') continue;
			
			$q = mysql_query ("
				SELECT * FROM `participants`
				WHERE `name` = '".$participant['name']."' AND
				`surname` = '".$participant['surname']."' AND
				`litgroup` = '".$participant['litgroup']."';
                        ");
				
			if ( mysql_num_rows ($q) == 0 ) {
				addParticipant($participant);
				echo 'Добавили: '.$participant['name'].' '.$participant['surname'].' ('.$participant['litgroup'].')<br>';
			}
                        
                        $q_full = mysql_query ("
				SELECT * FROM `participants`
				WHERE `name` = '".$participant['name']."' AND
				`surname` = '".$participant['surname']."' AND
				`litgroup` = '".$participant['litgroup']."' AND
                                `sex` = '".$participant['sex']."' AND
                                `photo_url` = '".$participant['photo_url']."';
                        ");
                        
                        if ( mysql_num_rows ($q) == 1 && mysql_num_rows ($q_full) == 0 ) {
                                $f = mysql_fetch_array($q_full);
                                $participant_new = $f['id'];
                                $participant_new['sex'] = $participant['sex'];
                                $participant_new['photo_url'] = $participant['photo_url'];
                                editParticipant( $participant_new );
				echo 'Обновили: '.$participant_new['name'].' '.$participant_new['surname'].' ('.$participant_new['litgroup'].')<br>';
                        }
			
		}
	}
}
?>

