<?php

namespace LegionPE\Iota\base\party;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\BaseSession;
use LegionPE\Iota\base\ChatRoom;
use LegionPE\Iota\base\Constants;
use LegionPE\Iota\base\DeletePartyPlayerQuery;
use LegionPE\Iota\base\DeletePartyPlayersQuery;
use LegionPE\Iota\base\event\party\PartyRemoveEvent;
use LegionPE\Iota\base\query\party\CreatePartyPlayerQuery;
use LegionPE\Iota\base\query\team\DeleteTeamPlayerQuery;

class Party{
	/** @var BasePlugin */
	private $plugin;
	/** @var int */
	private $leaderUid;
	/** @var PartyPlayer[] */
	private $partyPlayersByUid;
	/** @var PartyPlayer[] */
	private $partyPlayersByName;
	/** @var ChatRoom */
	private $chatRoom;
	/**
	 * @param BasePlugin $plugin
	 * @param int $leaderUid
	 * @param ChatRoom $chatRoom
	 */
	public function __construct(BasePlugin $plugin, int $leaderUid, ChatRoom $chatRoom){
		$this->plugin = $plugin;
		$this->leaderUid = $leaderUid;
		$this->chatRoom = $chatRoom;
	}
	public function checkRemove(){
		if($this->getAcceptedAuthenticatedSessionsFromServer()) return;
		$this->getPlugin()->removeChatRoom($this->getChatRoom());
		$this->getPlugin()->removeParty($this);
	}
	/**
	 * @return BasePlugin
	 */
	public function getPlugin(): BasePlugin{
		return $this->plugin;
	}
	/**
	 * @return int
	 */
	public function getLeaderUid(): int{
		return $this->leaderUid;
	}
	/**
	 * @return PartyPlayer[]
	 */
	public function getPartyPlayers(): array{
		return $this->partyPlayersByUid;
	}
	/**
	 * @return PartyPlayer[]
	 */
	public function getPartyPlayersByUid(): array{
		return $this->partyPlayersByUid;
	}
	/**
	 * @return PartyPlayer[]
	 */
	public function getPartyPlayersByName(): array{
		return $this->partyPlayersByName;
	}
	/**
	 * @param PartyPlayer $partyPlayer
	 */
	public function addPartyPlayer(PartyPlayer $partyPlayer){
		$this->partyPlayersByName[$partyPlayer->getName()] = $partyPlayer;
		$this->partyPlayersByUid[$partyPlayer->getUid()] = $partyPlayer;
	}
	/**
	 * @param int $uid
	 */
	public function removePartyPlayerByUid(int $uid){
		$partyPlayer = $this->partyPlayersByUid[$uid];
		$this->removePartyPlayer($partyPlayer);
	}
	/**
	 * @param string $name
	 */
	public function removePartyPlayerByName(string $name){
		$partyPlayer = $this->partyPlayersByName[$name];
		$this->removePartyPlayer($partyPlayer);
	}
	/**
	 * @param PartyPlayer $partyPlayer
	 */
	public function removePartyPlayer(PartyPlayer $partyPlayer){
		unset($this->partyPlayersByUid[$partyPlayer->getUid()]);
		unset($this->partyPlayersByName[$partyPlayer->getName()]);
	}
	/**
	 * @param int $uid
	 * @return PartyPlayer|null
	 */
	public function getPartyPlayerByUid(string $uid){
		return ($this->partyPlayersByUid[$uid] ?? null);
	}
	/**
	 * @param string $name
	 * @return PartyPlayer|null
	 */
	public function getPartyPlayerByName(string $name){
		return ($this->partyPlayersByName[$name] ?? null);
	}
	/**
	 * @param BaseSession $session
	 * @return PartyPlayer|null
	 */
	public function getPartyPlayer(BaseSession $session){
		return $this->getPartyPlayerByUid($session->getUid());
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
	 * @return BaseSession[]
	 */
	public function getAcceptedSessionsFromServer(): array{
		$sessions = [];
		foreach($this->partyPlayersByUid as $uid => $partyPlayer){
			if(($session = $this->getPlugin()->findSessionByUid($uid)) instanceof BaseSession){
				$sessions[$uid] = $session;
			}
		}
		return $sessions;
	}
	/**
	 * @return BaseSession[]
	 */
	public function getAcceptedAuthenticatedSessionsFromServer(): array{
		$sessions = [];
		foreach($this->partyPlayersByUid as $uid => $partyPlayer){
			if(($session = $this->getPlugin()->findSessionByUid($uid)) instanceof BaseSession){
				$sessions[$uid] = $session;
			}
		}
		return $sessions;
	}
	/**
	 * @param string $languageCode
	 */
	public function getPrefix(string $languageCode){
		$this->getPlugin()->getResourceManager()->getMessage($languageCode, 'commands.party.generalMessages.prefix');
	}
	/**
	 * @param string $message
	 * @param bool $local
	 */
	public function broadcast(string $message, bool $local = false){
		$sent = 0;
		if($local){
			foreach($this->getPartyPlayers() as $partyPlayer){
				if(($session = $this->plugin->getAuthenticatedSessionByUid($partyPlayer->getUid())) instanceof BaseSession and $partyPlayer->getStatus() === Constants::PARTY_STATUS_ACCEPTED){
					$session->sendMessage($this->getPrefix($session->getChatLanguageCodeString()) . " " . $message);
					++$sent;
				}
			}
			return $sent;
		}else{
			$sent = $this->broadcast($message, true);
			if($sent !== count($this->partyPlayersByName)){
				$this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_PARTY_BROADCAST, ['leaderUid' => $this->getLeaderUid(), 'message' => $message]);
			}
			return $sent;
		}
	}
	/**
	 * @param string $key
	 * @param array $search
	 * @param array $replace
	 * @param bool $local
	 * @return int
	 */
	public function broadcastPresetMessage(string $key, array $search = [], array $replace = [], bool $local = false){
		$sent = 0;
		if($local){
			foreach($this->getPartyPlayers() as $partyPlayer){
				if(($session = $this->plugin->getAuthenticatedSessionByUid($partyPlayer->getUid())) instanceof BaseSession and $partyPlayer->getStatus() === Constants::PARTY_STATUS_ACCEPTED){
					$session->sendMessage($this->getPrefix($session->getServerLanguageCodeString()) . " " . $session->getPresetMessage($key, $search, $replace));
					++$sent;
				}
			}
			return $sent;
		}else{
			$sent = $this->broadcastPresetMessage($key, $search, $replace, true);
			if($sent !== count($this->partyPlayersByName)){
				$this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_PARTY_BROADCAST_PRESET_MESSAGE, ['leaderUid' => $this->getLeaderUid(), 'key' => $key, 'search' => $search, 'replace' => $replace]);
			}
			return $sent;
		}
	}
	/**
	 * @param BaseSession $kicker
	 * @param int $kickedUid
	 */
	public function kick(BaseSession $kicker, int $kickedUid){
		$kicker->sendMessage($kicker->getPresetMessage('commands.party.arguments.kick.messages.user', ['%user%'], [$this->getPartyPlayerByUid($kickedUid)->getName()]));
		self::routerKick($this, $kicker->getUid(), $kickedUid);
		new DeletePartyPlayerQuery($this->getPlugin(), function($result, $rows, $error){}, $this->getLeaderUid(), $kickedUid);
		$this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_PARTY_KICK, ['leaderUid' => $this->getLeaderUid(), 'kickedUid' => $kickedUid, 'kickerUid' => $kicker->getUid()]);
	}
	/**
	 * @param int $kickerUid
	 * @param int $kickedUid
	 */
	public static function routerKick(Party $party, int $kickerUid, int $kickedUid){
		$kickedName = $party->getPartyPlayerByUid($kickedUid)->getName();
		$party->removePartyPlayerByUid($kickedUid);
		if(($session = $party->getPlugin()->findSessionByUid($kickedUid)) instanceof BaseSession){
			$session->addLoginMessage($session->getPresetMessage('commands.party.arguments.kick.messages.kicked'));
			$session->setParty(null);
			$session->setPartyPlayer(null);
			$party->checkRemove();
		}
		$party->broadcastPresetMessage('commands.party.arguments.kick.messages.broadcast', ['%user%'], [$kickedName], true);
	}
	/**
	 * @param BaseSession $inviter
	 * @param int $invitedUid
	 * @param string $invitedName
	 */
	public function invite(BaseSession $inviter, int $invitedUid, string $invitedName){
		new CreatePartyPlayerQuery($this->getPlugin(), function($result, $rows, $error)use($inviter, $invitedName, $invitedUid){
			if(strpos($error, 'Duplicate entry') !== false){
				$inviter->sendPresetMessage('commands.party.arguments.invite.messages.alreadyInvited', ['%user%'], [$invitedName]);
				return;
			}
			$inviter->sendPresetMessage('commands.party.arguments.invite.messages.success', ['%user%'], [$invitedName]);
			$this->addPartyPlayer($partyPlayer = new PartyPlayer($this->getPlugin(), $this->getLeaderUid(), $inviter->getName(), $invitedUid, $invitedName, Constants::PARTY_STATUS_REQUESTED));
			if(($session = $this->getPlugin()->findSessionByUid($invitedUid)) instanceof BaseSession){
				$session->addPartyPlayerInvite($partyPlayer);
				$session->addLoginMessage($session->getPresetMessage('commands.party.arguments.invite.messages.success', ['%user%'], [$inviter->getName()]));
			}else{
				$this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_PARTY_INVITE, ['leaderUid' => $this->getLeaderUid(), 'inviterUid' => $inviter->getUid(), 'inviterName' => $inviter->getName(), 'invitedUid' => $invitedUid, 'invitedName' => $invitedName, 'inviteDuration' => -1]);
			}
		}, $this->getLeaderUid(), $invitedUid, Constants::PARTY_STATUS_REQUESTED);
	}
	/**
	 * @param BaseSession $session
	 */
	public function leave(BaseSession $session){
		$session->setParty(null);
		$session->setPartyPlayer(null);
		if($session->getCurrentChatRoom() === $this->getChatRoom()){
			$session->resetCurrentChatRoom();
		}
		$session->removeChatRoom($this->getChatRoom());
		$this->checkRemove();
		$session->sendPresetMessage('commands.party.arguments.leave.messages.success');
		$this->plugin->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_PARTY_LEAVE, ['leaderUid' => $this->getLeaderUid(), 'uid' => $session->getUid()]);
		new DeletePartyPlayerQuery($this->plugin, function($result, $rows, $error){}, $this->getLeaderUid(), $session->getUid());
		self::routerLeave($this, $session->getUid());
	}
	/**
	 * @param Party $party
	 * @param int $uid
	 */
	public static function routerLeave(Party $party, int $uid){
		$party->broadcastPresetMessage('commands.party.arguments.leave.messages.broadcastMessage', ['%player%'], [$party->getPartyPlayerByUid($uid)->getName()], true);
		$party->removePartyPlayerByUid($uid);
	}
	/**
	 * @param Party $team
	 * @param int $inviterUid
	 * @param int $invitedUid
	 * @param int $invitedName
	 */
	public static function routerInvite(Party $party, int $inviterUid, int $invitedUid, string $invitedName){
		$party->addPartyPlayer($partyPlayer = new PartyPlayer($party->getPlugin(), $inviterUid, $inviterPartyPlayerName = $party->getPartyPlayerByUid($party->getLeaderUid())->getName(), $invitedUid, $invitedName, Constants::PARTY_STATUS_REQUESTED));
		if(($invitedSession = $party->getPlugin()->findSessionByUid($invitedUid)) instanceof BaseSession){
			$invitedSession->addPartyPlayerInvite($partyPlayer);
			$invitedSession->addLoginMessage($invitedSession->getPresetMessage('commands.party.arguments.invite.messages.success', ['%user%'], [$inviterPartyPlayerName]));
		}
	}
	/**
	 * @param BaseSession $disbander
	 * @param $sync bool
	 */
	public function disband(BaseSession $disbander){
		self::routerDisband($this, $disbander->getUid());
		new DeletePartyPlayersQuery($this->getPlugin(), function($result, $rows, $error){}, $this->getLeaderUid());
		$this->plugin->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_PARTY_DISBAND, ['leaderUid' => $this->getLeaderUid(), 'disbanderUid' => $disbander->getUid()]);
	}
	/**
	 * @param Party $party
	 * @param int $disbanderUid
	 */
	public static function routerDisband(Party $party, int $disbanderUid){
		$party->getPlugin()->getServer()->getPluginManager()->callEvent(new PartyRemoveEvent($party->getPlugin(), $party));
		$party->broadcastPresetMessage('commands.party.arguments.disband.messages.broadcast', ['%user%'], [$party->getPartyPlayerByUid($disbanderUid)->getName()], true);
		$party->getPlugin()->removeParty($party);
		foreach($party->getPartyPlayers() as $partyPlayer){
			if(($session = $party->getPlugin()->findSessionByUid($partyPlayer->getUid())) instanceof BaseSession){
				if($partyPlayer->getStatus() === Constants::PARTY_STATUS_ACCEPTED){
					$session->setParty(null);
					$session->setPartyPlayer(null);
				}else{
					$session->removePartyPlayerInvite($partyPlayer);
				}
			}
		}
	}
}
