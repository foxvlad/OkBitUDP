<?php

/*
	Class udp_okbit 	
   - Отправка UDP пакета в сокет
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
	
	public function __construct ($ip_gate='192.168.0.127', $port_gate=6400, $ip_udp_send='0.0.0.0', $port_udp_send=6600, $debug=true){
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
		
		log_write($this->data); //вызов функции записи в лог полученного сообщения
			
		//Получение ответа о шлюза
		if( (socket_recvfrom($this->sock , $this->redate , 250 , 0, $remote_ip, $remote_port )) === false){
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
				 
			die("Could not receive data: [$errorcode] $errormsg".PHP_EOL);
		}
								
		if ($this->debug){
			echo "redate : $this->redate".PHP_EOL;
		}
		
		socket_shutdown($this->sock, 2);
		socket_close($this->sock);		
	}
	
}


?>