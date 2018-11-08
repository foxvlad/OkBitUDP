<?php

/*
	Class Send_UDP_OkBit	
	   - Создание UDP Сокета
	   - задание параметров
	   - отправка данных посредством сокета
*/



class Send_UDP {
	
	public $ip_gate;
	public $port_gate;
	public $ip_udp_send;
	public $port_udp_send;
	public $sock;
	public $data;
	public $redate;
	public $debug;
	public $st_recive;
	public $redate_ip;
	
	public function __construct ($ip_gate='255.255.255.255', $port_gate=6400, $ip_udp_send='0.0.0.0', $port_udp_send=6600, $debug=false){
		$this->ip_gate = $ip_gate;
		$this->port_gate = $port_gate;
		$this->ip_udp_send = $ip_udp_send;
		$this->port_udp_send = $port_udp_send;
		$this->debug = $debug;
		
		if ($this->debug){	
			DebMes ("$this->ip_gate $this->port_gate $this->ip_udp_send $this->port_udp_send $this->debug", 'okbit');
		}			
	}
		
	
	public function sock_create() { //Создание udp сокета	  
		if(!($this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP))){
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			if ($this->debug) DebMes ("Couldn't create socket: [$errorcode] $errormsg", 'okbit');
		} 
		else if ($this->debug) DebMes ("Socket created", 'okbit');
	}
	
	public function sock_bind() { // привязка исходного адреса
		if(!socket_bind($this->sock, $this->ip_udp_send, $this->port_udp_send)){
		   $errorcode = socket_last_error();
		   $errormsg = socket_strerror($errorcode);
		   if ($this->debug) DebMes ("Could not bind socket : [$errorcode] $errormsg", 'okbit');
		}
		
		else if ($this->debug) DebMes ("Socket bind OK", 'okbit');
	}
	
	public function sockSetTimeout($timeout = 1) { // установка тайм аута для отправке получения пакета, в случае если шлюз не доступен
		if (!socket_set_option($this->sock, SOL_SOCKET, SO_RCVTIMEO, array("sec" => $timeout, "usec" => 0))) {
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			if ($this->debug)DebMes ("Error setting timeout SO_RCVTIMEO - [socket_create()] [$errorcode] $errormsg", 'okbit');
		}			
		else if ($this->debug) DebMes ('Timeout SO_RCVTIMEO successfully set', 'okbit');		
	}
	
	public function sockSetBroadcast() { //Установка параметров сокета - броадкаст.
		if (!socket_set_option($this->sock, SOL_SOCKET, SO_BROADCAST, 1)) {
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			if ($this->debug) DebMes ("Error setting broadcast SO_BROADCAST - [socket_create()] [$errorcode] $errormsg", 'okbit');
		} else if ($this->debug) DebMes ('Broadcast SO_BROADCAST successfully set', 'okbit');
	}
	
	
	public function udp_send($udpPacket){			
		$this->data = $udpPacket;	
				 
		//Отправка сообщения на шлюз
		if(!($bytes = socket_sendto($this->sock, $this->data, strlen($this->data) , 0 ,  $this->ip_gate,  $this->port_gate))){
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			if ($this->debug) DebMes ("Cannot send data to socket [$errorcode] $errormsg", 'okbit');
			
		} else if ($this->debug){	
			DebMes (">>>>> $this->data", 'okbit');
			DebMes (">>>>> Sent $bytes bytes to socket", 'okbit');
		}
		
			
	
		$buf = '';
		$count = 0;
	
		while ($bytes = @socket_recvfrom($this->sock, $buf, 4096, 0, $remote_ip, $remote_port)) {
			
			$count += 1;
			
			if ($buf != '') {
				if ($this->debug) {
					DebMes ("$count - <<<<< Reply received from IP $remote_ip , port $remote_port", 'okbit');
					DebMes ("<<<<< $bytes bytes received", 'okbit');
					DebMes ("<<<<< $buf", 'okbit');
				}
				
			$this->parsing_packege($buf, $remote_ip);

				
			} else {
				$errorcode = socket_last_error();
				$errormsg = socket_strerror($errorcode);
				if ($this->debug) DebMes ("Error reading socket [$errorcode] $errormsg", 'okbit');
				$this->st_recive = 0;
			}
			
		}
		
		
		
		socket_shutdown($this->sock, 2);
		socket_close($this->sock);		
	}
	
	
	
		public function udp_send_no_remote($udpPacket){			
		$this->data = $udpPacket;	
				 
		//Отправка сообщения на шлюз
		if(!($bytes = socket_sendto($this->sock, $this->data, strlen($this->data) , 0 ,  $this->ip_gate,  $this->port_gate))){
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			if ($this->debug) DebMes ("Cannot send data to socket [$errorcode] $errormsg", 'okbit');
			
		} else if ($this->debug){	
			DebMes (">>>>> $this->data", 'okbit');
			DebMes (">>>>> Sent $bytes bytes to socket", 'okbit');
		}			
	
		$buf = '';
		$count = 0;
	
		socket_shutdown($this->sock, 2);
		socket_close($this->sock);		
	}
	
	
	
	
	
	/**
	* parsing_packege
	*
	* разбор полученного пакета
	*
	* @access private
	*/	
	
	public function parsing_packege($buf_cmd, $gate_ip){
		if ($this->debug) DebMes('<<<< ' . $buf_cmd . ' | IP - ' .  $gate_ip, 'okbit');

		$arr = str_split($buf_cmd, 2);
		$arr_count = count($arr);

		$udp_package = array(// разбираем массив
			"mes" => chr(hexdec($arr[0])) . chr(hexdec($arr[1])) . chr(hexdec($arr[2])) . chr(hexdec($arr[3])) . chr(hexdec($arr[4])) . chr(hexdec($arr[5])) .
					chr(hexdec($arr[6])) . chr(hexdec($arr[7])) . chr(hexdec($arr[8])),
			"s_cod" => $arr[9] . $arr[10],
			"length" => hexdec($arr[11]),
			"sub_id" => hexdec($arr[12]),
			"id" => hexdec($arr[13]),
			"device"=> hexdec($arr[14] . $arr[15]),
			"cmd"=> hexdec($arr[16] . $arr[17]),
			"subto_id"=> hexdec($arr[18]),
			"to_id"=> hexdec($arr[19]),
		);

		if ($udp_package['length'] == 11 || $udp_package['length'] == 13 || $udp_package['length'] == 15 || $udp_package['length'] == 17 || $udp_package['length'] == 19) {
			$udp_package['vol_1'] = hexdec($arr[20] . $arr[21]);
		}
		if ($udp_package['length'] == 13 || $udp_package['length'] == 15 || $udp_package['length'] == 17 || $udp_package['length'] == 19) {
			$udp_package['vol_2'] = hexdec($arr[22] . $arr[23]);
		}
		if ($udp_package['length'] == 15 || $udp_package['length'] == 17 || $udp_package['length'] == 19) {
			$udp_package['vol_3'] = hexdec($arr[24] . $arr[25]);
		}
		if ($udp_package['length'] == 17 || $udp_package['length'] == 19) {
			$udp_package['vol_4'] = hexdec($arr[26] . $arr[27]);
		}
		if ($udp_package['length'] == 19) {
			$udp_package['vol_5'] = hexdec($arr[28] . $arr[29]);
		}
		
	
		$check_in = 0;
		for ($i = 0; $i < $arr_count - 2; $i++){ // считаем чек сум полученного пакета
			$check_in = $check_in + hexdec($arr[$i]);
		}
		if ($check_in == hexdec($arr[$arr_count - 2] . $arr[$arr_count - 1])){ //если чек сум правельный производим дальнейшию обработку
			
			
			//Дальнейшие действия в зависимости от пришедшей команды в запросе (см. список команд  в udp_send.php)
			if ($udp_package['cmd'] == 30){ //запуск функции присвоения значениея свойства объекта
				
				$cmd_gate = SQLSelectOne("SELECT * FROM `okbit_gate` WHERE IP='".DBSafe($gate_ip)."'");
								
				$cmd_gate['STATUS'] = 1;
				$cmd_gate['UPDATED'] = date('Y-m-d H:i:s');
				SQLUpdate('okbit_gate', $cmd_gate);
				
				if($cmd_gate['MOD'] == '6000'){				
					$cmd_devices = SQLSelectOne("SELECT * FROM `okbit_devices` WHERE PARENT_ID='".(int)$cmd_gate['ID']."' AND SUB_ID='".(int)$udp_package['subto_id']. "' AND DEVICE_ID='".(int)$udp_package['to_id']. "'");
					

					
					
					if ($cmd_devices){
						$cmd_devices['STATUS'] = 1;
						$cmd_devices['UPDATED'] = date('Y-m-d H:i:s');
						SQLUpdate('okbit_devices', $cmd_devices);
					
					
					
						if ($cmd_devices['DEVICE'] == 6001){
							$cmd_dev = explode(',',DATA_6001);
						}
						else if ($cmd_devices['DEVICE'] == 6002){
							$cmd_dev = explode(',',DATA_6002);
						}
						else if ($cmd_devices['DEVICE'] == 6003){
							$cmd_dev = explode(',',DATA_6003);
						}
						else if ($cmd_devices['DEVICE'] == 6004){
							$cmd_dev = explode(',',DATA_6004);
						}
						else if ($cmd_devices['DEVICE'] == 6005){
							$cmd_dev = explode(',',DATA_6005);
						}
						else if ($cmd_devices['DEVICE'] == 6006){
							$cmd_dev = explode(',',DATA_6006);
						}
						else if ($cmd_devices['DEVICE'] == 6007){
							$cmd_dev = explode(',',DATA_6007);
						}
						else if ($cmd_devices['DEVICE'] == 6008){
							$cmd_dev = explode(',',DATA_6008);
						}					
						$com_reg = $cmd_dev[$udp_package['vol_1'] - 1]; //вычисляем топик okbit_date по номмеру регистра
						
						$this->processCommand($cmd_gate['MOD'],$cmd_devices['ID'], $com_reg, $udp_package['vol_2']);//передаем данные на присвоение 
					}	
					if ($this->debug)DebMes('UDP parsing: GATE - '. $cmd_gate['MOD'] .'  DEVICE_ID - '. $cmd_devices['ID']. ' REG - ' .$com_reg. ' VOL - ' .$udp_package['vol_2'], 'okbit');
				}
				
				else if ($udp_package['sub_id'] =='0' && $udp_package['id'] =='0' && $udp_package['subto_id'] =='0' && $udp_package['to_id'] =='0'){ 
				
					if ($cmd_gate['MOD'] == 7001){
						$cmd_dev = explode(',',DATA_7001);
					}
					else if ($cmd_gate['MOD'] == 7002){
						$cmd_dev = explode(',',DATA_7002);
					}
					else if ($cmd_gate['MOD'] == 7003){
						$cmd_dev = explode(',',DATA_7003);
					}
					else if ($cmd_gate['MOD'] == 7004){
						$cmd_dev = explode(',',DATA_7004);
					}
					else if ($cmd_gate['MOD'] == 7005){
						$cmd_dev = explode(',',DATA_7005);
					}
					else if ($cmd_gate['MOD'] == 7006){
						$cmd_dev = explode(',',DATA_7006);
					}
					else if ($cmd_gate['MOD'] == 7007){
						$cmd_dev = explode(',',DATA_7007);
					}
					else if ($cmd_gate['MOD'] == 7008){
						$cmd_dev = explode(',',DATA_7008);
					}					
					$com_reg = $cmd_dev[$udp_package['vol_1'] - 1]; //вычисляем топик okbit_date по номмеру регистра
					
					$this->processCommand($cmd_gate['MOD'], $cmd_gate['ID'], $com_reg, $udp_package['vol_2']);//передаем данные на присвоение 
					
					
					if ($this->debug)DebMes('UDP parsing: GATE - '. $cmd_gate['MOD'] . ' ID - '. $cmd_gate['ID'] . ' REG - ' .$com_reg. ' VOL - ' .$udp_package['vol_2'], 'okbit');
				}
			}

			else if ($udp_package['cmd'] == 13){ // Получение серийного номера шлюза и версии прошивки
				if ($this->debug) {
					if ($this->debug)DebMes(date("H:i:s") . " запуск функции обработки информации о шлюзе",'okbit');
				}
				if ($this->debug) {
					if ($this->debug)DebMes(date("H:i:s") . ' VER: ' . $udp_package['vol_1'] . '.' . $udp_package['vol_2'] . ' SN: ' . $udp_package['vol_3'] . sprintf("%05d", $udp_package['vol_4']), 'okbit');
				}

				$table_name = 'okbit_gate';
				$rec = SQLSelectOne("SELECT * FROM $table_name WHERE SN='".DBSafe(sprintf("%04X", $udp_package['vol_3']) . sprintf("%04X", $udp_package['vol_4']))."'");
				
				$rec['STATUS'] = 1;
				$rec['UPDATED'] = date('Y-m-d H:i:s');
				$rec['VER'] = $udp_package['vol_1'] . '.' . $udp_package['vol_2'];
				
	
				
				if ($rec['SN']) {
					$rec['IP'] = $gate_ip;
					$rec['SN'] = sprintf("%04X", $udp_package['vol_3']) . sprintf("%04X", $udp_package['vol_4']);
					if ($this->debug) DebMes('Auto params for gate ' . $deb_title . ' with IP ' . $rec['IP'], 'okbit');
					$rec['SN'] = SQLUpdate($table_name, $rec);
				}

				else {
					$rec = SQLSelectOne("SELECT * FROM $table_name WHERE IP='".DBSafe($gate_ip)."'");
					$rec['STATUS'] = 1;
					$rec['UPDATED'] = date('Y-m-d H:i:s');
					$rec['VER'] = $udp_package['vol_1'] . '.' . $udp_package['vol_2'];
					if ($rec['IP'] && $rec['SN'] == '' && $rec['MOD'] == $udp_package['device']) {
						$rec['SN'] = sprintf("%04X", $udp_package['vol_3']) . sprintf("%04X", $udp_package['vol_4']);
						$rec['IP'] = SQLUpdate($table_name, $rec);
					}
					else {
						$rec = null;
						$rec['STATUS'] = 1;
						$rec['UPDATED'] = date('Y-m-d H:i:s');
						$rec['VER'] = $udp_package['vol_1'] . '.' . $udp_package['vol_2'];
						$rec['IP'] = $gate_ip;
						$rec['MOD'] = $udp_package['device'];
						//$rec['SN'] = $udp_package['vol_3'] . sprintf("%05d", $udp_package['vol_4']);
						$rec['SN'] = sprintf("%04X", $udp_package['vol_3']) . sprintf("%04X", $udp_package['vol_4']);
						SQLInsert($table_name, $rec);
						
						if ($rec['MOD'] =='6000'){						
							if ($this->debug) DebMes('Auto add new gate ' . $deb_title . ' with IP ' . $rec['IP'], 'okbit');
						}
						else {
							
							$rec = SQLSelectOne("SELECT * FROM $table_name WHERE SN='".DBSafe($rec['SN'])."'");
							
							if ($rec['MOD'] == 7001){
								$cmd_dev = explode(',',DATA_7001);
							}
							else if ($rec['MOD'] == 7002){
								$cmd_dev = explode(',',DATA_7002);
							}
							else if ($rec['MOD'] == 7003){
								$cmd_dev = explode(',',DATA_7003);
							}
							else if ($rec['MOD'] == 7004){
								$cmd_dev = explode(',',DATA_7004);
							}
							else if ($rec['MOD'] == 7005){
								$cmd_dev = explode(',',DATA_7005);
							}
							else if ($rec['MOD'] == 7006){
								$cmd_dev = explode(',',DATA_7006);
							}
							else if ($rec['MOD'] == 7007){
								$cmd_dev = explode(',',DATA_7007);
							}
							else if ($rec['MOD'] == 7008){
								$cmd_dev = explode(',',DATA_7008);
							}	
							
							foreach($cmd_dev as $cmd) {
								$cmd_rec = array();
								$cmd_rec['TITLE'] = $cmd;
								$cmd_rec['ETHERNET'] = 1;
								$cmd_rec['DEVICE_ID'] = $rec['ID'];
								SQLInsert('okbit_data', $cmd_rec);								
							}	
							
							if ($this->debug) DebMes('Auto add new modul_'. $rec['MOD'].' DEVICE_ID '.$rec['ID'].' with IP ' . $rec['IP'], 'okbit');
						}
					}
				}
			}
			return "UDP parsing OK, Count - " . $arr_count . " Checksum - " . $check_in . " Checksum  HEX - " . $arr[$arr_count - 2] . $arr[$arr_count - 1].  " Checksum flag - Yes" ;
		}
		else return "EROR"; //если чек сумм не соответсвует, пакет битый функция вернет ошибку
		
		
	}
	
	/**
	* processCommand
	*
	* Присвоение значения свойст в зависимости от полученного пакета от шлюза
	*
	* @access private
	*/
	
	function processCommand($mod, $device_id, $command, $value = 0, $params = 0) {
		
		if($mod == '6000'){
			$cmd_rec = SQLSelectOne("SELECT * FROM `okbit_data` WHERE DEVICE_ID=".(int)$device_id." AND TITLE LIKE '".DBSafe($command)."' AND ETHERNET='0'");
		}
		else $cmd_rec = SQLSelectOne("SELECT * FROM `okbit_data` WHERE DEVICE_ID=".(int)$device_id." AND TITLE LIKE '".DBSafe($command)."' AND ETHERNET='1'");
			
			
		if ($cmd_rec['ID']) {
			if ($mod == '7002') {
				$value = ($value / 100);				
			}
							
				$cmd_rec['VALUE'] = $value;
			
			$cmd_rec['UPDATED'] = date('Y-m-d H:i:s');
			SQLUpdate('okbit_data', $cmd_rec);

			if ($cmd_rec['LINKED_OBJECT'] && $cmd_rec['LINKED_PROPERTY']) {
				
				setGlobal($cmd_rec['LINKED_OBJECT'] . '.' . $cmd_rec['LINKED_PROPERTY'], $value, array(okbit => '0'));
			}
				
			if ($cmd_rec['LINKED_OBJECT'] && $cmd_rec['LINKED_METHOD']) {
				if (!is_array($params)) {
					$params = array();
				}
				$params['VALUE'] = $value;
				callMethodSafe($cmd_rec['LINKED_OBJECT'] . '.' . $cmd_rec['LINKED_METHOD'], $params);
			}
		}

	}
	

}
?>