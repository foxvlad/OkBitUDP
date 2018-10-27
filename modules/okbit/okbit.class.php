<?php

/**
* OkBitUDP 
* @package project
* @author Wizard <foxvlad@yandex.ru>
* @copyright http://okbit.ru (c)
* @version 0.2 (wizard, 19:10:55 [Oct 11, 2018])
*/


define ('OKBIT_GATE_CODES', serialize (array(	'6000' =>	'GATE UDP-RS485',
												'7001' =>	'ESP Lamp',
												'7002' =>	'ESP Climatic',
												'7003' =>	'ESP Rele',
												'7004' =>	'ESP LED RGB',
												'7005' =>	'ESP Dimmer',
												'7006' =>	'ESP Sensor'
												)));


define ('OKBIT_DEVICES_CODES', serialize (array('6001' =>	'LCM-8',
												'6002' => 	'SAM',
												'6003' => 	'UAM-8',
												'6004' => 	'HVDM-4',
												'6005' => 	'LDM-6',
												'6007' => 	'IRM',
												'6008' => 	'FAM-6'
												)));
												

												
define ('DATA_6001', 'Lamp1,Lamp2,Lamp3,Lamp4,Lamp5,Lamp6,Lamp7,Lamp8');	
define ('DATA_6002', 'S1,S2,S3,S4,S5,S6');													
define ('DATA_6003', 'IND1,IND2,IND3,IND4,INA1,INA2,INA3,INA4');	
define ('DATA_6004', 'Lamp1,Level1,Lamp2,Level2,Lamp3,Level3,Lamp4,Level4');
define ('DATA_6005', 'IND1,IND2,IND3,IND4,INA1,INA2,INA3,INA4');
define ('DATA_6006', 'IND1,IND2,IND3,IND4,INA1,INA2,INA3,INA4');	
define ('DATA_6007', 'IND1,IND2,IND3,IND4,INA1,INA2,INA3,INA4');
define ('DATA_6008', 'IND1,IND2,IND3,IND4,INA1,INA2,INA3,INA4');

define ('DATA_7001', 'Lamp1,Lamp2');
define ('DATA_7002', 'Temp,Humidity');
define ('DATA_7003', 'Rele');
define ('DATA_7004', 'Red,Green,Blue');
define ('DATA_7005', 'Lamp,Level');
define ('DATA_7006', 'Status1,Status1');

class okbit extends module {
	
	
	
