<?php

namespace LegionPE\Iota\base;

class ChatRoom{
	/** @var BasePlugin */
	private $plugin;
	/** @var string */
	private $key;
	/** @var BaseSession[] */
	private $sessions = [];
	/** @var bool */
	private $local = false;
	/**
	 * @param BasePlugin $plugin
	 * @param string $key
	 * @param bool $local
	 */
	public function __construct(BasePlugin $plugin, string $key, bool $local = false){
		$this->plugin = $plugin;
		$this->key = $key;
		$this->local = $local;
	}
	/**
	 * @return BasePlugin
	 */
	public function getPlugin(): BasePlugin{
		return $this->plugin;
	}
	/**
	 * @return string
	 */
	public function getKey(): string{
		return $this->key;
	}
	/**
	 * @return bool
	 */
	public function isLocal(): bool{
		return $this->local;
	}
	/**
	 * @return BaseSession[]
	 */
	public function getSessions(): array{
		return $this->sessions;
	}
	/**
	 * @param BaseSession $session
	 */
	public function addSession(BaseSession $session){
		$this->sessions[$session->getUid()] = $session;
	}
	/**
	 * @param BaseSession $session
	 */
	public function removeSession(BaseSession $session){
		$this->removeSessionByUid($session->getUid());
	}
	/**
	 * @param int $uid
	 */
	public function removeSessionByUid(int $uid){
		unset($this->sessions[$uid]);
	}
	/**
	 * @param string $message
	 */
	public function broadcastLocally(string $message){
		self::routerBroadcast($this, $message);
	}
	/**
	 * @param string $message
	 */
	public function broadcast(string $message){
		self::routerBroadcast($this, $message);
		if(!$this->isLocal()) $this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_CHAT_ROOM_BROADCAST, ['key' => $this->getKey(), 'message' => $message]);
	}
	/**
	 * @param ChatRoom $chatRoom
	 * @param string $message
	 */
	public static function routerBroadcast(ChatRoom $chatRoom, string $message){
		foreach($chatRoom->getSessions() as $session){
			$session->sendMessageIfLoggedIn($message);
		}
	}
	/**
	 * @param string $key
	 * @param array $search
	 * @param array $replace
	 */
	public function broadcastPresetMessageLocally(string $key, array $search = [], array $replace = []){
		self::routerBroadcastPresetMessage($this, $key, $search, $replace);
	}
	/**
	 * @param string $key
	 * @param array $search
	 * @param array $replace
	 */
	public function broadcastPresetMessage(string $key, array $search = [], array $replace = []){
		self::routerBroadcastPresetMessage($this, $key, $search, $replace);
		if(!$this->isLocal()) $this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_CHAT_ROOM_BROADCAST, ['key' => $this->getKey(), 'messageIdentifier' => $key, 'search' => $search, 'replace' => $replace]);
	}
	/**
	 * @param ChatRoom $chatRoom
	 * @param string $key
	 * @param array $search
	 * @param array $replace
	 */
	public static function routerBroadcastPresetMessage(ChatRoom $chatRoom, string $key, array $search = [], array $replace = []){
		foreach($chatRoom->getSessions() as $session){
			$session->sendPresetMessageIfLoggedIn($session->getPresetMessage($key, $search, $replace));
		}
	}
}
