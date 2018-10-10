<?php

/*
	Class udp_packet_okbit 	
   - сборка UDP - пакета
   - парсинг UDP - пакета					 
   
*/


class Udp_packet {
	
	public $data_packet;	
	public $length;
	public $sub_id;
	public $id;
	public $device;
	public $cmd;
	public $subto_id;
	public $to_id;
	public $value1;
	public $value2;
	public $value3;
	public $checksum;	
	public $data_eror;
	public $data_reply;
	public $ip_server;

	
	
	public function __construct ($ip_server=NULL, $sub_id = NULL, $id = NULL, $device = NULL, $cmd = NULL, $subto_id = NULL, $to_id = NULL, $value1 = NULL, 							$value2 = NULL, $value3 = NULL, $value4 = NULL)	{
		
		$this->ip_server = $ip_server;
		$this->sub_id = $sub_id;
		$this->id = $id;
		$this->device = $device;
		$this->cmd = $cmd;
		$this->subto_id = $subto_id;
		$this->to_id = $to_id;
		$this->value1 = $value1;
		$this->value2 = $value2;
		$this->value3 = $value3;
		$this->value4 = $value4;
	}


	public function udp_msg_packet() {  //Функция сборки пакета
		
		
		if ($this->cmd == 10 || $this->cmd == 20 || $this->cmd == 21 || $this->cmd == 22 || $this->cmd == 25 || $this->cmd == 26 ) { // Запрос без переменной только команда
			$length = 9;
		}
		
		
		else if ($this->cmd == 23 || $this->cmd == 24 ) { // Считать статус  - передается 1 парматр
			$length = 11;
		}
				
		
		else if ($this->cmd == 30){// Присвоение одного значение ОЗУ, передается два параметра:  адрес канала ОЗУ, значение)
			$length = 13;	
		}
		
		else if ($this->cmd == 31) { // Присвоение двух значений ОЗУ, передается три параметра: адрес канала ОЗУ, значение 1, значение 2)
			$length = 15;
		}
		
		else if ($this->cmd == 13) { // Присвоение двух значений ОЗУ, передается три параметра: адрес канала ОЗУ, значение 1, значение 2)
			$length = 17;
		}
		
		
		$date_array = array(   // Собираем массив данных для строки UDP -запроса		
			"title"=>$this->val_set_edit("OKBIT-UDP")[0], 			// Текствое собщение протокола
			"s_cod" => sprintf('%02X', 0xAAAA),            			// Стартовый ярлык
			"length" => sprintf('%02X', $length),					// Длины сообщения
			"sub_id" => sprintf('%02X', $this->sub_id),				// Sub ID отправителя
			"id" => sprintf('%02X', $this->id),						// ID отправителя
			"device_HI"=> sprintf('%02X', $this->device >> 8),		// Вырхний байт кода модуля
			"device_LOW"=> sprintf('%02X', $this->device & 0xFF),	// Нижний байт кода модуля
			"cmd_HI"=> sprintf('%02X', $this->cmd >> 8),			// Верхний байт команды
			"cmd_LOW"=> sprintf('%02X', $this->cmd & 0xFF),			// Нижний байт команды
			"subto_id"=> sprintf('%02X', $this->subto_id),			// Sub ID получателя
			"to_id"=> sprintf('%02X', $this->to_id),				// ID получателя
			"val_HI1"=> sprintf('%02X', $this->value1 >> 8),		// Верхний регистр первого значения
			"val_LOW1"=> sprintf('%02X', $this->value1 & 0xFF),		// Нижний регистр первого значения
			"val_HI2"=> sprintf('%02X', $this->value2 >> 8),		// Верхний регистр второго значения
			"val_LOW2"=> sprintf('%02X', $this->value2 & 0xFF),		// Нижний регистр второго значения
			"val_HI3"=> sprintf('%02X', $this->value3 >> 8),		// Верхний регистр третьего значения
			"val_LOW3"=> sprintf('%02X', $this->value3 & 0xFF),		// Нижний регистр третьего значения
			"val_HI4"=> sprintf('%02X', $this->value4 >> 8),		// Верхний регистр четвертого значения
			"val_LOW4"=> sprintf('%02X', $this->value4 & 0xFF),		// Нижний регистр четвертого значения
			);
				
				
				
		$this->checksum =   ($this->val_set_edit("OKBIT-UDP")[1]) + 340 + 
							hexdec($date_array['length']) + hexdec($date_array['sub_id']) + hexdec($date_array['id']) +	
							hexdec($date_array['device_HI']) + hexdec($date_array['device_LOW']) + hexdec($date_array['cmd_HI']) + 
							hexdec($date_array['cmd_LOW']) + hexdec($date_array['subto_id']) + hexdec($date_array['to_id']);
		
		if ($this->cmd == 20){ // широковещательный адрес
			$date_array['to_id'] = sprintf('%02X', 255);	
		}
		
		if ($this->cmd == 23 || $this->cmd == 24 ){
			$this->checksum = 	$this->checksum + hexdec($date_array['val_HI1']) + hexdec($date_array['val_LOW1']);
		}
		
		else if ($this->cmd == 30) {
			$this->checksum = 	$this->checksum + hexdec($date_array['val_HI1']) + hexdec($date_array['val_LOW1']) +
								hexdec($date_array['val_HI2']) + hexdec($date_array['val_LOW2']);
			
		}
		
		else if ($this->cmd == 31) {
			$this->checksum = 	$this->checksum + hexdec($date_array['val_HI1']) + hexdec($date_array['val_LOW1']) +
								hexdec($date_array['val_HI2']) + hexdec($date_array['val_LOW2']) +
								hexdec($date_array['val_HI3']) + hexdec($date_array['val_LOW3']);
			
		}
		
		else if ($this->cmd == 13) {
			$this->checksum = 	$this->checksum + hexdec($date_array['val_HI1']) + hexdec($date_array['val_LOW1']) +
								hexdec($date_array['val_HI2']) + hexdec($date_array['val_LOW2']) +
								hexdec($date_array['val_HI3']) + hexdec($date_array['val_LOW3']) +
								hexdec($date_array['val_HI4']) + hexdec($date_array['val_LOW4']);
			
		}
			
			
		$date_array['checksum_HI'] = sprintf('%02X', $this->checksum >> 8);
		$date_array['checksum_LOW'] = sprintf('%02X', $this->checksum & 0xFF);
				
		$data_packet=NULL;
			
		//собираем пакет воедино
			
		$data_packet =  $date_array['title'] .
						$date_array['s_cod'] . $date_array['length'] . $date_array['sub_id'] . $date_array['id'] .
						$date_array['device_HI'] . $date_array['device_LOW'] . $date_array['cmd_HI'] . $date_array['cmd_LOW'] .
						$date_array['subto_id'] . $date_array['to_id'];
		
		if ($this->cmd == 23 || $this->cmd == 24 ){
			$data_packet = 	$data_packet . $date_array['val_HI1']  . $date_array['val_LOW1'];
		}
		
		else if ($this->cmd == 30) {
			$data_packet = 	$data_packet . $date_array['val_HI1']  . $date_array['val_LOW1'] . 
							$date_array['val_HI2'] . $date_array['val_LOW2'];
			
		}
		
		else if ($this->cmd == 31) {
			$data_packet = 	$data_packet . $date_array['val_HI1']  . $date_array['val_LOW1'] . $date_array['val_HI2'] .
							$date_array['val_LOW2'] . $date_array['val_HI3'] . $date_array['val_LOW3'];
			
		}
		
		else if ($this->cmd == 13) {
			$data_packet = 	$data_packet . $date_array['val_HI1']  . $date_array['val_LOW1'] . $date_array['val_HI2'] .
							$date_array['val_LOW2'] . $date_array['val_HI3'] . $date_array['val_LOW3'] .
							$date_array['val_HI4'] . $date_array['val_LOW4'];
			
		}
		
				
		$data_packet = 	$data_packet . $date_array['checksum_HI'] . $date_array['checksum_LOW'];
		
		echo $data_packet .PHP_EOL;
		
		return ($data_packet);
	}
	
	public function val_set_edit($val_edit=NULL){//функция преобразования строки в hex - массив, для текстовых данных
		
		$crc = NULL;
		
		for ($i = 0; $i < strlen($val_edit); $i++) {
			$val_arr[$i]  = substr($val_edit, $i,1);
		}
		
		$data_packet = NULL;
		
		for ($i = 0; $i <  count($val_arr); $i++) {
			$temp = sprintf('%02X', (ord($val_arr[$i])));
			$crc = $crc + hexdec($temp);
			$data_packet = $data_packet . $temp;			
		}

		
		return array($data_packet, $crc);		
	}


	public function msgParse($msg) {  //функция парсинга пакета полученного ответа
		
		
	}

}


?>