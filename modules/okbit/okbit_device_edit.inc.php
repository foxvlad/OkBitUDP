<?php

if ($this->owner->name == 'panel') {
	$out['CONTROLPANEL'] = 1;
}

global $session;
$out['PARENT_TITLE'] =  $parent_title;
$out['PARENT_ID'] =  $parent_id;

$table_name = 'okbit_devices';

$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");

if ($this->mode == 'update') {

	$this->getConfig();
	$ok = 1;
	
	if ($this->tab == '') {

		global $title;
		$rec['TITLE'] = $title;


		global $sub_id;
		$rec['SUB_ID'] = $sub_id;
		if ($rec['SUB_ID'] == '') {
			$out['ERR_SUB_ID'] = 1;
			$ok = 0;
		}
		
		global $device_id;
		$rec['DEVICE_ID'] = $device_id;
		if ($rec['DEVICE_ID'] == '') {
			$out['ERR_DEVICE_ID'] = 1;
			$ok = 0;
		}
				
		$rec['PARENT_ID'] = $out['PARENT_ID'];
		
		global $device;			
		if ($device != $rec['DEVICE']) {
			$temp_sql = SQLSelectOne("SELECT * FROM `okbit_data` WHERE DEVICE_ID='$id'  AND ETHERNET='0'");
			if ($temp_sql)SQLExec("DELETE FROM `okbit_data`  WHERE DEVICE_ID='$id'  AND ETHERNET='0'");
		}			
		$rec['DEVICE'] = $device;
		if ($rec['DEVICE'] == '') {
			$out['ERR_DEVICE'] = 1;
			$ok = 0;
		}
		
		$in_out = '';
		
		if ($rec['DEVICE'] =='6001'){
			$in_out = explode(',',DATA_6001);
		}
	
		else if ($rec['DEVICE'] =='6002'){
			$in_out = explode(',',DATA_6002);
		}
		
		else if ($rec['DEVICE'] =='6003'){
			$in_out = explode(',',DATA_6003);
		}
		
		else if ($rec['DEVICE'] =='6004'){
			$in_out = explode(',',DATA_6004);
		}
		
		else if ($rec['DEVICE'] =='6005'){
			$in_out = explode(',',DATA_6005);
		}
		
		else if ($rec['DEVICE'] =='6006'){
			$in_out = explode(',',DATA_6006);
		}
		
				
	}

	if ($ok) {
		if ($rec['ID']) {
			if ($this->config['API_LOG_DEBMES']) DebMes('Save params for device ' . $rec['DEVICE'] . ' with Sub_id - ' . $rec['SUB_ID'] . ' with ID- ' . $rec['DEVICE_ID']. 'PARENT_ID- ' . $rec['PARENT_ID'] .PHP_EOL, 'okbit');
			SQLUpdate($table_name, $rec);
		} else {
			if ($this->config['API_LOG_DEBMES']) DebMes('Manual add new device ' . $rec['DEVICE'] . ' with Sub_id - ' . $rec['SUB_ID'] . ' with ID- ' . $rec['DEVICE_ID']. ' PARENT_ID- ' . $rec['PARENT_ID'] .PHP_EOL, 'okbit');
			$rec['ID'] = SQLInsert($table_name, $rec);
		}
		
		$out['OK'] = 1;
		
		if ($this->tab == '' && $in_out != '') {
			foreach($in_out as $cmd) {
				$cmd_rec = SQLSelectOne("SELECT * FROM `okbit_data` WHERE DEVICE_ID=" . $rec['ID'] . " AND TITLE = '" . $cmd . "' AND ETHERNET='0'");
				if (!$cmd_rec['ID']) {
					$cmd_rec = array();
					$cmd_rec['TITLE'] = $cmd;
					$cmd_rec['ETHERNET'] = 0;
					$cmd_rec['DEVICE_ID'] = $rec['ID'];
					SQLInsert('okbit_data', $cmd_rec);
				}
			}			
		}

		
	} else {
		$out['ERR'] = 1;
	}
}


if ($this->tab == 'data') {

	$new_id = 0;
	global $delete_id;
	
	if ($delete_id) {
		SQLExec("DELETE FROM `okbit_data`  WHERE ID='" . (int)$delete_id . "'");
	}
	

	$properties = SQLSelect("SELECT * FROM `okbit_data` WHERE DEVICE_ID='" . $rec['ID'] . "'  AND ETHERNET='0' ORDER BY ID");

	
	$total = count($properties);
	
	for($i = 0; $i < $total; $i++) {
		if ($properties[$i]['ID'] == $new_id) continue;
		
		if ($this->mode == 'update') {
			
			global ${'linked_object'.$properties[$i]['ID']};
			$properties[$i]['LINKED_OBJECT'] = trim(${'linked_object'.$properties[$i]['ID']});
			
			global ${'linked_property'.$properties[$i]['ID']};
			$properties[$i]['LINKED_PROPERTY'] = trim(${'linked_property'.$properties[$i]['ID']});
			
			global ${'linked_method'.$properties[$i]['ID']};
			$properties[$i]['LINKED_METHOD'] = trim(${'linked_method'.$properties[$i]['ID']});
			
			SQLUpdate('okbit_data', $properties[$i]);
			
			$old_linked_object = $properties[$i]['LINKED_OBJECT'];
			$old_linked_property = $properties[$i]['LINKED_PROPERTY'];
			
			if ($old_linked_object && $old_linked_object != $properties[$i]['LINKED_OBJECT'] && $old_linked_property && $old_linked_property != $properties[$i]['LINKED_PROPERTY']) {
				removeLinkedProperty($old_linked_object, $old_linked_property, $this->name);
			}
		}
		
		$properties[$i]['VALUE'] = str_replace('",','", ',$properties[$i]['VALUE']);

		if ($properties[$i]['LINKED_OBJECT'] && $properties[$i]['LINKED_PROPERTY']) {
			addLinkedProperty($properties[$i]['LINKED_OBJECT'], $properties[$i]['LINKED_PROPERTY'], $this->name);
		}
	
		
	}
	$out['PROPERTIES'] = $properties;   
}


outHash($rec, $out);
