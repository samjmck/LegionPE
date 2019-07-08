<?php

namespace LegionPE\Iota\base\team;

use LegionPE\Iota\base\ChatRoom;
use LegionPE\Iota\base\Constants;
use LegionPE\Iota\base\event\team\TeamRemoveEvent;
use LegionPE\Iota\base\query\team\CreateTeamPlayerQuery;
use LegionPE\Iota\base\query\team\CreateTeamQuery;
use LegionPE\Iota\base\query\team\DeleteTeamPlayerQuery;
use LegionPE\Iota\base\query\team\DeleteTeamPlayersQuery;
use LegionPE\Iota\base\query\team\DeleteTeamQuery;
use LegionPE\Iota\base\query\team\GetNextTeamIdQuery;
use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\BaseSession;

class Team{
	/** @var BasePlugin */
	private $plugin;
	/** @var int */
	private $id;
	/** @var string */
	private $name;
	/** @var string */
	private $acronym;
	/** @var TeamPlayer[] */
	private $teamPlayersByUid = [];
	/** @var TeamPlayer[] */
	private $teamPlayersByName = [];
	/** @var int */
	private $leaderUid;
	/** @var int */
	private $creationTime;
	/** @var ChatRoom */
	private $chatRoom;
	/**
	 * @param BasePlugin $plugin
	 * @param int $id
	 * @param string $name
	 * @param int $leaderUid
	 * @param int $creationTime
	 * @param ChatRoom $chatRoom
	 */
	public function __construct(BasePlugin $plugin, int $id, string $name, string $acronym, int $leaderUid, int $creationTime, ChatRoom $chatRoom){
		$this->plugin = $plugin;
		$this->id = $id;
		$this->name = $name;
		$this->acronym = $acronym;
		$this->leaderUid = $leaderUid;
		$this->creationTime = $creationTime;
		$this->chatRoom = $chatRoom;
	}
	/**
	 * @param BaseSession $leader
	 * @param callable $callback
	 * @param string $name
	 */
	public static function createTeam(BaseSession $leader, callable $callback, $name){
		new CreateTeamQuery($leader->getPlugin(), $callback, $name, $leader->getUid(), Constants::TEAM_RANK_OWNER);
	}
	/**
	 * @return string
	 */
	public function getPrefix(): string{
		return '§7[§6' . $this->getAcronym() . '§7]§f§r';
	}
	public function checkRemove(){
		if($this->getAcceptedAuthenticatedSessionsFromServer()) return;
		$this->getPlugin()->removeChatRoom($this->getChatRoom());
		$this->getPlugin()->removeTeam($this);
	}
	/**
	 * @return int
	 */
	public function getId(): int{
		return $this->id;
	}
	/**
	 * @return string
	 */
	public function getName(): string{
		return $this->name;
	}
	/**
	 * @return string
	 */
	public function getAcronym(): string{
		return $this->acronym;
	}
	/**
	 * @return int
	 */
	public function getLeaderUid(): int{
		return $this->leaderUid;
	}
	/**
	 * @return int
	 */
	public function getCreationTime(): int{
		return $this->creationTime;
	}
	/**
	 * @return BasePlugin
	 */
	public function getPlugin(): BasePlugin{
		return $this->plugin;
	}
	/**
	 * @param TeamPlayer $teamPlayer
	 */
	public function addTeamPlayer(TeamPlayer $teamPlayer){
		$this->teamPlayersByUid[$teamPlayer->getUid()] = $teamPlayer;
		$this->teamPlayersByName[$teamPlayer->getName()] = $teamPlayer;
	}
	/**
	 * @param TeamPlayer $teamPlayer
	 */
	public function removeTeamPlayer(TeamPlayer $teamPlayer){
		unset($this->teamPlayersByUid[$teamPlayer->getUid()]);
		unset($this->teamPlayersByName[$teamPlayer->getName()]);
	}
	/**
	 * @param string $name
	 */
	public function removeTeamPlayerByName(string $name): string{
		$this->removeTeamPlayer($this->getTeamPlayerByName($name));
	}
	/**
	 * @param int $uid
	 */
	public function removeTeamPlayerByUid(int $uid): int{
		$this->removeTeamPlayer($this->getTeamPlayerByUid($uid));
	}
	/**
	 * @return TeamPlayer[]
	 */
	public function getTeamPlayers(): array{
		return $this->teamPlayersByUid;
	}
	/**
	 * @return TeamPlayer[]
	 */
	public function getAcceptedTeamPlayers(): array{
		$sessions = [];
		foreach($this->teamPlayersByUid as $uid => $teamPlayer){
			$sessions[] = $teamPlayer;
		}
		return $sessions;
	}
	/**
	 * @return BaseSession[]
	 */
	public function getAcceptedSessionsFromServer(): array{
		$sessions = [];
		foreach($this->teamPlayersByUid as $uid => $teamPlayer){
			if(($session = $this->getPlugin()->findSessionByUid($teamPlayer->getUid())) instanceof BaseSession and $teamPlayer->getStatus() === Constants::TEAM_STATUS_ACCEPTED){
				$session[] = $session;
			}
		}
		return $sessions;
	}
	/**
	 * @return BaseSession[]
	 */
	public function getAcceptedAuthenticatedSessionsFromServer(): array{
		$sessions = [];
		foreach($this->teamPlayersByUid as $uid => $teamPlayer){
			if(($session = $this->getPlugin()->findSessionByUid($teamPlayer->getUid())) instanceof BaseSession and $teamPlayer->getStatus() === Constants::TEAM_STATUS_ACCEPTED and $teamPlayer->isAuthenticated()){
				$session[] = $session;
			}
		}
		return $sessions;
	}
	/**
	 * @return string[]
	 */
	public function getAcceptedAuthenticatedPlayerNames(): array{
		$names = [];
		foreach($this->teamPlayersByUid as $uid => $teamPlayer){
			if($teamPlayer->isAuthenticated() and $teamPlayer->getStatus() === Constants::TEAM_STATUS_ACCEPTED){
				$names[] = $teamPlayer->getName();
			}
		}
		return $names;
	}
	/**
	 * @return int[]
	 */
	public function getAcceptedAuthenticatedOnlineUids(): array{
		$uids = [];
		foreach($this->teamPlayersByUid as $uid => $teamPlayer){
			if($teamPlayer->isAuthenticated() and $teamPlayer->getStatus() === Constants::TEAM_STATUS_ACCEPTED){
				$uids[] = $teamPlayer->getUid();
			}
		}
		return $uids;
	}
	/**
	 * @return string[]
	 */
	public function getInvitedPlayerNames(): array{
		$names = [];
		foreach($this->teamPlayersByUid as $uid => $teamPlayer){
			if($teamPlayer->getStatus() === Constants::TEAM_STATUS_REQUESTED){
				$names[] = $teamPlayer->getName();
			}
		}
		return $names;
	}
	/**
	 * @return int[]
	 */
	public function getInvitedUids(): array{
		$uids = [];
		foreach($this->teamPlayersByUid as $uid => $teamPlayer){
			if($teamPlayer->getStatus() === Constants::TEAM_STATUS_REQUESTED){
				$uids[] = $teamPlayer->getUid();
			}
		}
		return $uids;
	}
	/**
	 * @return string[]
	 */
	public function getPlayerNames(): array{
		$names = [];
		foreach($this->teamPlayersByUid as $uid => $teamPlayer){
			$names[] = $teamPlayer->getName();
		}
		return $names;
	}
	/**
	 * @return int[]
	 */
	public function getUids(): array{
		$uids = [];
		foreach($this->teamPlayersByUid as $uid => $teamPlayer){
			$uids[] = $teamPlayer->getUid();
		}
		return $uids;
	}
	/**
	 * @param string $message
	 * @param bool $local
	 */
	public function broadcast(string $message, bool $local = false){
		$sent = 0;
		if($local){
			foreach($this->getTeamPlayers() as $partyPlayer){
				if(($session = $this->getPlugin()->getAuthenticatedSessionByUid($partyPlayer->getUid())) instanceof BaseSession and $partyPlayer->getStatus() === Constants::TEAM_STATUS_ACCEPTED){
					$session->sendMessage($this->getPrefix() . " " . $message);
					++$sent;
				}
			}
			return $sent;
		}else{
			$sent = $this->broadcast($message, true);
			if($sent !== count($this->getTeamPlayers())){
				$this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_TEAM_BROADCAST, ['id' => $this->getId(), 'message' => $message]);
			}
			return $sent;
		}
	}
	/**
	 * @param string $key
	 * @param array $search
	 * @param array $replace
	 * @param bool $local
	 */
	public function broadcastPresetMessage(string $key, array $search = [], array $replace = [], bool $local = false){
		$sent = 0;
		if($local){
			foreach($this->getTeamPlayers() as $partyPlayer){
				if(($session = $this->getPlugin()->getAuthenticatedSessionByUid($partyPlayer->getUid())) instanceof BaseSession and $partyPlayer->getStatus() === Constants::TEAM_STATUS_ACCEPTED){
					$session->sendMessage($this->getPrefix() . " " . $session->getPresetMessage($key, $search, $replace));
					++$sent;
				}
			}
			return $sent;
		}else{
			$sent = $this->broadcastPresetMessage($key, $search, $replace, true);
			if($sent !== count($this->getTeamPlayers())){
				$this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_TEAM_BROADCAST_PRESET_MESSAGE, ['id' => $this->getId(), 'key' => $key, 'search' => $search, 'replace' => $replace]);
			}
			return $sent;
		}
	}
	/**
	 * @param BaseSession $session
	 */
	public function leave(BaseSession $session){
		$session->setTeam(null);
		$session->setTeamPlayer(null);
		if($session->getCurrentChatRoom() === $this->getChatRoom()){
			$session->resetCurrentChatRoom();
		}
		$session->removeChatRoom($this->getChatRoom());
		$this->checkRemove();
		$session->sendPresetMessage('commands.team.arguments.leave.messages.success');
		$this->plugin->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_TEAM_LEAVE, ['id' => $this->getId(), 'uid' => $session->getUid()]);
		new DeleteTeamPlayerQuery($this->plugin, function($result, $rows, $error){}, $this->getId(), $session->getUid());
		self::routerLeave($this, $session->getUid());
	}
	/**
	 * @param Team $team
	 * @param int $uid
	 */
	public static function routerLeave(Team $team, int $uid){
		$team->broadcastPresetMessage('commands.team.arguments.leave.messages.broadcastMessage', ['%player%'], [$team->getTeamPlayerByUid($uid)->getName()], true);
		$team->removeTeamPlayerByUid($uid);
	}
	/**
	 * @param BaseSession $session
	 * @param int $kickerUid
	 */
	public function kick(BaseSession $kicker, int $kickedUid){
		$kicker->sendPresetMessage('commands.team.arguments.kick.messages.success', ['%user%'], [$this->getTeamPlayerByName($kickedUid)->getName()]);
		self::routerKick($this, $kicker->getUid(), $kickedUid);
		$this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_TEAM_KICK, ['id' => $this->getId(), 'kickerUid' => $kicker->getUid(), 'kickedUid' => $kickedUid]);
		new DeleteTeamPlayerQuery($this->plugin, function($result, $rows, $error){}, $this->getId(), $kickedUid);
	}
	/**
	 * @param Team $team
	 * @param int $kickerUid
	 * @param int $kickedUid
	 */
	public static function routerKick(Team $team, int $kickerUid, int $kickedUid){
		$kickerTeamPlayer = $team->getTeamPlayerByUid($kickerUid);
		if(($session = $team->getPlugin()->findSessionByUid($kickedUid)) instanceof BaseSession){
			$session->addLoginMessage($session->getPresetMessage('commands.team.arguments.kick.messages.kicked', ['%kicker%'], [$kickerTeamPlayer->getName()]));
			$session->setTeam(null);
			$session->setTeamPlayer(null);
			$team->checkRemove();
		}
		$kickedName = $team->getTeamPlayerByUid($kickedUid);
		$team->removeTeamPlayerByUid($kickedUid);
		$team->broadcastPresetMessage('commands.team.arguments.kick.messages.broadcastMessage', ['%user%', '%kicker%'], [$kickedName, $kickerTeamPlayer->getName()], true);
	}
	/**
	 * @param BaseSession $promoter
	 * @param TeamPlayer $promotedTeamPlayer
	 */
	public function promote(BaseSession $promoter, TeamPlayer $promotedTeamPlayer){
		if(($newRank = $promotedTeamPlayer->getRank() * 2) < Constants::TEAM_RANK_OWNER){
			$promotedTeamPlayer->setRank($newRank);
			$promotedTeamPlayer->saveData();
			$promoter->sendPresetMessage('commands.team.arguments.promote.messages.success', ['%user%', '%rank%'], [$promotedTeamPlayer->getName(), $promoter->getPresetMessage('ranks.team.' . $newRank)]);
			$this->plugin->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_TEAM_PROMOTE, ['id' => $this->getId(), 'uid' => $promotedTeamPlayer->getUid(), 'promoterUid' => $promoter->getUid(), 'rank' => $newRank]);
		}else{
			$promoter->sendPresetMessage('commands.team.arguments.promote.messages.alreadyHighest', ['%user%'], [$promotedTeamPlayer->getName()]);
		}
	}
	/**
	 * @param Team $team
	 * @param int $promoterUid
	 * @param int $promotedUid
	 */
	public static function routerPromote(Team $team, int $promoterUid, int $promotedUid){
		if(($teamPlayer = $team->getTeamPlayerByUid($promotedUid)) instanceof TeamPlayer){
			if(($newRank = $teamPlayer->getRank() * 2) < Constants::TEAM_RANK_OWNER){
				$teamPlayer->setRank($newRank);
				$teamPlayer->saveData();
			}
		}
	}
	/**
	 * @param BaseSession $demoter
	 * @param TeamPlayer $demoterTeamPlayer
	 */
	public function demote(BaseSession $demoter, TeamPlayer $demotedTeamPlayer){
		if(($newRank = $demotedTeamPlayer->getRank() / 2) >= Constants::TEAM_RANK_MEMBER){
			$demotedTeamPlayer->setRank($newRank);
			$demotedTeamPlayer->saveData();
			$demoter->sendPresetMessage('commands.team.arguments.demote.messages.success', ['%user%', '%rank%'], [$demotedTeamPlayer->getName(), $demoter->getPresetMessage('ranks.team.' . $newRank)]);
			$this->plugin->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_TEAM_DEMOTE, ['id' => $this->getId(), 'uid' => $demotedTeamPlayer->getUid(), 'demoterUid' => $demoter->getUid(), 'rank' => $newRank]);
		}else{
			$demoter->sendPresetMessage('commands.team.arguments.demote.messages.alreadyLowest', ['%user%'], [$demotedTeamPlayer->getName()]);
		}
	}
	/**
	 * @param Team $team
	 * @param int $demoterUid
	 * @param int $demotedUid
	 */
	public static function routerDemote(Team $team, int $demoterUid, int $demotedUid){
		if(($teamPlayer = $team->getTeamPlayerByUid($demotedUid)) instanceof TeamPlayer){
			if(($newRank = $teamPlayer->getRank() / 2) >= Constants::TEAM_RANK_MEMBER){
				$teamPlayer->setRank($newRank);
				$teamPlayer->saveData();
			}
		}
	}
	/**
	 * @param BaseSession $inviter
	 * @param int $invitedUid
	 * @param string $invitedName
	 */
	public function invite(BaseSession $inviter, int $invitedUid, string $invitedName){
		new CreateTeamPlayerQuery($this->getPlugin(), function($result, $rows, $error)use($inviter, $invitedUid, $invitedName){
			if(strpos($error, 'Duplicate entry') !== false){
				$inviter->sendPresetMessage('commands.team.arguments.invite.messages.alreadyInvited', ['%user%'], [$invitedName]);
				return;
			}$creationTime = time();
			$inviter->sendPresetMessage('commands.team.arguments.invite.messages.success', ['%user%'], [$invitedName]);
			$this->addTeamPlayer(($teamPlayer = new TeamPlayer($this->getPlugin(), $this->getId(), $this->getName(), Constants::TEAM_STATUS_REQUESTED, $creationTime, -1, -1, $inviter->getUid(), $invitedUid, $invitedName, 1, Constants::TEAM_RANK_MEMBER)));
			if(($invitedSession = $this->getPlugin()->findSessionByUid($invitedUid)) instanceof BaseSession){
				$invitedSession->addTeamPlayerInvite($teamPlayer);
				$invitedSession->addLoginMessage($invitedSession->getPresetMessage('commands.team.arguments.invite.messages.receiverMessages', ['%player%', '%team%'], [$inviter->getPlayer()->getName(), $this->getName()]));
			}else{
				$this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_TEAM_INVITE, ['id' => $this->getId(), 'teamName' => $this->getName(), 'inviterUid' => $inviter->getUid(), 'inviterName' => $inviter->getPlayer()->getName(), 'invitedUid' => $invitedUid, 'invitedName' => $invitedName, 'creationTime' => $creationTime, 'inviteDuration' => -1]);
			}
		}, $this->getId(), -1, $inviter->getUid(), $invitedUid, 0, Constants::TEAM_STATUS_REQUESTED);
	}
	/**
	 * @param Team $team
	 * @param int $inviterUid
	 * @param int $invitedUid
	 * @param string $invitedName
	 */
	public static function routerInvite(Team $team, int $inviterUid, int $invitedUid, string $invitedName){
		$team->addTeamPlayer(($teamPlayer = new TeamPlayer($team->getPlugin(), $team->getId(), $team->getName(), Constants::TEAM_STATUS_REQUESTED, time(), -1, -1, $inviterUid, $invitedUid, $invitedName, 1, Constants::TEAM_RANK_MEMBER)));
		if(($invitedSession = $team->getPlugin()->findSessionByUid($invitedUid)) instanceof BaseSession){
			$invitedSession->addTeamPlayerInvite($teamPlayer);
			$invitedSession->addLoginMessage($invitedSession->getPresetMessage('commands.team.arguments.invite.messages.receiverMessages', ['%user%', '%team%'], [$team->getTeamPlayerByUid($inviterUid)->getName(), $team->getName()]));
		}
	}
	/**
	 * @param int $uid
	 * @return bool
	 */
	public function isUidAuthenticated(int $uid): bool{
		return $this->teamPlayersByUid[$uid]->isAuthenticated();
	}
	/**
	 * @param int $uid
	 * @param bool|int $authenticated
	 */
	public function setUidAuthenticated(int $uid, $authenticated){
		$this->teamPlayersByUid[$uid]->setAuthenticated($authenticated);
	}
	/**
	 * @param BaseSession $session
	 * @return int
	 */
	public function getRank(BaseSession $session): int{
		return $this->getRankByUid($session->getUid());
	}
	/**
	 * @param int $uid
	 * @return int
	 */
	public function getRankByUid(int $uid): int{
		return $this->teamPlayersByUid[$uid]->getRank();
	}
	/**
	 * @param BaseSession $session
	 * @param int $rank
	 */
	public function setRank(BaseSession $session, int $rank){
		$this->setRankByUid($session->getUid(), $rank);
	}
	/**
	 * @param int $uid
	 * @param int $rank
	 */
	public function setRankByUid(int $uid, int $rank){
		$this->teamPlayersByUid[$uid]->setRank($rank);
	}
	/**
	 * @param string $name
	 * @return TeamPlayer|null
	 */
	public function getTeamPlayerByName(string $name){
		return ($this->teamPlayersByName[$name] ?? null);
	}
	/**
	 * @param int $uid
	 */
	public function getTeamPlayerByUid(int $uid){
		return ($this->teamPlayersByUid[$uid] ?? null);
	}
	/**
	 * @return ChatRoom
	 */
	public function getChatRoom(): ChatRoom{
		return $this->chatRoom;
	}
	/**
	 * @param ChatRoom $chatRoom
	 */
	public function setChatRoom(ChatRoom $chatRoom){
		$this->chatRoom = $chatRoom;
	}
	/**
	 * @param BaseSession $session
	 * @return TeamPlayer
	 */
	public function getTeamPlayer(BaseSession $session): TeamPlayer{
		return $this->getTeamPlayerByUid($session->getUid());
	}
	/**
	 * @param BaseSession $disbander
	 * @param $sync bool
	 */
	public function disband(BaseSession $disbander){
		self::routerDisband($this, $disbander->getUid());
		new DeleteTeamPlayersQuery($this->plugin, function($data){}, $this->getId());
		new DeleteTeamQuery($this->plugin, function($data){}, $this->getId());
		$this->plugin->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_TEAM_DISBAND, ['id' => $this->getId(), 'disbanderUid' => $disbander->getUid()]);
	}
	/**
	 * @param Team $team
	 * @param int $disbanderUid
	 */
	public static function routerDisband(Team $team, int $disbanderUid){
		$team->getPlugin()->getServer()->getPluginManager()->callEvent(new TeamRemoveEvent($team->getPlugin(), $team));
		$team->broadcast($team->getPlugin()->getResourceManager()->getMessage('en', 'commands.team.arguments.disband.messages.broadcastMessage', ['%user%'], [$team->getTeamPlayerByUid($disbanderUid)->getName()]), true);
		$team->getPlugin()->removeTeam($team);
		foreach($team->getTeamPlayers() as $teamPlayer){
			if(($session = $team->getPlugin()->findSessionByUid($teamPlayer->getUid())) instanceof BaseSession){
				if($teamPlayer->getStatus() === Constants::TEAM_STATUS_ACCEPTED){
					$session->setTeam(null);
					$session->setTeamPlayer(null);
				}else{
					$session->removeTeamPlayerInvite($teamPlayer);
				}
			}
		}
	}
	/**
	 * @param int $uid
	 */
	public function saveData(int $uid){
		$this->getTeamPlayerByUid($uid)->saveData();
	}
}
