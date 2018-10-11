<?php

/**
* OkBit (UDP-пакеты) 
* @package project
* @author Wizard <foxvlad@yandex.ru>
* @copyright http://okbit.ru (c)
* @version 0.1 (wizard, [Feb 14, 2018])
*/



define ('OKBIT_DEVICES_CODES', serialize (array('6000' =>	'ШЛЮЗ',
												'6001' =>	'МУС-8',
												'6002' => 	'МОС-6',
												'6003' => 	'УМА-8',
												'6004' => 	'МДВ-4',
												'6005' => 	'МДС-6',
												'6007' => 	'МИК',
												'6008' => 	'МПС-6',
												'6009' => 	'ДОП-2',
												'6010' => 	'ДОП-3',
												'6011' => 	'ДОП-4',
												'6012' =>	'ДОП-5'
												)));
												
define ('DATE_6001', 'L1,L2,L3,L4,L5,L6,L7,L8');	
define ('DATE_6002', 'S1,S2,S3,S4,S5,S6');													
define ('DATE_6003', 'IND1,IND2,IND3,IND4,INA1,INA2,INA3,INA4');	
define ('DATE_6004', 'L1,D1,L2,D2,L3,D3,L4,D4');												





class okbit extends module {
	
	/**
	* okbit
	*
	* Module class constructor
	*
	* @access private
	*/
	
	function okbit() {
	  $this->name="okbit";
	  $this->title="OkBit";
	  $this->module_category="<#LANG_SECTION_DEVICES#>";
	  $this->checkInstalled();
	}
	
	
	/**
	* saveParams
	*
	* Saving module parameters
	*
	* @access public
	*/
	
