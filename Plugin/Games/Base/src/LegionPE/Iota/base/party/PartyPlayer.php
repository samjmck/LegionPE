<?php

namespace LegionPE\Iota\base\party;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\BaseSession;
use LegionPE\Iota\base\ChatRoom;
use LegionPE\Iota\base\Constants;
use LegionPE\Iota\base\query\party\GetPartyPlayersByLeaderIdQuery;
use LegionPE\Iota\base\query\party\UpdatePartyPlayerStatusQuery;
use LegionPE\Iota\base\team\TeamPlayer;

class PartyPlayer{
	/** @var BasePlugin */
	private $plugin;
	/** @var int */
	private $leaderUid;
	/** @var string */
	private $leaderName;
	/** @var int */
	private $uid;
	/** @var string */
	private $name;
	/** @var int */
	private $status;
	/**
	 * @param BasePlugin $plugin
	 * @param int $leaderUid
	 * @param string $leaderName
	 * @param int $uid
	 * @param string $name
	 * @param int $status
	 */
	public function __construct(BasePlugin $plugin, int $leaderUid, string $leaderName, int $uid, string $name, int $status){
		$this->plugin = $plugin;
		$this->leaderUid = $leaderUid;
		$this->leaderName = $leaderName;
		$this->uid = $uid;
		$this->name = $name;
		$this->status = $status;
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
	 * @return string
	 */
	public function getLeaderName(): string{
		return $this->leaderName;
	}
	/**
	 * @return int
	 */
	public function getUid(): int{
		return $this->uid;
	}
	/**
	 * @return string
	 */
	public function getName(): string{
		return $this->name;
	}
	/**
	 * @param int $status
	 */
	public function setStatus(int $status){
		$this->status = $status;
	}
	/**
	 * @return int
	 */
	public function getStatus(): int{
		return $this->status;
	}
	/**
	 * @param BaseSession $accepter
	 */
	public function acceptInvite(BaseSession $accepter){
		self::routerAcceptInvite($this);
		if(($party = $this->getPlugin()->getParty($this->getLeaderUid())) instanceof Party){
			$accepter->setParty($party);
			$accepter->setPartyPlayer($this);
			$accepter->removePartyPlayerInvite($this);
			$accepter->sendPresetMessage('commands.party.arguments.accept.messages.success', ['%user%'], [$this->getLeaderName()]);
		}else{
			new GetPartyPlayersByLeaderIdQuery($this->getPlugin(), function($result, $rows, $error)use($accepter){
				if($rows >= 1){
					$accepter->sendPresetMessage('commands.party.arguments.accept.messages.success', ['%user%'], [$this->getLeaderName()]);
					if(($party = $this->getPlugin()->getParty($this->getLeaderUid())) instanceof Party){
						$accepter->setParty($party);
						$accepter->setPartyPlayer($this);
						$accepter->addChatRoom($party->getChatRoom());
						$accepter->removePartyPlayerInvite($this);
						return;
					}
					$accepter->setParty($party = new Party($this->getPlugin(), $this->getLeaderUid(), $chatRoom = new ChatRoom($this->getPlugin(), $this->getPlugin()->getChatRoomPrefix() . 'PARTY-LEADER-UID-' . $this->getLeaderUid())));
					$accepter->setPartyPlayer($this);
					$accepter->removePartyPlayerInvite($this);
					$accepter->addChatRoom($chatRoom);
					$this->getPlugin()->addChatRoom($chatRoom);
					$leaderName = null;
					foreach($result as $invite){
						if($invite['uid'] === $this->getLeaderUid()){
							$leaderName = $invite['name'];
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
						$party->addPartyPlayer(new PartyPlayer($this->getPlugin(), $this->getLeaderUid(), $leaderName, $invite['uid'], $invite['name'], $invite['status']));
					}
					$this->getPlugin()->addParty($party);
				}else{
					$accepter->sendPresetMessage('commands.party.arguments.accept.messages.partyDoesntExist');
					$accepter->removePartyPlayerInvite($this);
				}
			}, $this->getLeaderUid());
		}
		$this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_PARTY_ACCEPT_INVITE, ['leaderUid' => $this->getLeaderUid(), 'uid' => $this->getUid()]);
		new UpdatePartyPlayerStatusQuery($this->getPlugin(), function($result, $rows, $error){}, $this->getLeaderUid(), $this->getUid(), Constants::PARTY_STATUS_ACCEPTED);
	}
	/**
	 * @param PartyPlayer $partyPlayer
	 */
	public static function routerAcceptInvite(PartyPlayer $partyPlayer){
		if(($party = $partyPlayer->getPlugin()->getParty($partyPlayer->getLeaderUid())) instanceof Party){
			$party->broadcastPresetMessage('commands.party.arguments.accept.messages.broadcast.broadcast', ['%user%'], [$partyPlayer->getName()]);
		}
		$partyPlayer->setStatus(Constants::PARTY_STATUS_ACCEPTED);
	}
	/**
	 * @param BaseSession $denier
	 */
	public function denyInvite(BaseSession $denier){
		self::routerDenyInvite($this);
		$denier->sendPresetMessage('commands.party.arguments.deny.messages.success');
		$denier->removePartyPlayerInvite($this);
		$this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_PARTY_DENY_INVITE, ['leaderUid' => $this->getUid(), 'uid' => $this->getUid()]);
		new DeletePartyPlayerQuery($this->getPlugin(), function($result, $rows, $error){}, $this->getLeaderUid(), $this->getUid());
	}
	/**
	 * @param PartyPlayer $partyPlayer
	 */
	public static function routerDenyInvite(PartyPlayer $partyPlayer){
		if(($party = $partyPlayer->getPlugin()->getParty($partyPlayer->getLeaderUid())) instanceof Party){
			$party->removePartyPlayer($partyPlayer);
		}
	}
}
