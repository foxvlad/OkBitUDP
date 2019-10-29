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

$qry = '1';

global $save_qry;

if ($save_qry) {
	$qry = $session->data['okbit_devices_qry'];
} else {
	$session->data['okbit_devices_qry'] = $qry;
}

if (!$qry) $qry = '1';

$sortby_okbit_devices = 'ID DESC';

$out['SORTBY'] = $sortby_okbit_devices;
  
$res = SQLSelect("SELECT * FROM okbit_gate WHERE $qry ORDER BY $sortby_okbit_devices");

if ($res[0]['ID']) {
	$total = count($res);
	for($i = 0; $i < $total; $i++) {
		$dev_id = $res[$i]['ID'];
		$online = SQLSelectOne("SELECT VALUE FROM okbit_data WHERE DEVICE_ID='$dev_id' AND TITLE='online'");
		$res[$i]['ONLINE'] = $online['VALUE'];
	}
	$out['RESULT'] = $res;
}