	function saveParams($data = 0) {
		
		$p = array();
		
		if (IsSet($this->id)) {
			$p['id'] = $this->id;
		}
		if (IsSet($this->view_mode)) {
			$p['view_mode'] = $this->view_mode;
		}
		if (IsSet($this->edit_mode)) {
			$p['edit_mode'] = $this->edit_mode;
		}
		if (IsSet($this->data_source)) {
			$p['data_source'] = $this->data_source;
		}
		if (IsSet($this->tab)) {
			$p['tab'] = $this->tab;
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
			$this->id = $id;
		}
		if (isset($mode)) {
			$this->mode = $mode;
		}
		if (isset($view_mode)) {
			$this->view_mode = $view_mode;
		}
		if (isset($edit_mode)) {
			$this->edit_mode = $edit_mode;
		}
		if (isset($data_source)) {
			$this->data_source = $data_source;
		}
		if (isset($tab)) {
			$this->tab = $tab;
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
	*
	* @access public
	*/
	
	function run() {
		
		global $session;
		
		$out = array();
		
		if ($this->action == 'admin') {
			$this->admin($out);
		} else {
			$this->usual($out);
		}
		
		if (IsSet($this->owner->action)) {
			$out['PARENT_ACTION'] = $this->owner->action;
		}
  
		if (IsSet($this->owner->name)) {
			$out['PARENT_NAME'] = $this->owner->name;
		}
  
		$out['VIEW_MODE'] = $this->view_mode;
		$out['EDIT_MODE'] = $this->edit_mode;
		$out['MODE'] = $this->mode;
		$out['ACTION'] = $this->action;
		$out['DATA_SOURCE'] = $this->data_source;
		$out['TAB'] = $this->tab;
		
		$this->data = $out;
		
		$p = new parser(DIR_TEMPLATES . $this->name . '/' . $this->name . '.html', $this->data, $this);
		$this->result = $p->result;
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
		$out['API_LOG_DEBMES'] = $this->config['API_LOG_DEBMES'];
		$out['API_LOG_CYCLE'] = $this->config['API_LOG_CYCLE'];
		
		
		if ((time() - gg('cycle_okbitRun')) < 15 ) {
				$out['CYCLERUN'] = 1;
			} else {
				$out['CYCLERUN'] = 0;
			}
		
		
		 
		 if ($this->view_mode=='update_settings') {
			 
			global $api_ip;
			$this->config['API_IP']=$api_ip;
		   
			global $api_log_debmes;
			$this->config['API_LOG_DEBMES'] = (int)$api_log_debmes;
			
			global $api_log_cycle;
			$this->config['API_LOG_CYCLE'] = (int)$api_log_cycle;

		   $this->saveConfig();
		   $this->redirect("?");
		   
			// После изменения настроек модуля перезапускаем цикл
			setGlobal('cycle_okbitControl', 'restart');
		   
		 }
	 
		
		if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
			$out['SET_DATASOURCE'] = 1;
		}
		
		
		
		if ($this->data_source == 'okbit_gate' || $this->data_source == '') {
			
			
			if ($this->view_mode == '' || $this->view_mode == 'search_okbit_gate') {//вывод списка шлюзов
				$this->search_okbit_gate($out);
			}
			
							
			if ($this->view_mode == 'edit_okbit_gate') { // Добавление/редактирование шлюза вручную 
				$this->edit_okbit_gate($out, $this->id);
			}
			
			if ($this->view_mode == 'discover_gate') { // Автомотический поиск шлюза 
				$this->discover_gate();
			}
			
			if ($this->view_mode == 'update_gate') { // Получение информации о шлюзе 
				$this->update_gate();
			}
			
			
			if ($this->view_mode == 'bind_gate') { // Подвязать шлюз
				$this->bind_gate();
			}
			
			
			if ($this->view_mode == 'delete_okbit_gate') { //Удаление шлюза
				$this->delete_okbit_gate($this->id);
				$this->redirect('?data_source=okbit_gate');
			}
			
			
			
		}
		
		if ($this->data_source == 'okbit_devices') {
			
			if ($this->view_mode == 'search_okbit_devices' || $this->view_mode == '') {//вывод списка устройств
				$this->search_okbit_devices($out, $this->parent_title, $this->parent_id);
				
			}
			
			if ($this->view_mode == 'edit_okbit_devices') { // Добавление/редактирование устройства вручную 
				$this->edit_okbit_devices($out, $this->id, $this->parent_title, $this->parent_id);
			}
			
			if ($this->view_mode == 'devices_discover' || $this->view_mode == '') {//автоматический поиск устройств
				$this->edit_okbit_devices($out, $this->id, $this->parent_title, $this->parent_id);
				
			}
			
			if ($this->view_mode == 'delete_okbit_devices') { //Удаление устройства
				$this->delete_okbit_devices($this->id);
				$this->redirect("?data_source=okbit_devices&parent_title=$this->parent_title&parent_id=$this->parent_id");
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
	
	
	function processCycle() {
	 $this->getConfig();
	  //to-do
	 }

	 
	 
	/**
	* okbit_gate search
	*
	* @access public
	*/
	
	function search_okbit_gate(&$out) {
		
		require(DIR_MODULES.$this->name . '/okbit_gate_search.inc.php');
		
	}
	
	
	
		
	/**
	* okbit_devices search
	*
	* @access public
	*/
	
	function search_okbit_devices(&$out, $parent_title, $parent_id){
		
		require(DIR_MODULES.$this->name . '/okbit_devices_search.inc.php');
		
	}
	
	

	 
	 
	/**
	* okbit_gate edit/add
	*
	* @access public
	*/
	
	function edit_okbit_gate(&$out, $id) {

		require(DIR_MODULES.$this->name . '/okbit_gate_edit.inc.php');	
	}
	
	
	
	/**
	* okbit_discover_gate
	*
	* @access public
	*/
	
	function discover_gate() {
		
		$r_cmd = 255;
		
		require(DIR_MODULES.$this->name . '/lib/udp_send.php');
						
		if ($gate->st_recive == 1) $this->udp_parsing($gate->redate, $gate->ip_gate);
		
				
		$this->redirect('?');
	}
	
	
	/**
	* okbit_update_gate
	*
	* @access public
	*/
	
	function update_gate() {
		
		$r_cmd = 10;
		
		require(DIR_MODULES.$this->name . '/lib/udp_send.php');
						
		if ($gate->st_recive == 1) $this->udp_parsing($gate->redate, $gate->ip_gate);
		
				
		$this->redirect('?');
	}
	
	
	/**
	* okbit_bind_gate
	*
	* @access public
	*/
	
	function bind_gate() {
		
		$r_cmd = 64;
		
		require(DIR_MODULES.$this->name . '/lib/udp_send.php');
						
		if ($gate->st_recive == 1) $this->udp_parsing($gate->redate, $gate->ip_gate);
		
				
		$this->redirect('?');
	}
	
	
	
	
	/**
	* okbit_devices edit/add
	*
	* @access public
	*/
	
	function edit_okbit_devices(&$out, $id, $parent_title, $parent_id) {

		require(DIR_MODULES.$this->name . '/okbit_devices_edit.inc.php');
		
	}
	
	
	/**
	* okbit_devices_discover
	*
	* @access public
	*/
	
	function okbit_devices_discover(&$out, $id, $parent_title, $parent_id) {
		
		require(DIR_MODULES.$this->name . '/okbit_devices_discover.inc.php');
		
	}
	
	
	
	/**
	* olbit_gate delete record
	*
	* @access public
	*/
	
	function delete_okbit_gate($id) {
		
		$rec = SQLSelectOne("SELECT * FROM okbit_gate WHERE ID='" . $id . "'");
		
		$rec_d = SQLSelect("SELECT * FROM okbit_devices WHERE PARENT_ID ='" . $id . "'");
		
		foreach($rec_d as $cmd) {
			SQLExec("DELETE FROM okbit_data WHERE DEVICE_ID='" . $cmd['ID'] . "'");
		}
		
		SQLExec("DELETE FROM okbit_devices WHERE PARENT_ID='" . $rec['ID'] . "'");		
		
		SQLExec("DELETE FROM okbit_gate WHERE ID='" . $id . "'");
		
	}
	
	
	/**
	* olbit_gate delete record
	*
	* @access public
	*/
	
	function delete_okbit_devices($id) {
		
		$rec = SQLSelectOne("SELECT * FROM `okbit_devices` WHERE ID='" .$id. "'");
		
		SQLExec("DELETE FROM okbit_data WHERE DEVICE_ID='" . $rec['ID'] . "'");
		
		SQLExec("DELETE FROM `okbit_devices` WHERE ID='" . $rec['ID'] . "'");
		
	}
	
	 
	 
	/**
	* propertySetHandle
	*
	* Обработчик привязанных свойств и методов
	*
	* @access private
	*/
	
	function propertySetHandle($object, $property, $value) {
	
		$this->getConfig();
		
		$ip_serv = $this->config['API_IP'];
				
		$properties = SQLSelectOne("SELECT * FROM `okbit_data` WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND  LINKED_PROPERTY LIKE '".DBSafe($property)."'");
		
		$rs_id = SQLSelectOne("SELECT * FROM `okbit_devices` WHERE ID='".DBSafe($properties['DEVICE_ID'])."'");
		
		$gate_sh = SQLSelectOne("SELECT * FROM `okbit_gate` WHERE ID=".(int)$rs_id['PARENT_ID']);

		$properties['VALUE'] = $value;
		$properties['UPDATED'] = date('Y-m-d H:i:s');
		SQLUpdate('okbit_data', $properties);
		
		$r_cmd = 30;
		
		DebMes('function SetHandle ' . PHP_EOL, 'okbit');
		
		require(DIR_MODULES.$this->name . '/lib/udp_send.php');
		
	}
	
	
	
	/**
	* processCommand
	*
	* Присвоение значения свойст в зависимости от полученного пакета от шлюза
	*
	* @access private
	*/
	
	function processCommand($device_id, $command, $value, $params = 0) {
		
		
		$cmd_rec = SQLSelectOne("SELECT * FROM `okbit_data` WHERE DEVICE_ID=".(int)$device_id." AND TITLE LIKE '".DBSafe($command)."'");
		
		
		if (!$cmd_rec['ID']) {
			$cmd_rec = array();
			$cmd_rec['TITLE'] = $command;
			$cmd_rec['DEVICE_ID'] = $device_id;
		}

		$old_value = $cmd_rec['VALUE'];
		$cmd_rec['VALUE'] = $value;
		$cmd_rec['UPDATED'] = date('Y-m-d H:i:s');
		SQLUpdate('okbit_data', $cmd_rec);

		
		if ($cmd_rec['LINKED_OBJECT'] && $cmd_rec['LINKED_PROPERTY']) {
			setGlobal($cmd_rec['LINKED_OBJECT'] . '.' . $cmd_rec['LINKED_PROPERTY'], $value, array($this->name => '0'));
		}
		
		if ($cmd_rec['LINKED_OBJECT'] && $cmd_rec['LINKED_METHOD']) {
			if (!is_array($params)) {
				$params = array();
			}
			$params['VALUE'] = $value;
			callMethodSafe($cmd_rec['LINKED_OBJECT'] . '.' . $cmd_rec['LINKED_METHOD'], $params);
		}
		

	}
	
	
	/**
	* udp_parsing
	*
	* Парсинг полученных данных
	*
	* @access private
	*/	
	
	function udp_parsing($buf_cmd, $gate_ip){ //Парсинг полученного UDP-пакета
	
	require(DIR_MODULES.$this->name . '/lib/udp_package.php');
	
	
	}
	
	

	
 
 
	 /**
	* Install
	*
	* Module installation routine
	*
	* @access private
	*/
	function install($data='') {
		 
		setGlobal('cycle_okbitControl', 'restart');		 
		 
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
	* Install
	*
	* Module Install
	*
	* @access public
	*/
	 
 
 function dbInstall($data = '') {

		$data = <<<EOD
			okbit_gate: ID int(10) unsigned NOT NULL auto_increment
			okbit_gate: TITLE varchar(255) NOT NULL DEFAULT ''
			okbit_gate: IP varchar(255) NOT NULL DEFAULT ''
			okbit_gate: SN varchar(255) NOT NULL DEFAULT ''
			okbit_gate: VER varchar(255) NOT NULL DEFAULT ''
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

			okbit_data: ID int(10) unsigned NOT NULL auto_increment
			okbit_data: TITLE varchar(255) NOT NULL DEFAULT ''
			okbit_data: VALUE varchar(255) NOT NULL DEFAULT ''
			okbit_data: DEVICE_ID int(10) unsigned NOT NULL DEFAULT 0
			okbit_data: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
			okbit_data: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
			okbit_data: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
			okbit_data: UPDATED datetime

EOD;
		
		parent::dbInstall($data);
	}
 
 
 
}

