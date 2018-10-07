<?php

/**
* OkBit (UDP-пакеты) 
* @package project
* @author Wizard <foxvlad@yandex.ru>
* @copyright http://okbit.ru (c)
* @version 0.1 (wizard, [Feb 04, 2018])
*/

global $session;

if ($this->owner->name == 'panel') {
	$out['CONTROLPANEL'] = 1;
}

	$out['PARENT_TITLE'] =  $parent_title;
	$out['PARENT_ID'] =  $parent_id;
	
	$device_code = unserialize(OKBIT_DEVICES_CODES);
		
	
	if (!$parent_id)  $parent_id = '1';
	$filter = "$parent_id";

  
$res = SQLSelect("SELECT * FROM `okbit_devices` WHERE `PARENT_ID` = $filter ORDER BY ID DESC");

if ($res[0]['ID']) {
	$total = count($res);
	for($i = 0; $i < $total; $i++) {
		$dev_code = $res[$i]['DEVICE'];
		$res[$i]['DEVICE_NAME'] = $device_code["$dev_code"];
	}
	$out['RES_DEVICES'] = $res;
	
}
