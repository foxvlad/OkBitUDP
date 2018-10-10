<?php

/*
    php udp socket сервер

*/

	//функция записи в лог полученного сообщения
	function log_write($s_mess, $s_ip, $s_port){
		$dt = date("d-m-Y H:i:s");
		$text = $dt . ' - ' . $s_ip . ', ' . $s_port . " - " . $s_mess .PHP_EOL;
		$fp = fopen ("cycle_udp_log.txt", "a+");  //!!! лог файл
		fwrite($fp,$text);
		fclose($fp);
	}



	/**
	* udp_parsing
	*
	* Парсинг полученных данных
	*
	* @access private
	*/

	function udp_parsing($buf_cmd, $gate_ip){ //Парсинг полученного UDP-пакета


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

		if ($udp_package['length'] == 11 || $udp_package['length'] == 13 || $udp_package['length'] == 15) {
			$udp_package['vol_1'] = hexdec($arr[20] . $arr[21]);
		}

		if ($udp_package['length'] == 13 || $udp_package['length'] == 15) {
			$udp_package['vol_2'] = hexdec($arr[22] . $arr[23]);
		}

		if ($udp_package['length'] == 15) {
			$udp_package['vol_3'] = hexdec($arr[24] . $arr[25]);
		}

		$check_in = 0;

		for ($i = 0; $i < $arr_count - 2; $i++){ // считаем чек сум полученного пакета
			$check_in = $check_in + hexdec($arr[$i]);
		}




		if ($check_in == hexdec($arr[$arr_count - 2] . $arr[$arr_count - 1])){ //если чек сум правельный производим дальнейшию обработку


			if ($udp_package['cmd'] == 30) {

				if ($udp_package['vol_2'] == "1" ){
					$state_lamp = "On";
				}

				else $state_lamp = "Off";
				echo '==============================================' . PHP_EOL;
				echo PHP_EOL;
				echo '           LAMP ' . $udp_package['vol_1'] . ' - ' . $state_lamp . PHP_EOL;
				echo '==============================================' . PHP_EOL;
				echo PHP_EOL;
				return "000B";
			}

			else if ($udp_package['cmd'] == 255) { // Ответ на широковещательный запрос с VER: 1.4 SN: 181000005
				return "4F4B4249542D554450AAAA1100001770000D000000010005070D000504A7";
			}
			
			else if ($udp_package['cmd'] == 10) { // Ответ на широковещательный запрос с VER: 1.4 SN: 181000005
				return "4F4B4249542D554450AAAA1100001770000D000000010005070D000504A7";
			}
		}

		else return "EROR"; //если чек сумм не соответсвует, пакет битый функция вернет ошибку
	 }






	//Reduce errors
	error_reporting(~E_WARNING);

	//Create a UDP socket
	if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0)))
	{
		$errorcode = socket_last_error();
		$errormsg = socket_strerror($errorcode);

		die("Couldn't create socket: [$errorcode] $errormsg".PHP_EOL);
	}

	echo "Socket created".PHP_EOL;


	// привязка исходного адреса
	if( !socket_bind($sock, "0.0.0.0" , 6400) )
	{
		$errorcode = socket_last_error();
		$errormsg = socket_strerror($errorcode);

		die("Could not bind socket : [$errorcode] $errormsg".PHP_EOL);
	}

	echo "Socket bind OK".PHP_EOL;



	//Код петли для приема мульти сообщений
	while(1)
	{

		echo "Waiting for data ...".PHP_EOL;

		//Receive some data
		$r = socket_recvfrom($sock, $buf, 512, 0, $remote_ip, $remote_port);
		echo "Number of characters msg  - " . strlen($buf).PHP_EOL;
		$now = date("d-m-Y H:i:s");
		echo "$now - $remote_ip : $remote_port -- " . $buf .PHP_EOL;

		log_write($buf, $remote_ip , $remote_port);//вызов функции записи в лог полученного сообщения

		$remote_buf = udp_parsing ($buf, $remote_ip);

		echo "Send back -  " . $remote_buf .PHP_EOL;

		//Send back the data to the client
		socket_sendto($sock, $remote_buf, 100 , 0 , $remote_ip , $remote_port);










	}

	socket_close($sock);

?>



