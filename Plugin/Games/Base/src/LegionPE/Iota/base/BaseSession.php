<?php

namespace LegionPE\Iota\base;

use LegionPE\Iota\base\event\session\SessionLoginEvent;
use LegionPE\Iota\base\event\session\SessionLogoutEvent;
use LegionPE\Iota\base\event\session\SessionRegisterEvent;
use LegionPE\Iota\base\party\Party;
use LegionPE\Iota\base\party\PartyPlayer;
use LegionPE\Iota\base\query\party\GetPartyPlayersByLeaderIdQuery;
use LegionPE\Iota\base\query\session\CreateAdministrationLogQuery;
use LegionPE\Iota\base\query\session\CreateNewIpQuery;
use LegionPE\Iota\base\query\session\CreateUserQuery;
use LegionPE\Iota\base\query\session\GetUserAdminLogsQuery;
use LegionPE\Iota\base\query\session\GetUserAuthenticatedQuery;
use LegionPE\Iota\base\query\session\GetUserDataQuery;
use LegionPE\Iota\base\query\session\GetUserFriendsQuery;
use LegionPE\Iota\base\query\session\GetUserPartyInvitesQuery;
use LegionPE\Iota\base\query\session\SaveUserDataQuery;
use LegionPE\Iota\base\query\session\GetUserTeamInvitesQuery;
use LegionPE\Iota\base\query\team\GetTeamByIdQuery;
use LegionPE\Iota\base\task\GenerateHashTask;
use LegionPE\Iota\base\task\ValidatePasswordTask;
use LegionPE\Iota\base\team\TeamPlayer;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBedLeaveEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use LegionPE\Iota\base\team\Team;
use pocketmine\Player;
use LegionPE\Iota\base\query\Query;

abstract class BaseSession{
	const LOGIN_STAGE_LOGGING_IN    = 0;
	const LOGIN_STAGE_LOGGED_IN     = 1;
	/** @var int */
	protected $loginStage = self::LOGIN_STAGE_LOGGING_IN;

	const TEAM_STAGE_DEFAULT    = 0;
	const TEAM_STAGE_JOINING    = 1;
	/** @var int */
	public $teamStage = self::TEAM_STAGE_DEFAULT;

	/** @var int */
	private $mode = Constants::PLAYER_MODE_PLAYING;
	/** @var Player */
	private $player;
	/** @var BasePlugin */
	private $plugin;
	/** @var int */
	private $loggedInTime;
	/** @var int */
	private $uid = -1;
	/** @var string */
	private $lastIp;
	/** @var TeamPlayer[] */
	private $teamPlayersInvites = [];
	/** @var TeamPlayer[] */
	private $teamPlayersInvitesByTeamName = [];
	/** @var TeamPlayer */
	private $teamPlayer = null;
	/** @var int */
	private $online;
	/** @var string */
	private $uuid;
	/** @var FriendRelationship[] */
	private $friends = [];
	/** @var FriendRelationship[] */
	private $friendsByUsername = [];
	/** @var float */
	private $coins;
	/** @var string */
	private $hash;
	/** @var string */
	private $email;
	/** @var int */
	private $registrationTime;
	/** @var int */
	private $lastOn;
	/** @var int */
	private $onTime;
	/** @var int */
	private $rank;
	/** @var Team */
	private $team = null;
	/** @var int */
	private $teamId;
	/** @var int */
	private $muteUntil;
	/** @var int */
	private $banUntil;
	/** @var string */
	private $countryCode;
	/** @var int */
	private $chatLanguageCode = Constants::LANG_CODE_EN;
	/** @var int */
	private $serverLanguageCode = Constants::LANG_CODE_EN;
	/** @var int */
	private $registrationStep = 0;
	/** @var string */
	private $lastChatMessage = '';
	/** @var int */
	private $chatRoomId = 0;
	/** @var int */
	private $loginAttempts = 0;
	/** @var string[] */
	private $loginMessages = [];
	/** @var int */
	private $partyLeaderUid;
	/** @var PartyPlayer[] */
	private $partyPlayerInvitesbyLeaderName = [];
	/** @var PartyPlayer */
	private $partyPlayer = null;
	/** @var Party */
	private $party = null;
	/** @var array */
	private $adminLogs = [];
	/** @var int */
	private $hasPartyInvites;
	/** @var int */
	private $hasFriends;
	/** @var int */
	private $hasTeamInvites;
	/** @var int */
	private $hasIgnored;
	/** @var int */
	private $hasAdminLogs;
	/** @var ChatRoom[] */
	private $chatRoomsByKey = [];
	/** @var ChatRoom */
	private $currentChatRoom;
	/**
	 * @param BasePlugin $plugin
	 * @param Player $player
	 */
	public function __construct(BasePlugin $plugin, Player $player){
		$this->plugin = $plugin;
		$this->player = $player;
		$plugin->addSession($this);
		$this->getSessionDataQuery();
	}
	/**
	 * @return int
	 */
	public function getUid(): int{
		return $this->uid;
	}

