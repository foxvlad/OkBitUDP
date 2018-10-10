<?php
 


		//функция записи в лог полученного сообщения
	function log_write($s_mess){
		$text = date("d-m-Y H:i:s") . ' - ' .  $s_mess .PHP_EOL;
		$fp = fopen ("udp_sent_log.txt",  "a+");  //!!! лог файл
		fwrite($fp,$text);
		fclose($fp);
	}

	require_once "udp_okbit.class.php"; // Подключаем класс 
	require_once "udppacket.class.php";

	//Reduce errors
	error_reporting(~E_WARNING);

	$ip_server = getHostByName(getHostName());//локальный ip сервера мажордомо
	
	$ip_array = explode('.', $ip_server);//локальный ip сервера мажордомо
	
	//4F4B4249542D554450AAAA1100001770000D0000000100040712000504AB
	
	$udppacket = new Udp_packet($ip_array, 00, 00, 6000, 13, 0, 0, 1,0,1805,5); // Данные для формирование пакета на отправку
	
	//$gate = new Udp_gate('192.168.88.252', 6400, '0.0.0.0', 6600, true); //задаем свойства класса адрес и порт шлюза и порт модуля udp_send
	$gate = new Udp_gate('192.168.1.35', 6500, '0.0.0.0', 6600, true); //задаем свойства класса адрес и порт шлюза и порт модуля udp_send




	$data_packet = $udppacket->udp_msg_packet(); //сборка UDP OkBit пакета

	$data_send = $data_packet;
	
	
	$gate->sock_create(); //Создание UDP сокета
	$gate->sockSetTimeout(); //Установка таймаута для получения ответа
	log_write($data_send);//вызов функции записи в лог полученного сообщения
	$gate->udp_send($data_send); // отправка пакета
	
	



	
	echo "Are you sure you want to do this?  Type 'yes' to continue: ";
	$handle = fopen ("php://stdin","r");
	$line = fgets($handle);
	if(trim($line) != 'yes'){
		echo "ABORTING!\n";
		exit;
	}
	echo "\n";
	echo "Thank you, continuing...\n";


	
	
	
?>