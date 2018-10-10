<?php

	/**
	* OkBit (UDP-пакеты)
	* @package project
	* @author Wizard <foxvlad@yandex.ru>
	* @copyright http://okbit.ru (c)
	* @version 0.1 (wizard, [Feb 04, 2018])





	-------- Значение команд в десятичном/шестнадцатиричном формате ---------

	0010/A - считать SN шлюза, версию прощивки
	0011/B - ответ все хорошо
	0012/С - ответ ошибка (1 - колличество ошибок в ОЗУ, 2- код последней ошибки)
	0013/D - передать SN шлюза, версию прощивки (1 - Значение прошивки 1, 2 - значение прошивки 2, 3 - серийный номер 1, 4 - серийный номер 2)

	0020/14 - Поиск всех онлайн устройств
	0021/15 - Считать/передать тип устройства, версию прошивки (1- тип устройства, 2 - версия)
	0022/16 - Считат/передать коментарий устройства(n-е количество буквенный коментарий)
	0023/17 - Считать/передать статус входа (1 - адрес входа, 2- значение)
	0024/18 - Считать/передать значение ячейки ОЗУ (1 - адрес ячейки, 2 - значение)
	0025/19 - Считать/передать все значения ячеек ОЗУ
	0026/1A - Считать/передать количество ошибок на шине ( 1- кол-воошибок)

	0030/1E - Присвоение значения ОЗУ (1 - адрес канал, 2 - значение)
	0031/1F - Присвоение двух значений ОЗУ (1 - адрес канала, 2 - значение 1, 3 - значение 2)

	0040/28 - Смена Sub ID (1 - новый адрес подсети)
	0041/29 - Смена ID (1 - новый адрес устройства)

	0055/37 - Запись текстового значения (примечание для модуля)

	0060/3C - Запись значения настройки в модуль (1 - адрес канала, 2 - значение )
	0061/3D - Запись значений настройки в модуль (1 - адрес канала, 2 - значение 1, 3- значение 2)
	0062/3D - смена IP шлюза (1 - адрес, 2 - адрес, 3 - адрес, 4 - адрес)
	0063/3F - Работа шлюза по DHCP


	0070/46 - Запись значения сценария (1 - адрес канала,  2 - N -сценария, 3 - Sub ID получателя, 4 - id получателя , 5 - адрес канала ОЗУ,
				6 - значение 1, 7 - значение 2)

	0255/FF - автоматический поиск шлюза


*/


		//Вычисление номера регистра (Адреса канала)
	if ($r_cmd == 30){
		if ($rs_id['DEVICE'] == 6001){//МУС-8
			$cmd_out = explode(',',DATE_6001);
		}

		else if ($rs_id['DEVICE'] == 6002){//МОС-6
			$cmd_out = explode(',',DATE_6002);
		}

		else if ($rs_id['DEVICE'] == 6003){//УМА-8
			$cmd_out = explode(',',DATE_6003);
		}

		else if ($rs_id['DEVICE'] == 6004){//МДВ-4
			$cmd_out = explode(',',DATE_6004);
		}

		$s = 1;
		foreach($cmd_out as $xxx) {
			if ($xxx == $properties['TITLE']) $dev_in = $s;
			$s++;
		}
	}



	//Reduce errors
	error_reporting(~E_WARNING);





	if ($r_cmd == 255) {    // Данные для формирование широковещательного пакета на отправку
		$udppacket = new Udp_packet(0, 0, 65534, 255, 0, 0, 0, 0);
		$gate = new Udp_gate('255.255.255.255', 6400, '0.0.0.0', 6600, $this->config['API_LOG_DEBMES']); //задаем свойства класса адрес и порт шлюза и порт модуля udp_send
	}
	
	else if ($r_cmd == 10) {    // Данные для формирование пакета для запроса информации ошлюзе
		$udppacket = new Udp_packet(0, 0, 65534, 10, 0, 0, 0, 0);
		$gate = new Udp_gate('192.168.1.35', 6400, '0.0.0.0', 6600, $this->config['API_LOG_DEBMES']); //задаем свойства класса адрес и порт шлюза и порт модуля udp_send
	}

	else if ($r_cmd == 30)  {  // Данные для формирование пакета на отправку
		$udppacket = new Udp_packet($rs_id['SUB_ID'], 0, 65534, $r_cmd, $rs_id['SUB_ID'], $rs_id['DEVICE_ID'], $dev_in, $value);
		$gate = new Udp_gate($gate_sh['IP'], 6400, '0.0.0.0', 6600, $this->config['API_LOG_DEBMES']); //задаем свойства класса адрес и порт шлюза и порт модуля udp_send
	}
	


	
	
	
	$data_send = $udppacket->udp_msg_packet(); //сборка UDP OkBit пакета

	$gate->sock_create(); //Создание UDP сокета
	$gate->sockSetTimeout(); //Установка таймаута для получения ответа
	if ($r_cmd == 255) {
		$gate->sockSetBroadcast(); //Установки для широковешательной отправки
	}
	$gate->udp_send($data_send); // отправка пакета





	/**
	* Udp_gate
	*
	* Обработчик создания UDP - сокета
	*
	* @access private
	*/

	class Udp_gate {

		public $ip_gate;
		public $port_gate;
		public $ip_udp_send;
		public $port_udp_send;
		public $sock;
		public $data;
		public $redate;
		public $debug;
		public $st_recive;

		public function __construct ($ip_gate='192.168.0.100', $port_gate=6400, $ip_udp_send='0.0.0.0', $port_udp_send=6600, $debug=true){
			$this->ip_gate = $ip_gate;
			$this->port_gate = $port_gate;
			$this->ip_udp_send = $ip_udp_send;
			$this->port_udp_send = $port_udp_send;
			$this->debug = $debug;

			if ($this->debug){
				echo $this->ip_gate. "  " .$this->port_gate. "  " .$this->ip_udp_send. "  " .$this->port_udp_send. "  " .$this->debug. "  " .PHP_EOL;

			}

		}

		public function sockSetTimeout($timeout = 2) {// установка тайм аута для отправке получения пакета, в случае если шлюз не доступен

			if (!socket_set_option($this->sock, SOL_SOCKET, SO_RCVTIMEO, array("sec" => $timeout, "usec" => 0))) {
				$errorcode = socket_last_error();
				$errormsg = socket_strerror($errorcode);

				if ($this->debug){
					echo "Error setting timeout SO_RCVTIMEO - [socket_create()] [$errorcode] $errormsg" . PHP_EOL;
				}
			}

			else {
				if ($this->debug) {
					echo 'Timeout SO_RCVTIMEO successfully set' . PHP_EOL;
				}

			}

		}


		/*
		Установка параметров сокета - броадкаст.
		*/

		public function sockSetBroadcast() {
			

			if (!socket_set_option($this->sock, SOL_SOCKET, SO_BROADCAST, 1)) {
				$errorcode = socket_last_error();
				$errormsg = socket_strerror($errorcode);

				if ($this->debug) {
					echo "Error setting broadcast SO_BROADCAST - [socket_create()] [$errorcode] $errormsg" . PHP_EOL;
				}
			}
			else {
				if ($this->debug) {
					echo 'Broadcast SO_BROADCAST successfully set' . PHP_EOL;
				}
			}

		}


		public function sock_create() {

			if(!($this->sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
			{
				$errorcode = socket_last_error();
				$errormsg = socket_strerror($errorcode);
				die("Couldn't create socket: [$errorcode] $errormsg".PHP_EOL);
			}
			if ($this->debug){
				echo "Socket created".PHP_EOL;
			}

			// привязка исходного адреса
			if(!socket_bind($this->sock, $this->ip_udp_send, $this->port_udp_send)){
			   $errorcode = socket_last_error();
			   $errormsg = socket_strerror($errorcode);
			   die("Could not bind socket : [$errorcode] $errormsg" .PHP_EOL);
			}
			if ($this->debug){
				echo "Socket bind OK".PHP_EOL;
			}

		}


		public function udp_send($udpPacket = NULL, $hash_msg = NULL){

			if ($udpPacket != NULL){
				$this->data = $udpPacket;
			}
			else { //если сообщение пустое вывести на экран запрос сообщения, только для отладки
				echo 'Enter a message to send : ';
				$this->data = fgets(STDIN);
			}

			//Отправка сообщения на шлюз
			if( !socket_sendto($this->sock, $this->data, strlen($this->data) , 0 ,  $this->ip_gate,  $this->port_gate))
			{
				$errorcode = socket_last_error();
				$errormsg = socket_strerror($errorcode);

				die("Could not send data: [$errorcode] $errormsg \n");
			}


			//Получение ответа о шлюза
			if( (socket_recvfrom($this->sock , $this->redate , 250 , 0, $remote_ip, $remote_port )) === false){
				$errorcode = socket_last_error();
				$errormsg = socket_strerror($errorcode);

				//die("Could not receive data: [$errorcode] $errormsg".PHP_EOL);
				 if ($this->debug) DebMes('ERROR: No response from gateway ' . $this->ip_gate . PHP_EOL, 'okbit');
				$this->st_recive = 0;
			}

			else {
				$this->st_recive = 1;
				 if ($this->debug) DebMes( date("H:i:s") . ' - answer ' . $this->redate . PHP_EOL, 'okbit');
			}



			$this->ip_gate = $remote_ip;


			socket_shutdown($this->sock, 2);
			socket_close($this->sock);
		}

	}




	/*

	Class udp_packet
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



		public function __construct ($sub_id = NULL, $id = NULL, $device = NULL, $cmd = NULL, $subto_id = NULL, $to_id = NULL, $value1 = NULL, $value2 = NULL, $value3 = NULL, $value4 = NULL)	{

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


			if ( $this->cmd == 20 || $this->cmd == 21 || $this->cmd == 22 || $this->cmd == 25 || $this->cmd == 26 || $this->cmd == 255 ) { // Запрос без переменной только команда
				$length = 9;
			}

			else if ($this->cmd == 23 || $this->cmd == 24 ) { // Считать статус  - передается 1 парматр
				$length = 11;
			}

			else if ($this->cmd == 10 || $this->cmd == 30){// Присвоение одного значение ОЗУ, передается два параметра:  адрес канала ОЗУ, значение)
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
				"val_HI4"=> sprintf('%02X', $this->value4 >> 8),		// Верхний регистр третьего значения
				"val_LOW4"=> sprintf('%02X', $this->value4 & 0xFF),		// Нижний регистр третьего значения
				);


			$this->checksum =  ($this->val_set_edit("OKBIT-UDP")[1]) + 340 +
								hexdec($date_array['length']) + hexdec($date_array['sub_id']) + hexdec($date_array['id']) +
								hexdec($date_array['device_HI']) + hexdec($date_array['device_LOW']) + hexdec($date_array['cmd_HI']) +
								hexdec($date_array['cmd_LOW']) + hexdec($date_array['subto_id']) + hexdec($date_array['to_id']);

			if ($this->cmd == 20){ // широковещательный адрес
				$date_array['to_id'] = sprintf('%02X', 255);
			}

			if ($this->cmd == 23 || $this->cmd == 24 ){
				$this->checksum = 	$this->checksum + hexdec($date_array['val_HI1']) + hexdec($date_array['val_LOW1']);
			}

			else if ($this->cmd == 10 || $this->cmd == 30) {
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


	}


?>


