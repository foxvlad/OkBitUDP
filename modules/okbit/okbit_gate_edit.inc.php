<?php


if ($this->owner->name == 'panel') {
	$out['CONTROLPANEL'] = 1;
}



$table_name = 'okbit_gate';

$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");

	$out['UPDATED'] = $rec['UPDATED'];
	

if ($this->mode == 'update') {

	$this->getConfig();
	$ok = 1;
	
	if ($this->tab == '') {

		global $title;
		$rec['TITLE'] = $title;
		if ($rec['TITLE'] != '') {
			$deb_title = "title - ". $rec['TITLE'];
		}
		else $deb_title = '';

		global $ip;
		$rec['IP'] = $ip;
		if ($rec['IP'] == '') {
			$out['ERR_IP'] = 1;
			$ok = 0;
		}
		
		global $mod;			
		if ($mod != $rec['MOD']) {
			if($rec['MOD'] == '6000'){
				$rec_d = SQLSelect("SELECT * FROM okbit_devices WHERE PARENT_ID ='".$rec['ID']."'");		
				foreach($rec_d as $cmd) {
					SQLExec("DELETE FROM okbit_data WHERE DEVICE_ID='".$cmd['ID']."' AND ETHERNET='0'");
				}
			SQLExec("DELETE FROM okbit_devices WHERE PARENT_ID='".$rec['ID']."'");				
			}
			$temp_sql = SQLSelectOne("SELECT * FROM `okbit_data` WHERE DEVICE_ID='$id' AND ETHERNET='1'");
			if ($temp_sql)SQLExec("DELETE FROM `okbit_data`  WHERE DEVICE_ID='$id' AND ETHERNET='1'");
		}			
		$rec['MOD'] = $mod;
		if ($rec['MOD'] == '') {
			$out['ERR_MOD'] = 1;
			$ok = 0;
		}
		
		$in_out = '';
		
		if ($rec['MOD'] =='7001'){
			$in_out = explode(',',DATA_7001);
		}
	
		else if ($rec['MOD'] =='7002'){
			$in_out = explode(',',DATA_7002);
		}
		
		else if ($rec['MOD'] =='7003'){
			$in_out = explode(',',DATA_7003);
		}
		
		else if ($rec['MOD'] =='7004'){
			$in_out = explode(',',DATA_7004);
		}
		
		else if ($rec['MOD'] =='7005'){
			$in_out = explode(',',DATA_7005);
		}
		
		else if ($rec['MOD'] =='7006'){
			$in_out = explode(',',DATA_7006);
		}
		
				
	}

	if ($ok) {
		if ($rec['ID']) {
			if ($this->config['API_LOG_DEBMES']) DebMes("Save params for ethernet device  $deb_title  with IP " . $rec['IP'] ." Mod - ".$rec['MOD'], 'okbit');
			SQLUpdate($table_name, $rec);
		} else {
			if ($this->config['API_LOG_DEBMES']) DebMes("Manual add new ethernet device   $deb_title  with IP " . $rec['IP'] ." Mod - ".$rec['MOD'], 'okbit');
			$rec['ID'] = SQLInsert($table_name, $rec);
		}
		
		$out['OK'] = 1;
		
		
		if ($this->tab == '' && $in_out != '') {
			foreach($in_out as $cmd) {
				$cmd_rec = SQLSelectOne("SELECT * FROM `okbit_data` WHERE DEVICE_ID=" . $rec['ID'] . " AND TITLE = '" . $cmd . "' AND ETHERNET='1'");
				if (!$cmd_rec['ID']) {
					$cmd_rec = array();
					$cmd_rec['TITLE'] = $cmd;
					$cmd_rec['ETHERNET'] = 1;
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
		SQLExec("DELETE FROM `okbit_data`  WHERE ID='" . (int)$delete_id . "' AND ETHERNET='1' ");
	}
	

	$properties = SQLSelect("SELECT * FROM `okbit_data` WHERE DEVICE_ID='" . $rec['ID'] . "' AND ETHERNET='1' ORDER BY ID");

	
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
		
		if (file_exists(DIR_MODULES . 'devices/devices.class.php')) {
			if ($properties[$i]['TITLE'] == 'power') $properties[$i]['SDEVICE_TYPE'] = 'relay';
			elseif ($properties[$i]['TITLE'] == 'Lamp1') $properties[$i]['SDEVICE_TYPE'] = 'relay';
			elseif ($properties[$i]['TITLE'] == 'Lamp2') $properties[$i]['SDEVICE_TYPE'] = 'relay';
			elseif ($properties[$i]['TITLE'] == 'Lamp3') $properties[$i]['SDEVICE_TYPE'] = 'relay';
			elseif ($properties[$i]['TITLE'] == 'Lamp4') $properties[$i]['SDEVICE_TYPE'] = 'relay';
			elseif ($properties[$i]['TITLE'] == 'Reley') $properties[$i]['SDEVICE_TYPE'] = 'relay';
			elseif ($properties[$i]['TITLE'] == 'Temp') $properties[$i]['SDEVICE_TYPE'] = 'sensor_temp';
			elseif ($properties[$i]['TITLE'] == 'Humidity') $properties[$i]['SDEVICE_TYPE'] = 'sensor_humidity';	
			elseif ($properties[$i]['TITLE'] == 'Status1') $properties[$i]['SDEVICE_TYPE'] = 'motion';	
			elseif ($properties[$i]['TITLE'] == 'Status2') $properties[$i]['SDEVICE_TYPE'] = 'motion';
			
			
			/*
			Управляемое реле/Выключатель  			relay
			Управляемый диммер  					dimmer
			RGB-контроллер  						rgb
			Датчик движения  						motion
			Кнопка  								button
			Датчик открытия/закрытия  				openclose
			Датчик температуры  					sensor_temp
			Датчик влажности  						sensor_humidity
			Датчик состояния  						sensor_state
			Датчик процентное значение  			sensor_percentage
			Датчик атмосферного давления  			sensor_pressure
			Датчик мощности  						sensor_power
			Датчик напряжения 	 					sensor_voltage
			Датчик тока  							sensor_current
			Датчик освещённости  					sensor_light
			Датчик протечки  						leak
			Датчик дыма  							smoke
			Счётчик  								counter
			*/
		}
	
		
	}
	$out['PROPERTIES'] = $properties;   
}


outHash($rec, $out);
