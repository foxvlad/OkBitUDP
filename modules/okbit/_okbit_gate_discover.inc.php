<?php

/**
* OkBit (UDP-пакеты) 
* @package project
* @author Wizard <foxvlad@yandex.ru>
* @copyright http://okbit.ru (c)
* @version 0.1 (wizard, [Feb 04, 2018])
*/


if ($this->owner->name == 'panel') {
	$out['CONTROLPANEL'] = 1;
}

$table_name = 'okbit_gate';

$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");

if ($this->mode == 'update') {

	$this->getConfig();
	$ok = 1;
	
	if ($this->tab == '') {

		global $title;
		$rec['TITLE'] = $title;
		if ($rec['TITLE'] != '') {
			$deb_title = "title - ". $rec['TITLE'];
		}
		else $deb_title = 0;

		global $ip;
		$rec['IP'] = $ip;
		if ($rec['IP'] == '') {
			$out['ERR_IP'] = 1;
			$ok = 0;
		}
		

		
	}

	if ($ok) {
		if ($rec['ID']) {
			if ($this->config['API_LOG_DEBMES']) DebMes('Save params for gate ' . $deb_title . ' with IP ' . $rec['IP'] .PHP_EOL, 'okbit');
			SQLUpdate($table_name, $rec);
		} else {
			if ($this->config['API_LOG_DEBMES']) DebMes('Manual add new gate ' . $deb_title . ' with IP ' . $rec['IP'] .PHP_EOL, 'okbit');
			$rec['ID'] = SQLInsert($table_name, $rec);
		}
		
		$out['OK'] = 1;

		
	} else {
		$out['ERR'] = 1;
	}
}


outHash($rec, $out);
