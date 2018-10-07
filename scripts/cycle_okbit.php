<?php

/**
* OkBit (UDP-пакеты) 
* @package project
* @author Wizard <foxvlad@yandex.ru>
* @copyright http://okbit.ru (c)
* @version 0.1 (wizard, [Feb 04, 2018])
*/


	error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_STRICT);
	ini_set('display_errors', 0);

	chdir(dirname(__FILE__) . '/../');

	include_once("./config.php");
	include_once("./lib/loader.php");
	include_once("./lib/threads.php");

	set_time_limit(0);

	// connecting to database
	$db = new mysql(DB_HOST, '', DB_USER, DB_PASSWORD, DB_NAME);

	include_once("./load_settings.php");
	include_once(DIR_MODULES . "control_modules/control_modules.class.php");

	$ctl = new control_modules();
	
	include_once(DIR_MODULES . 'okbit/okbit.class.php');
	
	$okbit_module = new okbit();
	$okbit_module->getConfig();
	
	$bind_ip = '0.0.0.0';
	$bind_port = '6500';
	
	$latest_check=0;
	$checkEvery=5; // poll every 5 seconds



	if ($okbit_module->config['API_IP']) $bind_ip = $okbit_module->config['API_IP'];
	if ($okbit_module->config['API_LOG_DEBMES']) $debmes_debug = true;
	if ($okbit_module->config['API_LOG_CYCLE']) $cycle_debug = true;

	echo date("H:i:s") . " running " . basename(__FILE__) .  PHP_EOL;
	echo date('H:i:s') . " Bind IP - ".$bind_ip . PHP_EOL;
	echo date('H:i:s') . ' Cycle debug - ' . ($cycle_debug ? 'yes' : 'no') . PHP_EOL;
	echo date('H:i:s') . ' DebMes debug - ' . ($debmes_debug ? 'yes' : 'no') . PHP_EOL;



 


	$sock = udp_socket($bind_ip, $bind_port);  //вызов функциии создания UDP-сокета
	
	
	
	function udp_socket($bind_ip, $bind_port){ //создание сокета для прослушивания UDP-порта
		 
		 //Reduce errors
		error_reporting(~E_WARNING);
			 
		//Create a UDP socket
		if(!($sock = socket_create(AF_INET, SOCK_DGRAM, 0))){
				
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
				 
			die("Couldn't create socket: [$errorcode] $errormsg".PHP_EOL);
			
		}
			 
		if ($cycle_debug) echo "Socket created".PHP_EOL;
			 
			 
		// привязка исходного адреса
		if( !socket_bind($sock, $bind_ip , $bind_port) ){
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
				 
			die("Could not bind socket : [$errorcode] $errormsg".PHP_EOL);
		}
			
		if ($cycle_debug) echo "Socket bind OK".PHP_EOL;

		socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec" => 0));

		return ($sock);
	 }


	while (1){
	   
	   if ((time()-$latest_check)>$checkEvery) {
		$latest_check=time();
		setGlobal((str_replace('.php', '', basename(__FILE__))) . 'Run', time(), 1);

	   }
	   
		  
		//echo "Waiting for data ...".PHP_EOL;
		
		//Receive some data
		@$r = socket_recvfrom($sock, $buf, 512, 0, $remote_ip, $remote_port);
		
		if ($buf != '') {
			
			$e_test = $okbit_module->udp_parsing($buf, $remote_ip);//передача полученного буфера данных для дальнейшего парсинга в основной модуль
			
			if ($cycle_debug) {			
			echo date("H:i:s") . " - $remote_ip : $remote_port " . "| Number of characters msg  - " . strlen($buf) .", " . $e_test  .PHP_EOL;
			}
			
							 
			//Send back the data to the client
			socket_sendto($sock, "000B", 100 , 0 , $remote_ip , $remote_port);
			$buf = "";
		}
		
	   
	   if (file_exists('./reboot') || IsSet($_GET['onetime']))
	   {
		  $db->Disconnect();
		  echo date('H:i:s') . ' Stopping by command REBOOT or ONETIME' . basename(__FILE__) . PHP_EOL;
		  exit;
	   }
	   

   
   
   sleep(1);
}
DebMes("Unexpected close of cycle: " . basename(__FILE__));