	/**
	* okbit
	*
	* Module class constructor
	*
	* @access private
	*/
	function __construct() {
		$this->name="okbit";
		$this->title="OkBitUDP";
		$this->module_category="<#LANG_SECTION_DEVICES#>";
		$this->checkInstalled();
		//require_once(DIR_MODULES.$this->name . '/lib/build_package_okbit.class.php');		
		//require_once(DIR_MODULES.$this->name . '/lib/send_udp_okbit.class.php');	
	}
	
	
	/**
	* saveParams
	*
	* Saving module parameters
	*
	* @access public
	*/
	function saveParams($data=1) {
		$p=array();
		if (IsSet($this->id)) {
			$p["id"]=$this->id;
		}
		if (IsSet($this->view_mode)) {
			$p["view_mode"]=$this->view_mode;
		}
		if (IsSet($this->edit_mode)) {
			$p["edit_mode"]=$this->edit_mode;
		}
		if (IsSet($this->data_source)) {
			$p["data_source"]=$this->data_source;
		}
		if (IsSet($this->tab)) {
			$p["tab"]=$this->tab;
		}
		return parent::saveParams($p);
	}
	
	
	/**
	* getParams
	*
	* Getting module parameters from query string
	*
	* @access public
	*/
	function getParams() {
		global $id;
		global $mode;
		global $view_mode;
		global $edit_mode;
		global $data_source;
		global $tab;
		global $parent_title;
		global $parent_id;
		
		if (isset($id)) {
			$this->id=$id;
		}
		if (isset($mode)) {
			$this->mode=$mode;
		}
		if (isset($view_mode)) {
			$this->view_mode=$view_mode;
		}
		if (isset($edit_mode)) {
			$this->edit_mode=$edit_mode;
		}
		if (isset($data_source)) {
			$this->data_source=$data_source;
		}
		if (isset($tab)) {
			$this->tab=$tab;
		}		
		if (isset($parent_title)) {
			$this->parent_title=$parent_title;
		}	  
		if (isset($parent_id)) {
			$this->parent_id=$parent_id;
		}		
	}
	
	
	/**
	* Run
	*
	* Description
	*1
	* @access public
	*/
	function run() {
		global $session;
		$out=array();
		if ($this->action=='admin') {
			$this->admin($out);
		} else {
			$this->usual($out);
		}
		if (IsSet($this->owner->action)) {
			$out['PARENT_ACTION']=$this->owner->action;
		}
		if (IsSet($this->owner->name)) {
			$out['PARENT_NAME']=$this->owner->name;
		}
		$out['VIEW_MODE']=$this->view_mode;
		$out['EDIT_MODE']=$this->edit_mode;
		$out['MODE']=$this->mode;
		$out['ACTION']=$this->action;
		$out['DATA_SOURCE']=$this->data_source;
		$out['TAB']=$this->tab;
		$this->data=$out;
		$p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
		$this->result=$p->result;
	}
	
	
	/**
	* BackEnd
	*
	* Module backend
	*
	* @access public
	*/
	function admin(&$out) {
		
		
		
		$this->getConfig();
		
		$out['API_IP']=$this->config['API_IP'];
		$out['API_DISC_PERIOD'] = $this->config['API_DISC_PERIOD'];
		$out['API_LOG_DEBMES'] = $this->config['API_LOG_DEBMES'];
		$out['API_LOG_CYCLE'] = $this->config['API_LOG_CYCLE'];
		
		
		if ((time() - (int)gg('cycle_okbitRun')) < 15 ) {
				$out['CYCLERUN'] = 1;
			} else {
				$out['CYCLERUN'] = 0;
			}
		
		if ($this->config['API_LOG_DEBMES']) DebMes("admin(): " . $_SERVER['REQUEST_URI'], $this->name);
		
		if ($this->view_mode=='update_settings') {
			
			global $api_ip;
			$this->config['API_IP']=$api_ip;
			
			global $api_disc_period;
			$this->config['API_DISC_PERIOD'] = (int)$api_disc_period;
		   
			global $api_log_debmes;
			$this->config['API_LOG_DEBMES'] = (int)$api_log_debmes;
			
			global $api_log_cycle;
			$this->config['API_LOG_CYCLE'] = (int)$api_log_cycle;
			
			$this->saveConfig();
			$this->redirect("?");
		}
		if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
			$out['SET_DATASOURCE']=1;
		}
		 
		if ($this->data_source=='okbit_gate' || $this->data_source=='') {
			if ($this->view_mode=='' || $this->view_mode=='search_okbit_gate') {//выыод имеющихся шлюзов
				$this->search_okbit_gate($out);
			}
			if ($this->view_mode=='discover_okbit_gate') {// автоматический поиск шлюзов
				$this->discover_okbit_gate($out, $this->id);
			}
			if ($this->view_mode=='edit_okbit_gate') {// редактирование/ручное добавление шлюза
				$this->edit_okbit_gate($out, $this->id);
			}			
			if ($this->view_mode=='update_gate') {// Получение информации о шлюзе
				$this->okbit_update_gate($out, $this->id);
			}	
			if ($this->view_mode=='bind_gate') {// подвязка шлюза к серверу мажордомо
				$this->okbit_bind_gate($out, $this->id);
			}				
			if ($this->view_mode=='delete_okbit_gate') {//удаление шлюза
				$this->delete_okbit_gate($this->id);
				$this->redirect("?data_source=okbit_gate");
			}
		}
		
