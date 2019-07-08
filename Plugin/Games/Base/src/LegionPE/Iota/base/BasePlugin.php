<?php

namespace LegionPE\Iota\base;

use LegionPE\Iota\base\command\BaseCommand;
use LegionPE\Iota\base\error\CodeError;
use LegionPE\Iota\base\party\Party;
use LegionPE\Iota\base\query\chatroom\CreateChatRoomQuery;
use LegionPE\Iota\base\query\chatroom\CreateChatRoomsQuery;
use LegionPE\Iota\base\query\chatroom\GetChatRoomsByKeysQuery;
use LegionPE\Iota\base\query\party\CreatePartyPlayerQuery;
use LegionPE\Iota\base\query\server\CreateServerQuery;
use LegionPE\Iota\base\query\server\GetServerIdQuery;
use LegionPE\Iota\base\query\server\UpdateServerStatusQuery;
use LegionPE\Iota\base\query\session\GetUserDataQuery;
use LegionPE\Iota\base\query\team\CreateTeamQuery;
use LegionPE\Iota\base\task\NetworkSyncTask;
use LegionPE\Iota\base\team\Team;
use LegionPE\Iota\base\util\TCPNetworkSyncThread;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

abstract class BasePlugin extends PluginBase{
	private static $NAME = null;
	/** @var int */
	private $id;
	/** @var string[] */
	protected $worldNames = [];
	/** @var BaseSession[] */
	private $authenticatedSessions = [];
	/** @var BaseSession[] */
	private $authenticatedSessionsByUid = [];
	/** @var BaseSession[] */
	private $unauthenticatedSessions = [];
	/** @var BaseSession[] */
	private $unauthenticatedSessionsByUid = [];
	/** @var BaseSession[] */
	private $registeringSessions = [];
	/** @var ChatRoom[] */
	private $chatRooms = [];
	/** @var Team[] */
	private $teams = [];
	/** @var ResourceManager */
	private $resourceManager;
	/** @var EventListener */
	private $listener;
	/** @var \mysqli */
	private $mysqli;
	/** @var TCPNetworkSyncThread */
	private $networkSyncThread;
	/** @var Party[] */
	private $parties = [];
	private $objectStore = [];
	private $nextStoreId = 1;
	/**
	 * @param Server $server
	 * @return null|BasePlugin
	 */
	public static function getInstance(Server $server){
		$plugin = $server->getPluginManager()->getPlugin(self::$NAME);
		if($plugin instanceof BasePlugin){
			return $plugin;
		}
		return null;
	}
	/**
	 * Called when the plugin is loaded
	 */
	public function onLoad(){
		self::$NAME = $this->getName();
	}
	/**
	 * Called when the plugin is enabled
	 */
	public function onEnable(){
		$this->mysqli = Utils::getMySQLiConnection();
		new GetServerIdQuery($this, function($result, $rows, $error){
			if($rows){
				$this->id = $result[0]['id'];
				new UpdateServerStatusQuery($this, function($result, $rows, $error){}, $this->id, 1);
				$this->initChatRooms();
			}else{
				new CreateServerQuery($this, function($result, $rows, $error){
					$this->id = $result;
					$this->initChatRooms();
				}, $this->getServer()->getIp(), $this->getServer()->getPort(), 1);
			}
		}, $this->getServer()->getIp(), $this->getServer()->getPort());
		$this->networkSyncThread = new TCPNetworkSyncThread(Constants::TCP_SERVER_IP, Constants::TCP_SERVER_PORT, Constants::TCP_AUTH_TOKEN);
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new NetworkSyncTask($this), 1, 1);
		$this->listener = $this->getEventListener($this);
		$this->resourceManager = new ResourceManager($this);
		if(count($this->worldNames) === 0){
			if(($level = $this->getServer()->getLevelByName('world')) instanceof Level){
				$this->getServer()->unloadLevel($level, true);
			}
		}else{
			foreach($this->worldNames as $worldName){
				$this->getServer()->loadLevel($worldName);
				$this->getLogger()->info($this->resourceManager->getMessage('en', 'plugin.levelLoaded', ['%name%'], [$worldName]));
			}
		}
		if(($level = $this->getServer()->getLevelByName('nether')) instanceof Level){
			$this->getServer()->unloadLevel($level, true);
		}
		BaseCommand::registerAll($this, $this->getServer()->getCommandMap());
	}
	private function initChatRooms(){
		$keys = [];
		foreach(Constants::$languageCodes as $code){
			$keys[] = $this->getChatRoomPrefix() . 'LANG-' . strtoupper($code);
		}
		new GetChatRoomsByKeysQuery($this, function($result, $rows, $error)use($keys){
			$remainingRoomKeys = array_flip($keys);
			foreach($result as $chatRoomData){
				unset($remainingRoomKeys[$chatRoomData['key']]);
				$this->addChatRoom(new ChatRoom($this, $chatRoomData['key'], (bool) $chatRoomData['local']));
			}
			$queryValues = [];
			foreach($remainingRoomKeys as $key => $index){
				$this->addChatRoom(new ChatRoom($this, $key, true));
				$queryValues[] = ['key' => $key, 'local' => 1];
			}
			if(count($queryValues)) new CreateChatRoomsQuery($this, function($result, $rows, $error){}, $queryValues);
		}, $keys);
	}
	/**
	 * Called when the plugin gets disabled
	 */
	public function onDisable(){
		new UpdateServerStatusQuery($this, function($result, $rows, $error){}, $this->getId(), 0);
	}
	/**
	 * @return int
	 */
	public function getId(): int{
		return $this->id;
	}
	/**
	 * @return TCPNetworkSyncThread
	 */
	public function getNetworkSyncThread(): TCPNetworkSyncThread{
		return $this->networkSyncThread;
	}
	/**
	 * @param $object
	 * @return int
	 */
	public function storeObject($object): int{
		$id = $this->nextStoreId++;
		$this->objectStore[$id] = $object;
		return $id;
	}
	/**
	 * @param int $id
	 */
	public function fetchObject(int $id){
		$object = $this->objectStore[$id];
		unset($this->objectStore[$id]);
		return $object;
	}
	/**
	 * @return \mysqli
	 */
	public function getMySQLi(): \mysqli{
		return $this->mysqli;
	}
	/**
	 * @return ResourceManager
	 */
	public function getResourceManager(): ResourceManager{
		return $this->resourceManager;
	}
	/**
	 * @param BaseSession $session
	 */
	public function addSession(BaseSession $session){
		if($session->isRegistered()){
			if($session->isAuthenticated()){
				$this->authenticatedSessionsByUid[$session->getUid()] = $this->authenticatedSessions[$session->getPlayer()->getClientId()] = $session;
			}else{
				$this->unauthenticatedSessionsByUid[$session->getUid()] = $this->unauthenticatedSessions[$session->getPlayer()->getClientId()] = $session;
			}
		}else{
			$this->registeringSessions[$session->getPlayer()->getClientId()] = $session;
		}
	}
	/**
	 * @return BaseSession[]
	 */
	public function getAuthenticatedSessions(): array{
		return $this->authenticatedSessions;
	}
	/**
	 * @return BaseSession[]
	 */
	public function getAuthenticatedSessionsByUid(): array{
		return $this->authenticatedSessionsByUid;
	}
	/**
	 * @param Player $player
	 * @return BaseSession|null
	 */
	public function getAuthenticatedSession(Player $player){
		return $this->getAuthenticatedSessionByPlayerClientId($player->getClientId());
	}
	/**
	 * @param int $uid
	 * @return BaseSession|null
	 */
	public function getAuthenticatedSessionByUid(int $uid){
		return ($this->authenticatedSessionsByUid[$uid] ?? null);
	}
	/**
	 * @param $clientId
	 * @return BaseSession|null
	 */
	public function getAuthenticatedSessionByPlayerClientId($clientId){
		return ($this->authenticatedSessions[$clientId] ?? null);
	}
	/**
	 * @return BaseSession[]
	 */
	public function getUnauthenticatedSessions(): array{
		return $this->unauthenticatedSessions;
	}
	/**
	 * @return BaseSession[]
	 */
	public function getUnauthenticatedSessionsByUid(): array{
		return $this->unauthenticatedSessionsByUid;
	}
	/**
	 * @param Player $player
	 * @return BaseSession|null
	 */
	public function getUnauthenticatedSession(Player $player){
		return $this->getUnauthenticatedSessionByPlayerClientId($player->getClientId());
	}
	/**
	 * @param int $uid
	 * @return BaseSession|null
	 */
	public function getUnauthenticatedSessionByUid(int $uid){
		return ($this->unauthenticatedSessionsByUid[$uid] ?? null);
	}
	/**
	 * @param $clientId
	 * @return BaseSession|null
	 */
	public function getUnauthenticatedSessionByPlayerClientId($clientId){
		return ($this->unauthenticatedSessions[$clientId] ?? null);
	}
	/**
	 * @return BaseSession[]
	 */
	public function getRegisteringSessions(): array{
		return $this->registeringSessions;
	}
	/**
	 * @param Player $player
	 * @return BaseSession|null
	 */
	public function getRegisteringSession(Player $player){
		return ($this->registeringSessions[$player->getClientId()] ?? null);
	}
	/**
	 * @param BaseSession $session
	 */
	public function removeSession(BaseSession $session){
		if(isset($this->authenticatedSessions[$session->getPlayer()->getClientId()])){
			unset($this->authenticatedSessions[$session->getPlayer()->getClientId()]);
			unset($this->authenticatedSessionsByUid[$session->getUid()]);
		}elseif(isset($this->unauthenticatedSessions[$session->getPlayer()->getClientId()])){
			unset($this->unauthenticatedSessions[$session->getPlayer()->getClientId()]);
			unset($this->unauthenticatedSessionsByUid[$session->getUid()]);
		}elseif($this->registeringSessions[$session->getPlayer()->getClientId()]){
			unset($this->registeringSessions[$session->getPlayer()->getClientId()]);
		}
	}
	/**
	 * @param BaseSession $session
	 */
	public function addAuthenticatedSession(BaseSession $session){
		if(isset($this->unauthenticatedSessions[$session->getPlayer()->getClientId()])){
			unset($this->unauthenticatedSessions[$session->getPlayer()->getClientId()]);
			unset($this->unauthenticatedSessionsByUid[$session->getUid()]);
		}elseif($this->registeringSessions[$session->getPlayer()->getClientId()]){
			unset($this->registeringSessions[$session->getPlayer()->getClientId()]);
		}
		$this->authenticatedSessions[$session->getPlayer()->getClientId()] = $session;
		$this->authenticatedSessionsByUid[$session->getUid()] = $session;
	}
	/**
	 * @param Player $player
	 * @return BaseSession|null
	 */
	public function findSession(Player $player){
		if(($session = $this->getAuthenticatedSession($player)) instanceof BaseSession){
			return $session;
		}elseif(($session = $this->getUnauthenticatedSession($player)) instanceof BaseSession){
			return $session;
		}elseif(($session = $this->getRegisteringSession($player)) instanceof BaseSession){
			return $session;
		}
		return null;
	}
	/**
	 * @param int $uid
	 * @return BaseSession|null
	 */
	public function findSessionByUid(int $uid){
		if(($session = $this->getAuthenticatedSessionByUid($uid)) instanceof BaseSession){
			return $session;
		}elseif(($session = $this->getUnauthenticatedSessionByUid($uid)) instanceof BaseSession){
			return $session;
		}
	}
	/**
	 * @param int $id
	 * @return Team|null
	 */
	public function getTeam(int $id){
		return ($this->teams[$id] ?? null);
	}
	/**
	 * @param Team $team
	 */
	public function addTeam(Team $team){
		$this->teams[$team->getId()] = $team;
	}
	/**
	 * @param Team $team
	 */
	public function removeTeam(Team $team){
		$this->removeTeamById($team->getId());
	}
	/**
	 * @param int $id
	 */
	public function removeTeamById(int $id){
		unset($this->teams[$id]);
	}
	/**
	 * @param Party $party
	 */
	public function addParty(Party $party){
		$this->parties[$party->getLeaderUid()] = $party;
	}
	/**
	 * @param int $leaderUid
	 * @return Party|null
	 */
	public function getParty(int $leaderUid){
		return ($this->parties[$leaderUid] ?? null);
	}
	/**
	 * @param int $leaderUid
	 */
	public function removePartyByLeaderUid(int $leaderUid){
		unset($this->parties[$leaderUid]);
	}
	/**
	 * @param Party $party
	 */
	public function removeParty(Party $party){
		$this->removePartyByLeaderUid($party->getLeaderUid());
	}
	/**
	 * @return ChatRoom[]
	 */
	public function getChatRooms(): array{
		return $this->chatRooms;
	}
	/**
	 * @param ChatRoom $chatRoom
	 */
	public function addChatRoom(ChatRoom $chatRoom){
		$this->chatRooms[$chatRoom->getKey()] = $chatRoom;
	}
	/**
	 * @param string $languageCode
	 * @return ChatRoom
	 */
	public function getChatRoomByLanguageCode(string $languageCode): ChatRoom{
		return $this->getChatRoom($this->getChatRoomPrefix() . 'LANG-' . strtoupper($languageCode));
	}
	/**
	 * @param string $key
	 * @return ChatRoom
	 */
	public function getChatRoom(string $key): ChatRoom{
		return ($this->chatRooms[$key] ?? null);
	}
	/**
	 * @param ChatRoom $chatRoom
	 */
	public function removeChatRoom(ChatRoom $chatRoom): ChatRoom{
		$this->removeChatRoomByKey($chatRoom->getKey());
	}
	/**
	 * @param string $key
	 */
	public function removeChatRoomByKey(string $key): string{
		unset($this->chatRooms[$key]);
	}
	/**
	 * @return string
	 */
	public function getChatRoomPrefix(): string{
		return "SERVER-" . $this->getId() . "_";
	}
	/**
	 * @param callable $callback
	 * @param BaseSession $leader
	 * @param string $name
	 * @param string $acronym
	 * @return PresetMessageError[]
	 */
	public function createTeam(callable $callback, BaseSession $leader, string $name, string $acronym): array{
		$presetMessageErrors = [];
		if($leader->getTeam() instanceof Team){
			$presetMessageErrors[] = new PresetMessageError('commands.team.generalMessages.inTeam', Constants::ERROR_CODE_TEAM_CREATE_CREATOR_IN_TEAM);
		}
		if(strlen($name) < 4){
			$presetMessageErrors[] = new PresetMessageError('commands.team.arguments.create.messages.nameTooShort', Constants::ERROR_CODE_TEAM_CREATE_NAME_TOO_SHORT);
		}
		if(strlen($name) > 32){
			$presetMessageErrors[] = new PresetMessageError('commands.team.arguments.create.messages.nameTooLong', Constants::ERROR_CODE_TEAM_CREATE_NAME_TOO_LONG);
		}
		if(!preg_match(Constants::REGEX_NUMBERS_LETTERS_SYMBOLS, $name)){
			$presetMessageErrors[] = new PresetMessageError('commands.team.arguments.create.messages.nameOnlyLettersNumbersAndSymbols', Constants::ERROR_CODE_TEAM_CREATE_NAME_DISALLOWED_CHARACTERS);
		}
		if(strlen($acronym) === 4){
			$presetMessageErrors[] = new PresetMessageError('commands.team.arguments.create.messages.acronymNotRightLength', Constants::ERROR_CODE_TEAM_CREATE_ACRONYM_WRONG_LENGTH);
		}
		if(!preg_match(Constants::REGEX_NUMBERS_LETTERS, $acronym)){
			$presetMessageErrors[] = new PresetMessageError('commands.team.arguments.create.messages.acronymOnlyLettersAndNumbers', Constants::ERROR_CODE_TEAM_CREATE_ACRONYM_NO_SYMBOLS);
		}
		if(!count($presetMessageErrors)){
			new CreateTeamQuery($this, $callback, $name, $acronym, $leader->getUid(), Constants::TEAM_RANK_OWNER);
		}
		return $presetMessageErrors;
	}
	/**
	 * @param callable $callback
	 * @param BaseSession $leader
	 * @return PresetMessageError[]
	 */
	public function createParty(callable $callback, BaseSession $leader): array{
		$presetMessageErrors = [];
		if($leader->getParty() instanceof Party){
			$presetMessageErrors[] = new PresetMessageError('commands.party.generalMessages.alreadyInParty', Constants::ERROR_CODE_PARTY_CREATE_IN_PARTY);
		}
		if(!count($presetMessageErrors)){
			new CreatePartyPlayerQuery($this, $callback, $leader->getUid(), $leader->getUid(), Constants::PARTY_STATUS_ACCEPTED);
		}
		return $presetMessageErrors;
	}
	/**
	 * @param callable $callback
	 * @param string $key
	 * @param bool $local
	 * @return CodeError[]
	 */
	public function createChatRoom(callable $callback, string $key, bool $local = false): array{
		$codeErrors = [];
		if(strlen($key) > Constants::CHAT_ROOM_KEY_MAX_LENGTH){
			$codeErrors[] = new CodeError(Constants::ERROR_CODE_CHAT_ROOM_CREATE_KEY_TOO_LONG);
		}
		if(!count($codeErrors)){
			new CreateChatRoomQuery($this, $callback, $key, (int) $local);
		}
		return $codeErrors;
	}
	/**
	 * @param BasePlugin $plugin
	 * @param Player $player
	 * @return BaseSession
	 */
	abstract public function createSession(BasePlugin $plugin, Player $player): BaseSession;
	/**
	 * @param BasePlugin $plugin
	 * @return EventListener
	 */
	protected function getEventListener(BasePlugin $plugin): EventListener{
		return new EventListener($plugin);
	}
}
