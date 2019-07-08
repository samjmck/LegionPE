<?php

namespace LegionPE\Iota\base\util;

use pocketmine\Thread;

class TCPNetworkSyncThread extends Thread{
	const TYPE_SERVER = 0;
	/** @var bool */
	private $shutdown = false;
	/** @var string */
	private $ip;
	/** @var int */
	private $port;

	private $socket;
	/** @var string */
	private $authToken;
	private $sendBuffer, $recvBuffer;
	public function __construct(string $ip, int $port, string $authToken){
		$this->ip = $ip;
		$this->port = $port;
		$this->authToken = $authToken;

		$this->sendBuffer = new \Volatile;
		$this->recvBuffer = new \Volatile;

		$this->socket = stream_socket_client("tcp://{$this->ip}:{$this->port}", $errno, $errstr, 30);
		if(!$this->socket){
			throw new \RuntimeException('Could not create stream socket: ' . $errno . '(' . $errstr . ')');
		}
		if(!stream_set_blocking($this->socket, false)){
			throw new \RuntimeException('Could not set stream socket to non-blocking mode');
		}
		$this->writeArray(['type' => self::TYPE_SERVER, 'authKey' => $this->authToken]);
		$this->start();
	}
	public function shutdown(){
		$this->shutdown = true;
		fclose($this->socket);
	}
	public function quit(){
		$this->shutdown();
	}
	public function unstack(){

	}
	public function run(){
		while(!$this->shutdown){
			while(!@feof($this->socket)){
				$data = @fgets($this->socket, 2048);
				if(strlen($data) > 0){
					$this->recvBuffer[] = $data;
				}
			}
		}
	}
	public function getNextReceivedMessage(){
		return $this->recvBuffer->count() !== 0 ? $this->recvBuffer->shift() : null;
	}
	public function getNextReceivedMessageDecoded(){
		return ($message = $this->getNextReceivedMessage()) !== null ? json_decode($message) : null;
	}
	/**
	 * @param string $string
	 */
	public function writeString(string $string){
		fwrite($this->socket, $string . "\n");
	}
	/**
	 * @param array $array
	 */
	public function writeArray(array $array){
		$this->writeString(json_encode($array));
	}
	/**
	 * @param array $data
	 */
	public function pushArray(array $data){
		$this->pushString(json_encode($data));
	}
	/**
	 * @param string $data
	 */
	public function pushString(string $data){
		$this->sendBuffer[] = $data;
	}
	/**
	 * @param string $eventName
	 * @param array $extraData
	 */
	public function callEvent(string $eventName, array $extraData = []){
		$this->writeArray(array_merge(['event' => $eventName], $extraData));
	}
}