		if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
			$out['SET_DATASOURCE']=1;
		}
		
		if ($this->data_source=='okbit_device') {
			if ($this->view_mode=='' || $this->view_mode=='search_okbit_devices') {//вывод списка устройств
				$this->search_okbit_devices($out, $this->parent_title, $this->parent_id);				
			}
			if ($this->view_mode=='edit_okbit_device') {// Добавление/редактирование устройства вручную
				$this->edit_okbit_device($out, $this->id, $this->parent_title, $this->parent_id);
			}			
			if ($this->view_mode=='okbit_devices_discover') {// автоматический поиск устройств
				$this->okbit_devices_discover($out, $this->parent_title, $this->parent_id);
			}	
			if ($this->view_mode=='okbit_devices_update') {// получение информации об устройстве
				$this->okbit_devices_update($out, $this->parent_title, $this->parent_id, $this->id);
			}				
			if ($this->view_mode == 'delete_okbit_device') { //Удаление устройства
				$this->delete_okbit_device($this->id);
				$this->redirect("?data_source=okbit_device&parent_title=$this->parent_title&parent_id=$this->parent_id");
			}
			
		}
	}


	/**
	* FrontEnd
	*
	* Module frontend
	*
	* @access public
	*/
	function usual(&$out) {
		$this->admin($out);
	}
	
	
	/**
	* okbit_gate search
	*
	* @access public
	*/
	function search_okbit_gate(&$out) {		
		global $session;
		if ($this->owner->name == 'panel') {
			$out['CONTROLPANEL'] = 1;		
		}		  
		
		$gate_code = unserialize(OKBIT_GATE_CODES);
		
		$res = SQLSelect("SELECT * FROM okbit_gate ORDER BY ID DESC");	


		if ($res[0]['ID']) {
			$total = count($res);
			for($i = 0; $i < $total; $i++) {
				$mod_code = $res[$i]['MOD'];
				$res[$i]['GATE_NAME'] = $gate_code["$mod_code"];
			}
			$out['RESULT'] = $res;
		}
	}
	
	
	/**
	* discover_okbit_gate
	*
	* @access public
	*/
	function discover_okbit_gate(&$out) {
		require_once(DIR_MODULES.$this->name . '/lib/build_package_okbit.class.php');		
		require_once(DIR_MODULES.$this->name . '/lib/send_udp_okbit.class.php');
	
		if($this->config['API_IP']) $ip_serv = $this->config['API_IP'];	
		else $ip_serv = '0.0.0.0';
		
		$udppacket = new Build_package($this->config['API_LOG_DEBMES'],0, 0, 65534, 255, 0, 0);
		$data_send = $udppacket->udp_msg_packet(); //сборка UDP OkBit пакета		
		$gate = new Send_UDP('255.255.255.255', 6400, $ip_serv, 6600, $this->config['API_LOG_DEBMES']); //задаем свойства класса адрес и порт шлюза и порт модуля udp_send
		$gate->sock_create(); //Создание UDP сокета
		$gate->sockSetTimeout(10); //Установка таймаута для получения ответа
		$gate->sock_bind();
		$gate->sockSetBroadcast();
		$gate->udp_send($data_send); // отправка пакета
		
		$this->redirect("?data_source=okbit_gate");		
	}
	
	
	
	/**
	* okbit_update_gate
	*
	* @access public
	*/
	
	function okbit_update_gate(&$out, $id) {
		require_once(DIR_MODULES.$this->name . '/lib/build_package_okbit.class.php');		
		require_once(DIR_MODULES.$this->name . '/lib/send_udp_okbit.class.php');
		
		if($this->config['API_IP']) $ip_serv = $this->config['API_IP'];	
		else $ip_serv = '0.0.0.0';
		$gate_sh = SQLSelectOne("SELECT * FROM `okbit_gate` WHERE ID='".$id."'");//запрос для получения IP шлюза	
	
		$udppacket = new Build_package($this->config['API_LOG_DEBMES'],0, 0, 65534, 20, 0, 0);
		$data_send = $udppacket->udp_msg_packet(); //сборка UDP OkBit пакета		
		$gate = new Send_UDP($gate_sh['IP'], 6400, $ip_serv, 6600, $this->config['API_LOG_DEBMES']); //задаем свойства класса адрес и порт шлюза и порт модуля udp_send
		$gate->sock_create(); //Создание UDP сокета
		$gate->sockSetTimeout(1); //Установка таймаута для получения ответа
		$gate->sock_bind();
		//$gate->sockSetBroadcast();
		$gate->udp_send($data_send); // отправка пакета
		
		
		$this->redirect("?data_source=okbit_gate");	
	}	
	
	
	
	/**
	* test_update_gate
	*
	* @access public
	*/
	
	function test_update_gate() {
		require_once(DIR_MODULES.$this->name . '/lib/build_package_okbit.class.php');		
		require_once(DIR_MODULES.$this->name . '/lib/send_udp_okbit.class.php');	
		
		if($this->config['API_IP']) $ip_serv = $this->config['API_IP'];	
		else $ip_serv = '0.0.0.0';
				
		$udppacket = new Build_package($this->config['API_LOG_DEBMES'],0, 0, 65534, 20, 0, 0);
		$data_send = $udppacket->udp_msg_packet(); //сборка UDP OkBit пакета		
		$gate = new Send_UDP('255.255.255.255', 6400, $ip_serv, 6600, $this->config['API_LOG_DEBMES']); //задаем свойства класса адрес и порт шлюза и порт модуля udp_send
		$gate->sock_create(); //Создание UDP сокета
		$gate->sockSetTimeout(1); //Установка таймаута для получения ответа
		$gate->sock_bind();
		$gate->sockSetBroadcast();

		$gate->udp_send($data_send); // отправка пакета
	}	
	
	
	/**
	* okbit_bind_gate
	*
	* @access public
	*/
	
	function okbit_bind_gate(&$out, $id) {
		require_once(DIR_MODULES.$this->name . '/lib/build_package_okbit.class.php');		
		require_once(DIR_MODULES.$this->name . '/lib/send_udp_okbit.class.php');
		
		if($this->config['API_IP']) $ip_serv = $this->config['API_IP'];	
		else $ip_serv = '0.0.0.0';
		$gate_sh = SQLSelectOne("SELECT * FROM `okbit_gate` WHERE ID='".$id."'");//запрос для получения IP шлюза	
			
		$udppacket = new Build_package($this->config['API_LOG_DEBMES'],0, 0, 65534, 64, 0, 0);
		$data_send = $udppacket->udp_msg_packet(); //сборка UDP OkBit пакета		
		$gate = new Send_UDP($gate_sh['IP'], 6400, $ip_serv, 6600, $this->config['API_LOG_DEBMES']); //задаем свойства класса адрес и порт шлюза и порт модуля udp_send
		$gate->sock_create(); //Создание UDP сокета
		$gate->sockSetTimeout(1); //Установка таймаута для получения ответа
		$gate->sock_bind();
		//$gate->sockSetBroadcast();
		$gate->udp_send($data_send); // отправка пакета
		
		$this->redirect("?data_source=okbit_gate");	
	}
	
	
	/**
	* okbit_gate edit/add
	*
	* @access public
	*/
	function edit_okbit_gate(&$out, $id) {
		require(DIR_MODULES.$this->name.'/okbit_gate_edit.inc.php');
	}
	
	
	/**
	* okbit_gate delete record
	*
	* @access public
	*/
	function delete_okbit_gate($id) {
		
		$rec = SQLSelectOne("SELECT * FROM okbit_gate WHERE ID='" . $id . "'");
		
		
		if($rec['MOD'] == '6000'){	
			$rec_d = SQLSelect("SELECT * FROM okbit_devices WHERE PARENT_ID ='" . $id . "'");		
			foreach($rec_d as $cmd) {
				SQLExec("DELETE FROM okbit_data WHERE DEVICE_ID='".$cmd['ID']."' AND ETHERNET='0'");
			}
			SQLExec("DELETE FROM okbit_devices WHERE PARENT_ID='".$rec['ID']."'");
		} else SQLExec("DELETE FROM okbit_data WHERE DEVICE_ID='".$id."' AND ETHERNET='1'");
				
		SQLExec("DELETE FROM okbit_gate WHERE ID='" . $id . "'");
		
		
		
	}
	
	/**
	* propertySetHandle
	*
	* Обработчик привязанных свойств и методов
	*
	* @access private
	*/
	
	function propertySetHandle($object, $property, $value) {
		require_once(DIR_MODULES.$this->name . '/lib/build_package_okbit.class.php');		
		require_once(DIR_MODULES.$this->name . '/lib/send_udp_okbit.class.php');		
		
		$this->getConfig();		
		if($this->config['API_IP']) $ip_serv = $this->config['API_IP'];	
		else $ip_serv = '0.0.0.0';
		
		$properties = SQLSelectOne("SELECT * FROM `okbit_data` WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND  LINKED_PROPERTY LIKE '".DBSafe($property)."'");
		$properties['VALUE'] = $value;
		$properties['UPDATED'] = date('Y-m-d H:i:s');
		SQLUpdate('okbit_data', $properties);
		
		if ($properties['ETHERNET'] == '1') {
			$gate_sh = SQLSelectOne("SELECT * FROM `okbit_gate` WHERE ID='".$properties['DEVICE_ID']."'");					
			
			if ($gate_sh['MOD'] == 7001){
				$cmd_out = explode(',',DATA_7001);
			}
			else if ($gate_sh['MOD'] == 7002){
				$cmd_out = explode(',',DATA_7002);
			}
			else if ($gate_sh['MOD'] == 7003){
				$cmd_out = explode(',',DATA_7003);
			}
			else if ($gate_sh['MOD'] == 7004){
				$cmd_out = explode(',',DATA_7004);
			}
			else if ($gate_sh['MOD'] == 7005){
				$cmd_out = explode(',',DATA_7005);
			}
			else if ($gate_sh['MOD'] == 7006){
				$cmd_out = explode(',',DATA_7006);
			}
			$s = 1;
			foreach($cmd_out as $xxx) {
				if ($xxx == $properties['TITLE']) $dev_in = $s;
				$s++;
			}			
				
			$udppacket = new Build_package($this->config['API_LOG_DEBMES'],0, 0, 65534, 30, 0, 0, $dev_in, $value);
			$data_send = $udppacket->udp_msg_packet(); //сборка UDP OkBit пакета		
			$gate = new Send_UDP($gate_sh['IP'], 6400, $ip_serv, 6600, $this->config['API_LOG_DEBMES']); //задаем свойства класса адрес и порт шлюза и порт модуля udp_send
			$gate->sock_create(); //Создание UDP сокета
			$gate->sockSetTimeout(1); //Установка таймаута для получения ответа
			$gate->sock_bind();
			$gate->udp_send($data_send); // отправка пакета
		}		
		
		else if ($properties['ETHERNET'] == '0') {
			
			$rs485 = SQLSelectOne("SELECT * FROM `okbit_devices` WHERE ID='".$properties['DEVICE_ID']."'");			
			$gate_sh = SQLSelectOne("SELECT * FROM `okbit_gate` WHERE ID='".$rs485['PARENT_ID']."'");	
			
			if ($rs485['DEVICE'] == 6001){
				$cmd_out = explode(',',DATA_6001);
			}
			else if ($rs485['DEVICE'] == 6002){
				$cmd_out = explode(',',DATA_6002);
			}
			else if ($rs485['DEVICE'] == 6003){
				$cmd_out = explode(',',DATA_6003);
			}
			else if ($rs485['DEVICE'] == 6004){
				$cmd_out = explode(',',DATA_6004);
			}
			else if ($rs485['DEVICE'] == 6005){
				$cmd_out = explode(',',DATA_6005);
			}
			else if ($rs485['DEVICE'] == 6006){
				$cmd_out = explode(',',DATA_6006);
			}
			else if ($rs485['DEVICE'] == 6007){
				$cmd_out = explode(',',DATA_6007);
			}
			else if ($rs485['DEVICE'] == 6008){
				$cmd_out = explode(',',DATA_6008);
			}
			$s = 1;
			foreach($cmd_out as $xxx) {
				if ($xxx == $properties['TITLE']) $dev_in = $s;
				$s++;
			}			
			
			$udppacket = new Build_package($this->config['API_LOG_DEBMES'],$rs485['SUB_ID'], 0, 65534, 30, $rs485['SUB_ID'], $rs485['DEVICE_ID'], $dev_in, $value);
			$data_send = $udppacket->udp_msg_packet(); //сборка UDP OkBit пакета	
			$gate = new Send_UDP($gate_sh['IP'], 6400, $ip_serv, 6600, $this->config['API_LOG_DEBMES']); //задаем свойства класса адрес и порт шлюза и порт модуля udp_send
			$gate->sock_create(); //Создание UDP сокета
			$gate->sockSetTimeout(1); //Установка таймаута для получения ответа
			$gate->sock_bind();
			$gate->udp_send($data_send); // отправка пакета
		}
		
	}
	
	
	/**
	* udp_parsing
	*
	* Парсинг полученных данных
	*
	* @access private
	*/	
	
	function parsing_soc($buf_cmd, $gate_ip){ //Парсинг полученного UDP-пакета
	
		$pars = new Send_UDP();
		if ($this->config['API_LOG_DEBMES'])$pars->debug=true;
		$pars->parsing_packege($buf_cmd, $gate_ip);	
	}
	
	
	
	/**
	* okbit_devices search
	*
	* @access public
	*/	
	function search_okbit_devices(&$out, $parent_title, $parent_id){			
		global $session;
		if ($this->owner->name == 'panel') {
			$out['CONTROLPANEL'] = 1;		
		}			
		$out['PARENT_TITLE'] =  $parent_title;
		$out['PARENT_ID'] =  $parent_id;		
		$device_code = unserialize(OKBIT_DEVICES_CODES);		
		$res = SQLSelect("SELECT * FROM `okbit_devices` WHERE `PARENT_ID` = '$parent_id' ORDER BY ID DESC");
		if ($res[0]['ID']) {
						$total = count($res);
			for($i = 0; $i < $total; $i++) {
				$dev_code = $res[$i]['DEVICE'];
				$res[$i]['DEVICE_NAME'] = $device_code["$dev_code"];
			}
			$out['RES_DEVICES'] = $res;
		}		
	}
	
	
		
	
	
	/**
	* okbit_device edit/add
	*
	* @access public
	*/
	function edit_okbit_device(&$out, $id, $parent_title, $parent_id) {
		require(DIR_MODULES.$this->name.'/okbit_device_edit.inc.php');
	}
	
	
	/**
	* okbit_devices_discover
	*
	* @access public
	*/
	function okbit_devices_discover(&$out, $parent_title, $parent_id) {
		require_once(DIR_MODULES.$this->name . '/lib/build_package_okbit.class.php');		
		require_once(DIR_MODULES.$this->name . '/lib/send_udp_okbit.class.php');
		
		if($this->config['API_IP']) $ip_serv = $this->config['API_IP'];	
		else $ip_serv = '0.0.0.0';		

		$gate_sh = SQLSelectOne("SELECT * FROM `okbit_gate` WHERE ID='".$parent_id."'");//запрос для получения IP шлюза	
		$udppacket = new Build_package($this->config['API_LOG_DEBMES'],0, 0, 65534, 20, 0, 0);
		$data_send = $udppacket->udp_msg_packet(); //сборка UDP OkBit пакета		
		$gate = new Send_UDP($gate_sh['IP'], 6400, $ip_serv, 6600, $this->config['API_LOG_DEBMES']); //задаем свойства класса адрес и порт шлюза и порт модуля udp_send
		$gate->sock_create(); //Создание UDP сокета
		$gate->sockSetTimeout(10); //Установка таймаута для получения ответа
		$gate->sock_bind();
		//$gate->sockSetBroadcast();
		$gate->udp_send($data_send); // отправка пакета
		
		$this->redirect("?data_source=okbit_device&view_mode=search_okbit_devices&parent_title=$parent_title&parent_id=$parent_id");	
	}
	
	
	
	/**
	* okbit_devices_update
	*
	* @access public
	*/
	
	function okbit_devices_update(&$out, $parent_title, $parent_id, $id) {
		require_once(DIR_MODULES.$this->name . '/lib/build_package_okbit.class.php');		
		require_once(DIR_MODULES.$this->name . '/lib/send_udp_okbit.class.php');
		
		if($this->config['API_IP']) $ip_serv = $this->config['API_IP'];	
		else $ip_serv = '0.0.0.0';
		
		$cmd_up = SQLSelectOne("SELECT * FROM `okbit_devices` WHERE ID=".(int)$id);
		$cmd_up_gate = SQLSelectOne("SELECT * FROM `okbit_gate` WHERE ID=".(int)$parent_id);		
		
		$udppacket = new Build_package($this->config['API_LOG_DEBMES'],$cmd_up['SUB_ID'], 0, 65534, 21, $cmd_up['SUB_ID'], $cmd_up['DEVICE_ID']);
		$data_send = $udppacket->udp_msg_packet(); //сборка UDP OkBit пакета		
		$gate = new Send_UDP($cmd_up_gate['IP'], 6400, $ip_serv, 6600, $this->config['API_LOG_DEBMES']); //задаем свойства класса адрес и порт шлюза и порт модуля udp_send
		$gate->sock_create(); //Создание UDP сокета
		$gate->sockSetTimeout(1); //Установка таймаута для получения ответа
		$gate->sock_bind();
		$gate->sockSetBroadcast();
		$gate->udp_send($data_send); // отправка пакета
				
		$this->redirect("?data_source=okbit_device&view_mode=search_okbit_devices&parent_title=$parent_title&parent_id=$parent_id");
	}
	
	/**
	* olbit_device delete record
	*
	* @access public
	*/
	
	function delete_okbit_device(&$id) {
		
		$rec = SQLSelectOne("SELECT * FROM `okbit_devices` WHERE ID='".$id."'");
		
		SQLExec("DELETE FROM okbit_data WHERE DEVICE_ID='".$rec['ID']."' AND ETHERNET='0'");
		
		SQLExec("DELETE FROM `okbit_devices` WHERE ID='" .$rec['ID']."'");
		
	}
	
	

	function processCycle() {
		$this->getConfig();
		//to-do
	}
	
	
	/**
	* Install
	*
	* Module installation routine
	*
	* @access private
	*/
	function install($data='') {
		parent::install();
	}
	
	
	/**
	* Uninstall
	*
	* Module uninstall routine
	*
	* @access public
	*/
	function uninstall() {
		SQLExec('DROP TABLE IF EXISTS okbit_gate');
		SQLExec('DROP TABLE IF EXISTS okbit_devices');
		SQLExec('DROP TABLE IF EXISTS okbit_data');
		parent::uninstall();
	}
	
	/**
	* dbInstall
	*
	* Database installation routine
	*
	* @access private
	*/
	function dbInstall($data) {

		$data = <<<EOD
			okbit_gate: ID int(10) unsigned NOT NULL auto_increment
			okbit_gate: TITLE varchar(255) NOT NULL DEFAULT ''
			okbit_gate: IP varchar(255) NOT NULL DEFAULT ''
			okbit_gate: SN varchar(255) NOT NULL DEFAULT ''
			okbit_gate: VER varchar(255) NOT NULL DEFAULT ''
			okbit_gate: MOD varchar(255) NOT NULL DEFAULT ''
			okbit_gate: STATUS int(2) unsigned NOT NULL DEFAULT 0
			okbit_gate: IP_SERVER varchar(255) NOT NULL DEFAULT ''
			okbit_gate: UPDATED datetime			
			
			okbit_devices: ID int(10) unsigned NOT NULL auto_increment
			okbit_devices: TITLE varchar(255) NOT NULL DEFAULT ''
			okbit_devices: PARENT_ID int(10) unsigned NOT NULL DEFAULT 0
			okbit_devices: SUB_ID int(10) unsigned NOT NULL DEFAULT 0
			okbit_devices: DEVICE_ID int(10) unsigned NOT NULL DEFAULT 0
			okbit_devices: DEVICE int(10) unsigned NOT NULL DEFAULT 0
			okbit_devices: VER varchar(255) NOT NULL DEFAULT ''
			okbit_devices: STATUS int(2) unsigned NOT NULL DEFAULT 0
			okbit_devices: UPDATED datetime

			okbit_data: ID int(10) unsigned NOT NULL auto_increment
			okbit_data: TITLE varchar(255) NOT NULL DEFAULT ''
			okbit_data: VALUE varchar(255) NOT NULL DEFAULT ''
			okbit_data: DEVICE_ID int(10) unsigned NOT NULL DEFAULT 0
			okbit_data: ETHERNET int(2) unsigned NOT NULL DEFAULT 0
			okbit_data: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
			okbit_data: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
			okbit_data: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
			okbit_data: UPDATED datetime
EOD;
		parent::dbInstall($data);
	}
	
}