	/**
	 * @param int $uid
	 */
	public function setUid(int $uid){
		$this->uid = $uid;
	}
	/**
	 * @param int $id
	 * @return AdminLog|null
	 */
	public function getAdminLog(int $id){
		return $this->adminLogs[$id] ?? null;
	}
	/**
	 * @param AdminLog $adminLog
	 */
	public function addAdminLog(AdminLog $adminLog){
		$this->adminLogs[$adminLog->getId()] = $adminLog;
		$this->setHasAdminLogs(true);
	}
	/**
	 * @param AdminLog $adminLog
	 */
	public function removeAdminLog(AdminLog $adminLog){
		$this->removeAdminLogById($adminLog->getId());
	}
	/**
	 * @param int $id
	 */
	public function removeAdminLogById(int $id){
		if(($adminLog = $this->getAdminLog($id)) instanceof AdminLog){
			if($adminLog->getType() === Constants::ADMIN_LOG_TYPE_MUTE){
				if($this->getMuteUntil() === $adminLog->getCreationTime() + $adminLog->getDuration()){
					$this->setMuteUntil(-1);
				}
			}
			unset($this->adminLogs[$id]);
		}
	}
	/**
	 * @return AdminLog[]
	 */
	public function getAdminLogs(): array{
		return $this->adminLogs;
	}
	/**
	 * @return string
	 */
	public function getLastIp(): string{
		return $this->lastIp;
	}
	/**
	 * @param string $ip
	 */
	private function setLastIp(string $ip){
		$this->lastIp = $ip;
	}
	/**
	 * @return string|null
	 */
	public function getEmail(): ?string{
		return $this->email;
	}
	/**
	 * @param string $email
	 */
	public function setEmail(string $email){
		$this->email = $emai;
	}
	/**
	 * @return bool
	 */
	public function isOnline(): bool{
		return (bool) $this->online;
	}
	/**
	 * @param int|bool $online
	 */
	private function setOnline($online){
		$this->online = (int) $online;
	}
	/**
	 * @return string
	 */
	public function getUUID(): string{
		return $this->uuid;
	}
	/**
	 * @param string $uuid
	 */
	private function setUUID(string $uuid){
		$this->uuid = $uuid;
	}
	/**
	 * @return float
	 */
	public function getCoins(): float{
		return $this->coins;
	}
	/**
	 * @param float $coins
	 */
	public function addCoins($coins): float{
		$this->coins += $coins;
	}
	/**
	 * @param float $coins
	 */
	public function removeCoins($coins): float{
		$this->coins -= $coins;
	}
	/**
	 * @param ChatRoom $chatRoom
	 */
	public function addChatRoom(ChatRoom $chatRoom){
		$this->chatRoomsByKey[$chatRoom->getKey()] = $chatRoom;
	}
	/**
	 * @param string $key
	 * @return ChatRoom
	 */
	public function getChatRoom(string $key): ChatRoom{
		return $this->chatRoomsByKey[$key];
	}
	/**
	 * @param ChatRoom $chatRoom
	 */
	public function removeChatRoom(ChatRoom $chatRoom){
		$this->removeChatRoomByKey($chatRoom->getKey());
	}
	/**
	 * @param string $key
	 */
	public function removeChatRoomByKey(string $key){
		unset($this->chatRoomsByKey[$key]);
	}
	/**
	 * @return ChatRoom
	 */
	public function getCurrentChatRoom(): ChatRoom{
		return $this->currentChatRoom;
	}
	/**
	 * @param ChatRoom $chatRoom
	 */
	public function setCurrentChatRoom(ChatRoom $chatRoom){
		$this->currentChatRoom = $chatRoom;
	}
	public function resetCurrentChatRoom(){
		$this->setCurrentChatRoom($this->getPlugin()->getChatRoom($this->getPlugin()->getChatRoomPrefix() . 'LANG-' . strtoupper(Utils::languageConstantToString($this->chatLanguageCode))));
	}
	/**
	 * @return string|null
	 */
	public function getHash(): ?string{
		return $this->hash;
	}
	/**
	 * @param string $hash
	 */
	protected function setHash(string $hash){
		$this->hash = $hash;
	}
	/**
	 * @return int
	 */
	public function getRegistrationTime(): int{
		return $this->registrationTime;
	}
	/**
	 * @return int
	 */
	public function getLastOn(): int{
		return time();
	}
	/**
	 * @return int
	 */
	public function getOnTime(): int{
		return $this->onTime + time() - $this->loggedInTime;
	}
	/**
	 * @return int
	 */
	public function getRank(): int{
		return $this->rank;
	}
	/**
	 * @param int $rank
	 */
	public function setRank(int $rank){
		$this->rank = $rank;
	}
	/**
	 * @return Team|null
	 */
	public function getTeam(){
		return $this->team;
	}
	/**
	 * @param Team|null $team
	 */
	public function setTeam(?Team $team){
		$this->team = $team;
		if(!($team instanceof Team)){
			$this->setTeamId(-1);
		}else{
			$this->setTeamId($team->getId());
		}
	}
	/**
	 * @return int|null
	 */
	public function getTeamId(): ?int{
		return $this->teamId;
	}
	/**
	 * @param int|null $id
	 */
	private function setTeamId(?int $id){
		$this->teamId = $id;
	}
	/**
	 * @return string
	 */
	public function getName(): string{
		return $this->getPlayer()->getName();
	}
	/**
	 * @param int $languageCode
	 */
	public function setChatLanguageCode(int $languageCode){
		$this->chatLanguageCode = $languageCode;
	}
	/**
	 * @return int
	 */
	public function getChatLanguageCode(): int{
		return $this->chatLanguageCode;
	}
	/**
	 * @return string
	 */
	public function getChatLanguageCodeString(): string{
		return Utils::languageConstantToString($this->getChatLanguageCode());
	}
	/**
	 * @param int $languageCode
	 */
	public function setServerLanguageCode(int $languageCode){
		$this->serverLanguageCode = $languageCode;
	}
	/**
	 * @return int
	 */
	public function getServerLanguageCode(): int{
		return $this->serverLanguageCode;
	}
	/**
	 * @return string
	 */
	public function getServerLanguageCodeString(): string{
		return Utils::languageConstantToString($this->getServerLanguageCode());
	}
	/**
	 * @return string
	 */
	public function getCountryCode(): string{
		return $this->countryCode;
	}
	/**
	 * @param string $countryCode
	 */
	public function setCountryCode(string $countryCode){
		$this->countryCode = $countryCode;
	}
	/**
	 * @param BaseSession $warner
	 * @param int $points
	 * @param string $message
	 * @param bool $ipSensitive
	 * @param bool $uuidSensitive
	 */
	public function warn(BaseSession $warner, string $message, int $duration = 31556926, bool $ipSensitive = false, bool $uuidSensitive = false){
		self::routerWarn($this, $warner->getUid(), $warner->getPlayer()->getAddress(), $message, $duration, $ipSensitive, $uuidSensitive);
		$warner->sendPresetMessage('player.warnings.sent', ['%name%'], [$this->getPlayer()->getName()]);
	}
	/**
	 * @param BaseSession $warned
	 * @param int $warnerUid
	 * @param string $warnerIp
	 * @param string $message
	 * @param int $duration
	 * @param bool $ipSensitive
	 * @param bool $uuidSensitive
	 */
	public static function routerWarn(BaseSession $warned, int $warnerUid, string $warnerIp, string $message, int $duration, bool $ipSensitive, bool $uuidSensitive){
		new CreateAdministrationLogQuery($warned->getPlugin(), function($result, $rows, $error)use($warned, $message, $duration, $ipSensitive, $uuidSensitive, $warnerUid, $warnerIp){
			$warned->addAdminLog(new AdminLog($result[0], $warned->getUid(), $warned->getUUID(), $warned->getPlayer()->getAddress(), time(), Constants::ADMIN_LOG_TYPE_WARNING, $message, $warnerUid, $warnerIp, $duration, $ipSensitive, $uuidSensitive));
		}, $warned->getUid(), $warned->getUUID(), Constants::ADMIN_LOG_TYPE_WARNING, $message, $warnerUid, $duration, $ipSensitive, $uuidSensitive);
		$warned->getPlayer()->sendMessage($warned->getPlugin()->getResourceManager()->getMessage('en', 'player.warnings.receive', ['%message%'], [$message]));
	}
	/**
	 * @return int
	 */
	public function getMuteUntil(): int{
		return $this->muteUntil;
	}
	/**
	 * @param int $until
	 */
	public function setMuteUntil(int $until){
		$this->muteUntil = $until;
	}
	/**
	 * @param BaseSession $muter
	 * @param int $duration
	 * @param string $reason
	 * @param bool $ipSensitive
	 * @param bool $uuidSensitive
	 */
	public function mute(BaseSession $muter, string $reason, int $duration = 86400, bool $ipSensitive = false, bool $uuidSensitive = false){
		self::routerMute($this, $muter->getUid(), $muter->getPlayer()->getAddress(), $reason, $duration, $ipSensitive, $uuidSensitive);
		$muter->sendPresetMessage('player.warnings.muted', ['%name%', '%time%'], [$this->getPlayer()->getName(), Utils::secondsToTime($duration)]);
	}
	/**
	 * @param BaseSession $muter
	 * @param int $duration
	 * @param string $message
	 * @param bool $ipSensitive
	 * @param bool $uuidSensitive
	 */
	public static function routerMute(BaseSession $muted, int $muterUid, string $muterIp, string $reason, int $duration, bool $ipSensitive, bool $uuidSensitive){
		$message = $muted->getPresetMessage('player.warnings.mute', ['%reason%', '%duration%'], [$reason, Utils::secondsToTime($duration)]);
		new CreateAdministrationLogQuery($muted->getPlugin(), function($result, $rows, $error)use($muted, $message, $muterUid, $muterIp, $duration, $ipSensitive, $uuidSensitive){
			$muted->addAdminLog(new AdminLog($result[0], $muted->getUid(), $muted->getUUID(), $muted->getPlayer()->getAddress(), time(), Constants::ADMIN_LOG_TYPE_MUTE, $message, $muterUid, $muterIp, $duration, $ipSensitive, $uuidSensitive));
		}, $muted->getUid(), $muted->getUUID(), Constants::ADMIN_LOG_TYPE_MUTE, $message, $muterUid, $duration, $ipSensitive, $uuidSensitive);
		$muted->getPlayer()->sendMessage($message);
		$muted->setMuteUntil(time() + $duration);
	}
	/**
	 * @return int
	 */
	public function getBanUntil(): int{
		return $this->banUntil;
	}
	/**
	 * @param int $until
	 */
	public function setBanUntil(int $until){
		$this->banUntil = $until;
	}
	/**
	 * @return int
	 */
	public function getMode(): int{
		return $this->mode;
	}
	/**
	 * @param int $mode
	 */
	public function setMode(int $mode){
		$this->mode = $mode;
	}
	/**
	 * @param BaseSession $banner
	 * @param string $reason
	 * @param int $duration
	 * @param bool $ipSensitive
	 * @param bool $uuidSensitive
	 */
	public function ban(BaseSession $banner, string $reason, int $duration = 2592000, bool $ipSensitive = false, bool $uuidSensitive = false){
		self::routerBan($this, $banner->getUid(), $banner->getPlayer()->getAddress(), $reason, $duration, $ipSensitive, $uuidSensitive);
		$banner->sendPresetMessage('player.warnings.banned', ['%name%', '%time%'], [$this->getPlayer()->getName(), Utils::secondsToTime($duration)]);
	}
	/**
	 * @param BaseSession $banned
	 * @param int $bannerUid
	 * @param string $bannerIp
	 * @param string $reason
	 * @param int $duration
	 * @param bool $ipSensitive
	 * @param bool $uuidSensitive
	 */
	public static function routerBan(BaseSession $banned, int $bannerUid, string $bannerIp, string $reason, int $duration, bool $ipSensitive, bool $uuidSensitive){
		$message = $banned->getPresetMessage('player.warnings.messages.ban', ['%reason%', '%duration%'], [$reason, Utils::secondsToTime($duration)]);
		new CreateAdministrationLogQuery($banned->getPlugin(), function($result, $rows, $error)use($banned, $message, $bannerUid, $bannerIp, $duration, $ipSensitive, $uuidSensitive){
			$banned->addAdminLog(new AdminLog($banned->getUid(), $banned->getUUID(), $banned->getPlayer()->getAddress(), time(), Constants::ADMIN_LOG_TYPE_BAN, $message, $bannerUid, $bannerIp, $duration, $ipSensitive, $uuidSensitive));
		}, $banned->getUid(), $banned->getUUID(), Constants::ADMIN_LOG_TYPE_BAN, $message, $bannerUid, $duration, $ipSensitive, $uuidSensitive);
		$banned->setHasAdminLogs(true);
		$banned->getPlayer()->sendMessage($message);
		$banned->getPlayer()->kick($message);
	}
	/**
	 * @return BasePlugin
	 */
	public function getPlugin(): BasePlugin{
		return $this->plugin;
	}
	/**
	 * @return Player
	 */
	public function getPlayer(): Player{
		return $this->player;
	}
	/**
	 * @param FriendRelationship $friendRelationship
	 */
	public function addFriendRelationship(FriendRelationship $friendRelationship){
		$uid = ($friendRelationship->getRequestedUid() === $this->getUid() ? $friendRelationship->getRequesterUid() : $friendRelationship->getRequestedUid());
		$this->friends[$uid] = $friendRelationship;
		$this->friendsByUsername[($uid === $friendRelationship->getRequestedUid() ? $friendRelationship->getRequesterName() : $friendRelationship->getRequestedName())];
		$this->setHasFriends(true);
	}
	/**
	 * @param FriendRelationship $friendRelationship
	 */
	public function removeFriendRelationship(FriendRelationship $friendRelationship){
		$uid = ($friendRelationship->getRequestedUid() === $this->getUid() ? $friendRelationship->getRequesterUid() : $friendRelationship->getRequestedUid());
		unset($this->friends[$uid]);
		unset($this->friendsByUsername[($uid === $friendRelationship->getRequestedUid() ? $friendRelationship->getRequesterName() : $friendRelationship->getRequestedName())]);
		if(count($this->friends) === 0){
			$this->setHasFriends(false);
		}
	}
	/**
	 * @param int $uid
	 * @return FriendRelationship|null
	 */
	public function getFriendRelationshipByUid(int $uid): ?FriendRelationship{
		return ($this->friends[$uid] ?? null);
	}
	/**
	 * @param string $name
	 * @return FriendRelationship|null
	 */
	public function getFriendRelationshipByUsername(string $name): ?FriendRelationship{
		return ($this->friendsByUsername[$name] ?? null);
	}
	/**
	 * @return FriendRelationship[]
	 */
	public function getFriendRelationships(): array{
		return $this->friends;
	}
	/**
	 * @return FriendRelationship[]
	 */
	public function getAcceptedFriendRelationships(): array{
		$friends = [];
		foreach($this->friends as $friendRelationship){
			if($friendRelationship->getStatus() === Constants::FRIEND_STATUS_ACCEPTED){
				$friends[] = $friendRelationship;
			}
		}
		return $friends;
	}
	/**
	 * @return FriendRelationship[]
	 */
	public function getRequestedFriendRelationships(): array{
		$friends = [];
		foreach($this->friends as $friendRelationship){
			if($friendRelationship->getStatus() === Constants::FRIEND_STATUS_REQUESTED and $friendRelationship->getRequestedUid() === $this->getUid()){
				$friends[] = $friendRelationship;
			}
		}
		return $friends;
	}
	/**
	 * @return FriendRelationship[]
	 */
	public function getPendingFriendRelationships(): array{
		$friends = [];
		foreach($this->friends as $friendRelationship){
			if($friendRelationship->getStatus() === Constants::FRIEND_STATUS_REQUESTED and $friendRelationship->getRequesterUid() === $this->getUid()){
				$friends[] = $friendRelationship;
			}
		}
		return $friends;
	}
	/**
	 * @param TeamPlayer $teamPlayer
	 */
	public function addTeamPlayerInvite(TeamPlayer $teamPlayer){
		$this->teamPlayersInvites[$teamPlayer->getId()] = $teamPlayer;
		$this->teamPlayersInvitesByTeamName[$teamPlayer->getTeamName()] = $teamPlayer;
		$this->setHasTeamInvites(true);
	}
	/**
	 * @return TeamPlayer|null
	 */
	public function getTeamPlayer(): ?TeamPlayer{
		return $this->teamPlayer;
	}
	/**
	 * @param int $id
	 * @return TeamPlayer|null
	 */
	public function getTeamPlayerInvite(int $id): ?TeamPlayer{
		return ($this->teamPlayersInvites[$id] ?? null);
	}
	/**
	 * @return TeamPlayer[]
	 */
	public function getTeamPlayerInvites(): array{
		return $this->teamPlayersInvites;
	}
	/**
	 * @param string $name
	 * @return TeamPlayer|null
	 */
	public function getTeamPlayerInviteByTeamName(string $name): ?TeamPlayer{
		return ($this->teamPlayersInvitesByTeamName[$name] ?? null);
	}
	/**
	 * @param TeamPlayer $teamPlayer
	 */
	public function removeTeamPlayerInvite(TeamPlayer $teamPlayer){
		unset($this->teamPlayersInvites[$teamPlayer->getId()]);
		unset($this->teamPlayersInvitesByTeamName[$teamPlayer->getTeamName()]);
		if(count($this->teamPlayersInvites) === 0){
			$this->setHasTeamInvites(false);
		}
	}
	/**
	 * @param TeamPlayer|null $teamPlayer
	 */
	public function setTeamPlayer(?TeamPlayer $teamPlayer){
		$this->teamPlayer = $teamPlayer;
	}
	/**
	 * @param PartyPlayer $partyPlayer
	 */
	public function addPartyPlayerInvite(PartyPlayer $partyPlayer){
		$this->partyPlayerInvitesbyLeaderName[$partyPlayer->getLeaderName()] = $partyPlayer;
		$this->setHasPartyInvites(true);
	}
	/**
	 * @param string $name
	 * @return PartyPlayer|null
	 */
	public function getPartyPlayerInvitebyLeaderName(string $name): ?PartyPlayer{
		return ($this->partyPlayerInvitesbyLeaderName[$name] ?? null);
	}
	/**
	 * @param string $name
	 */
	public function removePartyPlayerInvitebyLeaderName(string $name){
		unset($this->partyPlayerInvitesbyLeaderName[$name]);
		if(count($this->partyPlayerInvitesbyLeaderName) === 0){
			$this->setHasPartyInvites(false);
		}
	}
	/**
	 * @param PartyPlayer $partyPlayer
	 */
	public function removePartyPlayerInvite(PartyPlayer $partyPlayer){
		$this->removePartyPlayerInvitebyLeaderName($partyPlayer->getLeaderName());
	}
	/**
	 * @return int
	 */
	public function getPartyLeaderUid(): int{
		return $this->partyLeaderUid;
	}
	/**
	 * @param int $partyLeaderUid
	 */
	private function setPartyLeaderUid(int $partyLeaderUid){
		$this->partyLeaderUid = $partyLeaderUid;
	}
	/**
	 * @param PartyPlayer|null $partyPlayer
	 */
	public function setPartyPlayer(?PartyPlayer $partyPlayer){
		$this->teamPlayer = $partyPlayer;
	}
	/**
	 * @return PartyPlayer|null
	 */
	public function getPartyPlayer(): ?PartyPlayer{
		return $this->partyPlayer;
	}
	/**
	 * @param Party|null $party
	 */
	public function setParty($party): ?Party{
		if($party instanceof Party){
			$this->setPartyLeaderUid($party->getLeaderUid());
		}else{
			$this->setPartyLeaderUid(-1);
		}
		$this->party = $party;
	}
	/**
	 * @return Party|null
	 */
	public function getParty(): ?Party{
		return $this->party;
	}
	/**
	 * @return GetUserDataQuery
	 */
	public function getSessionDataQuery(): GetUserDataQuery{
		return (new GetUserDataQuery($this->getPlugin(), function($result, $rows, $error){
			$this->setSessionData($rows, $result);
		}, $this->getPlayer()->getName()));
	}
	/**
	 * @param int $rows
	 * @param array $data
	 */
	public function setSessionData(int $rows, array $data){
		if(!$rows){
			$this->sendPresetMessage('player.register.welcome', ['%name%'], [$this->getPlayer()->getName()]);
			$this->setRegisterVars();
			$this->initLogin(true);
		}else{
			$this->initData($data[0]);
			$this->sendPresetMessage('player.login.welcomeBack', ['%name%'], [$this->getPlayer()->getName()]);
		}
		new GetUserAdminLogsQuery($this->getPlugin(), function($result, $rows, $error){
			if($rows > 0){
				$this->setHasAdminLogs(true);
				foreach($result as $row){
					$this->addAdminLog($adminLog = new AdminLog($row['id'], $row['uid'], $row['uuid'], $row['ip_string'], $row['creation_time'], $row['type'], $row['message'], $row['from_uid'], $row['from_ip_string'], $row['duration'], $row['ip_sensitive'], $row['uuid_sensitive']));
					if($adminLog->getType() === Constants::ADMIN_LOG_TYPE_MUTE){
						if(($until = ($adminLog->getDuration() + $adminLog->getCreationTime())) < time()){
							$this->setMuteUntil($until);
						}
					}elseif($adminLog->getType() === Constants::ADMIN_LOG_TYPE_BAN){
						if(($until = ($adminLog->getDuration() + $adminLog->getCreationTime())) < time()){
							$this->setBanUntil($until);
							$this->getPlayer()->kick($this->getPresetMessage('player.warnings.messages.banned', ['%duration%', '%reason%'], [Utils::secondsToTime(time() - $this->banUntil), $adminLog->getMessage()]));
						}
					}
				}
			}
		}, $this->getUid(), $this->getPlayer()->getAddress(), "X'" . bin2hex($this->getPlayer()->getRawUniqueId()) . "'");
	}
	/**
	 * @return bool
	 */
	private function hasPartyInvites(): bool{
		return $this->hasPartyInvites;
	}
	/**
	 * @param bool $value
	 */
	private function setHasPartyInvites(bool $value){
		$this->hasPartyInvites = (int) $value;
	}
	/**
	 * @return bool
	 */
	private function hasTeamInvites(): bool{
		return $this->hasTeamInvites;
	}
	/**
	 * @param bool|int $value
	 */
	private function setHasTeamInvites(bool $value){
		$this->hasTeamInvites = (int) $value;
	}
	/**
	 * @return bool
	 */
	private function hasFriends(): bool{
		return $this->hasFriends;
	}
	/**
	 * @param bool|int $value
	 */
	private function setHasFriends($value){
		$this->hasFriends = (int) $value;
	}
	/**
	 * @return bool
	 */
	private function hasAdminLogs(): bool{
		return (bool) $this->hasAdminLogs;
	}
	/**
	 * @param bool|int $value
	 */
	private function setHasAdminLogs($value){
		$this->hasAdminLogs = (int) $value;
	}
	/**
	 * @param array $data
	 */
	protected function initData(array $data){
		$this->uid = $data['uid'];
		$this->lastIp = $data['last_ip_string'];
		$this->online = $data['online'];
		$this->uuid = $data['uuid'];
		$this->coins = $data['coins'];
		$this->hash = $data['hash'];
		$this->email = $data['email'];
		$this->teamId = $data['team_id'];
		$this->partyLeaderUid = $data['party_leader_uid'];
		$this->chatLanguageCode = $data['chat_language_code'];
		$this->serverLanguageCode = $data['server_language_code'];
		$this->registrationTime = $data['registration_time'];
		$this->lastOn = $data['last_on'];
		$this->onTime = $data['on_time'];
		$this->rank = $data['rank'];
		$this->hasPartyInvites = $data['has_party_invites'];
		$this->hasFriends = $data['has_friends'];
		$this->hasTeamInvites = $data['has_team_invites'];
		$this->hasIgnored = $data['has_ignored'];
		if($this->online){
			$this->getPlayer()->kick($this->getPresetMessage('player.login.status.failed.alreadyOnline'));
			return;
		}
		if($this->banUntil !== -1 or $this->banUntil !== 0){
			$this->getPlayer()->kick($this->getPresetMessage('player.warnings.messages.banned', ['%duration%'], [Utils::secondsToTime(time() - $this->banUntil)]));
			return;
		}
		$this->addChatRoom($chatRoom = $this->getPlugin()->getChatRoom($this->getPlugin()->getChatRoomPrefix() . 'LANG-' . strtoupper(Utils::languageConstantToString($this->chatLanguageCode))));
		$this->setCurrentChatRoom($chatRoom);
		if($this->teamId !== -1){
			if(($team = $this->getPlugin()->getTeam($data['team_id'])) instanceof Team){
				$this->team = $team;
				$this->setTeamPlayer(($tp = $team->getTeamPlayer($this)));
				$tp->setOnline(true);
			}else{
				new GetTeamByIdQuery($this->getPlugin(), function($result, $rows, $error){
					if($rows >= 1){
						$teamData = $result;
						if(($team = $this->getPlugin()->getTeam($teamData['id'])) instanceof Team){
							$this->setTeam($team);
							$this->setTeamPlayer($team->getTeamPlayer($this));
							return;
						}
						$this->team = new Team($this->getPlugin(), $teamData['id'], $teamData['name'], $teamData['acronym'], $teamData['leader_uid'], $teamData['creation_time'], $chatRoom = new ChatRoom($this->getPlugin(), $this->getPlugin()->getChatRoomPrefix() . 'TEAM-ID-' . $teamData['id'], false));
						$this->getPlugin()->addChatRoom($chatRoom);
						$this->addChatRoom($chatRoom);
						foreach($teamData['players'] as $players){
							if($players['status'] === Constants::TEAM_STATUS_REQUESTED){
								if(($ses = $this->getPlugin()->findSessionByUid($players['invited_uid'])) instanceof BaseSession){
									if(($teamPlayer = $ses->getTeamPlayerInvite($players['id'])) instanceof TeamPlayer){
										$this->team->addTeamPlayer($teamPlayer);
										continue;
									}
								}
							}
							$this->team->addTeamPlayer(new TeamPlayer($this->getPlugin(), $players['id'], $this->team->getName(), $players['status'], $players['creation_time'], $players['invite_duration'], $players['accepted_time'], $players['inviter_uid'], $players['invited_uid'], $players['name'], $players['authenticated'], $players['rank']));
						}
						$this->setTeamPlayer(($tp = $this->team->getTeamPlayer($this)));
						$tp->setOnline(true);
						$this->getPlugin()->addTeam($team);
					}
				}, $this->teamId);
			}
		}
		if($this->hasTeamInvites){
			new GetUserTeamInvitesQuery($this->getPlugin(), function($result, $rows, $error){
				if($rows > 0){
					foreach($result as $teamPlayerData){
						if(($team = $this->getPlugin()->getTeam($teamPlayerData['id'])) instanceof Team){
							$teamPlayer = $team->getTeamPlayer($this);
							$this->addTeamPlayerInvite($teamPlayer);
							$teamPlayer->setOnline(true);
						}else{
							$this->addTeamPlayerInvite(new TeamPlayer($this->getPlugin(), $teamPlayerData['id'], $teamPlayerData['team_name'], $teamPlayerData['status'], $teamPlayerData['creationtime'], $teamPlayerData['invite_duration'], $teamPlayerData['accepted_time'], $teamPlayerData['inviter_uid'], $teamPlayerData['invited_uid'], $this->getName(), 1, $teamPlayerData['rank']));
						}
					}
				}
			}, $this->getUid());
		}
		if($this->hasFriends){
			new GetUserFriendsQuery($this->getPlugin(), function($result, $rows, $error){
				if($rows > 0){
					foreach($result as $friendData){
						if(($ses = $this->getPlugin()->findSessionByUid(($friendData['requester_uid'] === $this->getUid() ? $friendData['requested_uid'] : $friendData['requester_uid']))) instanceof BaseSession){
							if(($friendRelationship = $ses->getFriendRelationshipByUid($this->getUid())) instanceof FriendRelationship){
								$this->addFriendRelationship($friendRelationship);
								continue;
							}
						}
						$this->addFriendRelationship((new FriendRelationship($this->getPlugin(), $friendData['status'], $friendData['requester_uid'], $friendData['requester_name'], $friendData['requester_online'], $friendData['requested_uid'], $friendData['requested_name'], $friendData['requested_online'])));
					}
				}
			}, $this->getUid());
		}
		if($data['party_leader_uid'] !== -1){
			if(($party = $this->getPlugin()->getParty($data['party_leader_uid'])) instanceof Party){
				$this->setParty($party);
				$this->setPartyPlayer($party->getPartyPlayer($this));
			}else{
				new GetPartyPlayersByLeaderIdQuery($this->getPlugin(), function($result, $rows, $error)use($data){
					if(($party = $this->getPlugin()->getParty($data['party_leader_uid'])) instanceof Party){
						$this->setParty($party);
						$this->setPartyPlayer($party->getPartyPlayer($this));
						return;
					}
					$this->setParty($party = new Party($this->getPlugin(), $data['party_leader_uid']));
					$leaderName = null;
					foreach($result as $invite){
						if($invite['uid'] === $data['party_leader_uid']){
							$leaderName  = $invite['name'];
							break;
						}
					}
					foreach($result as $invite){
						if(($session = $this->getPlugin()->findSessionByUid($invite['uid'])) instanceof BaseSession){
							if(($partyPlayer = $session->getPartyPlayerInvitebyLeaderName($leaderName)) instanceof PartyPlayer){
								$party->addPartyPlayer($partyPlayer);
								continue;
							}
						}
						$party->addPartyPlayer(new PartyPlayer($this->getPlugin(), $data['party_leader_uid'], $leaderName, $invite['uid'], $invite['name'], $invite['status']));
					}
					$this->getPlugin()->addParty($party);
				}, $this->getPartyLeaderUid());
			}
		}
		if($this->hasPartyInvites){
			new GetUserPartyInvitesQuery($this->getPlugin(), function($result, $rows, $error){
				foreach($result as $invite){
					if($invite['status'] !== Constants::PARTY_STATUS_ACCEPTED){
						$this->addPartyPlayerInvite(new PartyPlayer($this->getPlugin(), $invite['leader_uid'], $invite['leader_name'], $invite['uid'], $this->getName(), $invite['status']));
					}
				}
			}, $this->getUid());
		}
		$this->initLogin();
	}
	/**
	 * @param string $message
	 */
	public function sendMessageIfLoggedIn(string $message){
		if($this->isOnline()){
			$this->sendMessage($message);
		}
	}
	/**
	 * @param string $identifier
	 * @param array $search
	 * @param array $replace
	 */
	public function sendPresetMessageIfLoggedIn(string $identifier, array $search = [], array $replace = []){
		if($this->isOnline()){
			$this->sendPresetMessage($identifier, $search, $replace);
		}
	}
	/**
	 * @return array
	 */
	protected abstract function getStatsData();
	/**
	 * @param PlayerJoinEvent $event
	 */
	public function PlayerJoinEvent(PlayerJoinEvent $event){

	}
	protected function setRegisterVars(){
		$this->lastIp = $this->getPlayer()->getAddress();
		$this->online = 1;
		$this->uuid = $this->getPlayer()->getUniqueId()->toString();
		$this->coins = Constants::DEFAULT_VALUE_COINS;
		$this->registrationTime = time();
		$this->lastOn = time();
		$this->email = Constants::DEFAULT_VALUE_EMAIL;
		$this->hash = Constants::DEFAULT_VALUE_HASH;
		$this->onTime = Constants::DEFAULT_VALUE_ON_TIME;
		$this->rank = Constants::DEFAULT_VALUE_RANK;
		$this->muteUntil = Constants::DEFAULT_VALUE_MUTE_UNTIL;
		$this->banUntil = Constants::DEFAULT_VALUE_BAN_UNTIL;
		$this->teamId = Constants::DEFAULT_VALUE_TEAM_ID;
		$this->partyLeaderUid = Constants::DEFAULT_VALUE_PARTY_LEADER_UID;
	}
	/**
	 * @param bool $isFirstLogin
	 */
	public function saveSessionData(bool $isFirstLogin = false){
		if($isFirstLogin){
			new CreateUserQuery($this->getPlugin(), function($result, $rows, $error){
				$this->setUid($result);
				$this->loginStage = self::LOGIN_STAGE_LOGGED_IN;
			}, [
				'name'                  => [$this->getPlayer()->getName(), Query::DATA_TYPE_STRING],
				'last_ip'               => [$this->getPlayer()->getAddress(), Query::DATA_TYPE_IP],
				'online'                => [$this->isOnline(), Query::DATA_TYPE_NUMERIC],
				'server_ip'             => [$this->getPlugin()->getServer()->getIp(), Query::DATA_TYPE_IP],
				'server_port'           => [$this->getPlugin()->getServer()->getPort(), Query::DATA_TYPE_NUMERIC],
				'uuid'                  => ["X'" . bin2hex($this->getPlayer()->getRawUniqueId()) . "'", Query::DATA_TYPE_RAW],
				'registration_time'     => [time(), Query::DATA_TYPE_NUMERIC],
				'last_on'               => [time(), Query::DATA_TYPE_NUMERIC]
			]);
		}else{
			if($this->getLastIp() !== $this->getPlayer()->getAddress()){
				$this->setLastIp($this->getPlayer()->getAddress());
				new CreateNewIpQuery($this->getPlugin(), function($result, $rows, $error){}, $this->getUid(), $this->getPlayer()->getAddress());
			}
			new SaveUserDataQuery($this->getPlugin(), function($result, $rows, $error){}, $this->getUid(), $this->getSaveData(), $this->getStatsData());
		}
	}
	/**
	 * @return array
	 */
	protected function getSaveData(){
		return [
			'name' => [$this->getPlayer()->getName(), Query::DATA_TYPE_STRING],
			'last_ip' => [$this->getPlayer()->getAddress(), Query::DATA_TYPE_IP],
			'online' => [(int) $this->isOnline(), Query::DATA_TYPE_NUMERIC],
			'server_ip' => [$this->getPlugin()->getServer()->getIp(), Query::DATA_TYPE_IP],
			'server_port' => [$this->getPlugin()->getServer()->getPort(), Query::DATA_TYPE_NUMERIC],
			'uuid' => [$this->getUUID(), Query::DATA_TYPE_RAW],
			'email' => [$this->getEmail(), Query::DATA_TYPE_STRING],
			'chat_language_code' => [$this->getChatLanguageCode(), Query::DATA_TYPE_NUMERIC],
			'server_language_code' => [$this->getServerLanguageCode(), Query::DATA_TYPE_NUMERIC],
			'team_id' => [$this->getTeamId(), Query::DATA_TYPE_NUMERIC],
			'coins' => [$this->getCoins(), Query::DATA_TYPE_NUMERIC],
			'hash' => [$this->getHash(), Query::DATA_TYPE_STRING],
			'last_on' => [time(), Query::DATA_TYPE_NUMERIC],
			'on_time' => [$this->getOnTime(), Query::DATA_TYPE_NUMERIC],
			'party_leader_uid' => [$this->getPartyLeaderUid(), Query::DATA_TYPE_NUMERIC],
			'rank' => [$this->getRank(), Query::DATA_TYPE_NUMERIC],
			'has_party_invites' => [(int) $this->hasPartyInvites(), Query::DATA_TYPE_NUMERIC],
			'has_friends' => [(int) $this->hasFriends(), Query::DATA_TYPE_NUMERIC],
			'has_team_invites' => [(int) $this->hasTeamInvites(), Query::DATA_TYPE_NUMERIC]
		];
	}
	/**
	 * @param string $message
	 * @param bool $sendIfLoggedIn
	 */
	public function addLoginMessage(string $message, bool $sendIfLoggedIn = true){
		if($sendIfLoggedIn and $this->isOnline()){
			$this->sendMessage($message);
		}else{
			$this->loginMessages[] = $message;
		}
	}
	public function sendLoginMessages(){
		foreach($this->loginMessages as $message){
			$this->getPlayer()->sendMessage($message);
		}
		$this->loginMessages = [];
	}
	/**
	 * @param bool $isFirstLogin
	 */
	private function initLogin(bool $isFirstLogin = false){
		$this->resetCurrentChatRoom();
		$this->setOnline(true);
		$this->loggedInTime = time();
		$this->saveSessionData($isFirstLogin);
		$this->sendLoginMessages();
		foreach($this->getFriendRelationships() as $friendRelationship){
			if($friendRelationship->getRequesterUid() === $this->getUid()){
				$friendRelationship->setRequesterOnline(true);
			}else{
				$friendRelationship->setRequestedOnline(true);
			}
		}
		foreach($this->getTeamPlayerInvites() as $teamPlayerInvite){
			$teamPlayerInvite->setOnline(true);
		}
		if(($team = $this->getTeam()) instanceof Team){
			$team->getTeamPlayer($this)->setOnline(true);
		}
	}
	private function initLogout(){
		$this->setOnline(false);
		$this->saveSessionData();
		foreach($this->getTeamPlayerInvites() as $teamPlayerInvite){
			$teamPlayerInvite->setOnline(false);
		}
		foreach($this->getFriendRelationships() as $friendRelationship){
			if($friendRelationship->getRequesterUid() === $this->getUid()){
				$friendRelationship->setRequesterOnline(false);
			}else{
				$friendRelationship->setRequestedOnline(false);
			}
		}
		if(($team = $this->getTeam()) instanceof Team){
			$team->getTeamPlayer($this)->setOnline(false);
			$team->checkRemove();
		}
		// double check this later
		if(($party = $this->getParty()) instanceof Party){
			$party->removePartyPlayerByUid($this->getUid());
			$party->checkRemove();
		}
	}
	/**
	 * @param PlayerQuitEvent $event
	 */
	public function PlayerQuitEvent(PlayerQuitEvent $event){
		$this->initLogout();
		$this->getPlugin()->removeSession($this);
	}
	/**
	 * @param string $identifier
	 * @param string[] $findWords
	 * @param string[] $replaceWords
	 * @return string
	 */
	public function getPresetMessage(string $identifier, array $findWords = [], array $replaceWords = []): string{
		return $this->getPlugin()->getResourceManager()->getMessage($this->getServerLanguageCodeString(), $identifier, $findWords, $replaceWords);
	}
	/**
	 * @param string $identifier
	 * @param string[] $findWords
	 * @param string[] $replaceWords
	 */
	public function sendPresetMessage(string $identifier, array $findWords = [], array $replaceWords = []){
		$this->getPlayer()->sendMessage($this->getPresetMessage($identifier, $findWords, $replaceWords));
	}
	/**
	 * @param PresetMessageError $error
	 */
	public function sendPresetMessageError(PresetMessageError $error){
		$this->sendMessage($error->getPresetMessage($this->getPlugin(), $this->getServerLanguageCodeString()));
	}
	/**
	 * @param string $message
	 */
	public function sendMessage(string $message): string{
		$this->getPlayer()->sendMessage($message);
	}
	/**
	 * @param PlayerPreLoginEvent $event
	 */
	public function PlayerPreLoginEvent(PlayerPreLoginEvent $event){

	}
	/**
	 * @param PlayerLoginEvent $event
	 */
	public function PlayerLoginEvent(PlayerLoginEvent $event){

	}
	/**
	 * @param PlayerChatEvent $event
	 */
	public function PlayerChatEvent(PlayerChatEvent $event){
		$message = $event->getMessage();
		if(!$this->isOnline()){
			return false;
		}else{
			if($this->getMuteUntil() > 0 and strpos($this->currentChatRoom->getKey(), 'LANG') !== false){
				$this->sendPresetMessage('player.warnings.messages.muted', ['%duration%'], [Utils::secondsToTime(time() - $this->getMuteUntil())]);
				return false;
			}
			$this->currentChatRoom->broadcast($event->getMessage());
			$this->lastChatMessage = $message;
			return false;
		}
	}
	/**
	 * @param PlayerKickEvent $event
	 */
	public function PlayerKickEvent(PlayerKickEvent $event){

	}
	/**
	 * @param EntityDamageEvent $event
	 * @return bool
	 */
	public function EntityDamageEvent(EntityDamageEvent $event){

	}
	/**
	 * @param PlayerDeathEvent $event
	 * @return bool
	 */
	public function PlayerDeathEvent(PlayerDeathEvent $event){

	}
	/**
	 * @param PlayerDropItemEvent $event
	 * @return bool
	 */
	public function PlayerDropItemEvent(PlayerDropItemEvent $event){

	}
	/**
	 * @param PlayerInteractEvent $event
	 * @return bool
	 */
	public function PlayerInteractEvent(PlayerInteractEvent $event){

	}
	/**
	 * @param PlayerItemConsumeEvent $event
	 * @return bool
	 */
	public function PlayerItemConsumeEvent(PlayerItemConsumeEvent $event){

	}
	/**
	 * @param PlayerItemHeldEvent $event
	 * @return bool
	 */
	public function PlayerItemHeldEvent(PlayerItemHeldEvent $event){

	}
	/**
	 * @param PlayerMoveEvent $event
	 */
	public function PlayerMoveEvent(PlayerMoveEvent $event){
		/*if(!$this->isAuthenticated()){
			if(!$event->getFrom()->equals($event->getTo())){
				return false;
			}
		}*/
	}
	/**
	 * @param PlayerRespawnEvent $event
	 */
	public function PlayerRespawnEvent(PlayerRespawnEvent $event){

	}
	/**
	 * @param PlayerToggleSneakEvent $event
	 */
	public function PlayerToggleSneakEvent(PlayerToggleSneakEvent $event){

	}
	/**
	 * @param PlayerToggleSprintEvent $event
	 */
	public function PlayerToggleSprintEvent(PlayerToggleSprintEvent $event){

	}
	/**
	 * @param PlayerBedEnterEvent $event
	 * @return bool
	 */
	public function PlayerBedEnterEvent(PlayerBedEnterEvent $event){

	}
	/**
	 * @param PlayerBedLeaveEvent $event
	 * @return bool
	 */
	public function PlayerBedLeaveEvent(PlayerBedLeaveEvent $event){

	}
	/**
	 * @param PlayerGameModeChangeEvent $event
	 * @return bool
	 */
	public function PlayerGameModeChangeEvent(PlayerGameModeChangeEvent $event){

	}
	/**
	 * @param BlockBreakEvent $event
	 * @return bool
	 */
	public function BlockBreakEvent(BlockBreakEvent $event){

	}
	/**
	 * @param BlockPlaceEvent $event
	 * @return bool
	 */
	public function BlockPlaceEvent(BlockPlaceEvent $event){

	}
	/**
	 * @param SignChangeEvent $event
	 * @return bool
	 */
	public function SignChangeEvent(SignChangeEvent $event){

	}
	/**
	 * @param InventoryCloseEvent $event
	 */
	public function InventoryCloseEvent(InventoryCloseEvent $event){

	}
	/**
	 * @param InventoryOpenEvent $event
	 */
	public function InventoryOpenEvent(InventoryOpenEvent $event){

	}
	/**
	 * @param PlayerCommandPreprocessEvent $event
	 * @return bool
	 */
	public function PlayerCommandPreprocessEvent(PlayerCommandPreprocessEvent $event){

	}
	/**
	 * @param EntityInventoryChangeEvent $event
	 * @return bool
	 */
	public function EntityInventoryChangeEvent(EntityInventoryChangeEvent $event){

	}
	/**
	 * @param EntityInventoryChangeEvent $event
	 */
	public function EntityLevelChangeEvent(EntityLevelChangeEvent $event){

	}
	/**
	 * @param EntityRegainHealthEvent $event
	 * @return bool
	 */
	public function EntityRegainHealthEvent(EntityRegainHealthEvent $event){

	}
	/**
	 * @param EntityShootBowEvent $event
	 * @return bool
	 */
	public function EntityShootBowEvent(EntityShootBowEvent $event){

	}
	/**
	 * @param EntityTeleportEvent $event
	 */
	public function EntityTeleportEvent(EntityTeleportEvent $event){

	}
}
