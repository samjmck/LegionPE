<?php

namespace LegionPE\Iota\base;

use LegionPE\Iota\base\query\session\RemoveFriendQuery;
use LegionPE\Iota\base\query\session\UpdateFriendStatusQuery;

class FriendRelationship{
	/** @var BasePlugin */
	private $plugin;
	/** @var int */
	private $status;
	/** @var int */
	private $requesterUid, $requesterOnline, $requestedUid, $requestedOnline;
	/** @var string */
	private $requesterName, $requestedName;
	/**
	 * @param int $status
	 * @param int $requesterUid
	 * @param string $requesterName
	 * @param int $requesterOnline
	 * @param int $requestedUid
	 * @param string $requestedName
	 * @param int $requestedOnline
	 */
	public function __construct(BasePlugin $plugin, int $status, int $requesterUid, string $requesterName, int $requesterOnline, int $requestedUid, string $requestedName, int $requestedOnline){
		$this->plugin = $plugin;
		$this->status = $status;
		$this->requesterUid = $requesterUid;
		$this->requesterName = $requesterName;
		$this->requesterOnline = (int) $requesterOnline;
		$this->requestedUid = $requestedUid;
		$this->requestedName = $requestedName;
		$this->requestedOnline = (int) $requestedOnline;
	}
	/**
	 * @param int $status
	 * @param int $requesterUid
	 * @param string $requesterName
	 * @param int $requesterOnline
	 * @param int $requestedUid
	 * @param string $requestedName
	 * @param int $requestedOnline
	 * @param bool $sync
	 * @return FriendRelationship
	 */
	public static function createFriendRequest(BasePlugin $plugin, int $requesterUid, string $requesterName, int $requesterOnline, int $requestedUid, string $requestedName, int $requestedOnline, bool $sync = true): FriendRelationship{
		$friendRelationship = new self($plugin, Constants::FRIEND_STATUS_REQUESTED, $requesterUid, $requesterName, $requesterOnline, $requestedUid, $requestedName, $requestedOnline);
		if(($requesterSession = $plugin->findSessionByUid($requesterUid)) instanceof BaseSession){
			$requesterSession->addFriendRelationship($friendRelationship);
			$requesterSession->addLoginMessage($requesterSession->getPresetMessage('commands.friend.arguments.add.messages.success', ['%user%'], [$requestedName]));
		}
		if(($requestedSession = $plugin->findSessionByUid($requestedUid)) instanceof BaseSession){
			$requestedSession->addFriendRelationship($friendRelationship);
			$requestedSession->addLoginMessage($requesterSession->getPresetMessage('commands.friend.arguments.add.messages.receiver', ['%user%'], [$requestedName]));
		}
		if($sync){
			$plugin->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_FRIEND_REQUEST, ['requesterUid' => $requesterUid, 'requesterName' => $requesterName, 'requesterOnline' => $requesterOnline, 'requestedUid' => $requestedUid]);
			//new self($plugin, Constants::FRIEND_STATUS_REQUESTED, $requesterUid, $requesterName, $requesterOnline, $requestedUid, $requestedName, $requestedOnline);
		}
		return $friendRelationship;
	}
	/**
	 * @return int
	 */
	public function getStatus(): int{
		return $this->status;
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
	public function getRequesterUid(): int{
		return $this->requestedUid;
	}
	/**
	 * @return string
	 */
	public function getRequesterName(): string{
		return $this->requestedName;
	}
	/**
	 * @return bool
	 */
	public function getRequesterOnline(): bool{
		return (bool) $this->requesterOnline;
	}
	/**
	 * @return BasePlugin
	 */
	public function getPlugin(): BasePlugin{
		return $this->plugin;
	}
	/**
	 * @param bool $value
	 * @param bool $sync
	 */
	public function setRequesterOnline($value, bool $sync = true){
		$this->requesterOnline = (int) $value;
		if($sync){
			$this->plugin->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_FRIEND_ONLINE_CHANGE, ['uid' => $this->getRequesterUid(), 'friendUid' => $this->getRequestedUid(), 'status' => (int) $value]);
		}
	}
	/**
	 * @return int
	 */
	public function getRequestedUid(): int{
		return $this->requestedUid;
	}
	/**
	 * @return string
	 */
	public function getRequestedName(): string{
		return $this->requestedName;
	}
	/**
	 * @return bool
	 */
	public function getRequestedOnline(): bool{
		return (bool) $this->requestedOnline;
	}
	/**
	 * @param bool $value
	 * @param bool $sync
	 */
	public function setRequestedOnline(bool $value, bool $sync = true){
		$this->requestedOnline = (int) $value;
		if($sync){
			$this->plugin->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_FRIEND_ONLINE_CHANGE, ['uid' => $this->getRequestedUid(), 'friendUid' => $this->getRequesterUid(), 'status' => (int) $value]);
		}
	}
	/**
	 * @param BaseSession $accepter
	 */
	public function accept(BaseSession $accepter){
		if($accepter->getUid() === $this->getRequestedUid()){
			switch($this->getStatus()){
				case Constants::FRIEND_STATUS_REQUESTED:
				case Constants::FRIEND_STATUS_DENIED:
					self::routerAccept($this);
					$this->updateStatus();
					$this->plugin->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_FRIEND_ACCEPT_REQUEST, ['uid' => $accepter->getUid(), 'friendUid' => $this->getRequesterUid()]);
					break;
				case Constants::FRIEND_STATUS_ACCEPTED:
					$accepter->sendPresetMessage('commands.friend.generalMessages.alreadyFriend', ['%user%'], [$this->getRequesterName()]);
					break;
			}
		}else{
			switch($this->getStatus()){
				case Constants::FRIEND_STATUS_DENIED:
				case Constants::FRIEND_STATUS_ACCEPTED:
					$accepter->sendPresetMessage('commands.friend.generalMessages.alreadyFriend', ['%user%'], [$this->getRequesterName()]);
					break;
				case Constants::FRIEND_STATUS_REQUESTED:
					$accepter->sendPresetMessage('commands.friend.messages.awaitingResponse');
					break;
			}
		}
	}
	/**
	 * @param FriendRelationship $friendRelationship
	 */
	public static function routerAccept(FriendRelationship $friendRelationship){
		$friendRelationship->setStatus(Constants::FRIEND_STATUS_ACCEPTED);
		if(($session = $friendRelationship->plugin->findSessionByUid($friendRelationship->getRequesterUid())) instanceof BaseSession){
			$session->addLoginMessage($session->getPresetMessage('commands.friend.arguments.accept.messages.friend', ['%user%'], [$friendRelationship->getRequestedName()]));
		}
	}
	public function remove(){
		self::routerRemove($this);
		$this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_FRIEND_REMOVE, ['requesterUid' => $this->getRequesterUid(), 'requestedUid' => $this->getRequestedUid()]);
		new RemoveFriendQuery($this->plugin, function($result){}, $this->getRequesterUid(), $this->getRequestedUid());
	}
	/**
	 * @param FriendRelationship $friendRelationship
	 */
	public static function routerRemove(FriendRelationship $friendRelationship){
		if(($requestedSession = $friendRelationship->plugin->findSessionByUid($friendRelationship->getRequestedUid())) instanceof BaseSession){
			if($requestedSession->getFriendRelationshipByUid($friendRelationship->getRequesterUid()) instanceof FriendRelationship){
				$requestedSession->removeFriendRelationship($friendRelationship);
			}
		}
		if(($requesterSession = $friendRelationship->getPlugin()->findSessionByUid($friendRelationship->getRequestedUid())) instanceof BaseSession){
			if($requesterSession->getFriendRelationshipByUid($friendRelationship->getRequestedUid()) instanceof FriendRelationship){
				$requesterSession->removeFriendRelationship($friendRelationship);
			}
		}
	}
	/**
	 * @param BaseSession $denier
	 */
	public function deny(BaseSession $denier){
		if($denier->getUid() === $this->getRequestedUid()){
			switch($this->getStatus()){
				case Constants::FRIEND_STATUS_REQUESTED:
				case Constants::FRIEND_STATUS_DENIED:
					$this->remove();
					$this->deny($denier);
					$denier->sendPresetMessage('commands.friend.arguments.deny.messages.success');
					$this->plugin->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_FRIEND_DENY_REQUEST, ['uid' => $denier->getUid(), 'friendUid' => $this->getRequesterUid()]);
					break;
				case Constants::FRIEND_STATUS_ACCEPTED:
					$denier->sendPresetMessage('commands.friend.arguments.deny.messages.useRemoveCommand', ['%user%'], [$this->getRequesterName()]);
					break;
			}
		}else{
			switch($this->getStatus()){
				case Constants::FRIEND_STATUS_DENIED:

					break;
				case Constants::FRIEND_STATUS_ACCEPTED:
					$denier->sendPresetMessage('commands.friend.generalMessages.alreadyFriend', ['%user%'], [$this->getRequestedName()]);
					break;
				case Constants::FRIEND_STATUS_REQUESTED:
					$denier->sendPresetMessage('commands.friend.messages.awaitingResponse');
					break;
			}
		}
	}
	/**
	 * @param FriendRelationship $friendRelationship
	 */
	public static function routerDeny(FriendRelationship $friendRelationship){
		$friendRelationship->remove();
		$friendRelationship->setStatus(Constants::FRIEND_STATUS_DENIED);
		if(($session = $friendRelationship->getPlugin()->findSessionByUid($friendRelationship->getRequesterUid())) instanceof BaseSession){
			$session->addLoginMessage($session->getPresetMessage('commands.friend.arguments.deny.messages.friend', ['%user%'], [$friendRelationship->getRequestedName()]));
		}
	}
	public function updateStatus(){
		new UpdateFriendStatusQuery($this->plugin, function ($data){}, $this->getRequesterUid(), $this->getRequestedUid(), $this->getStatus(), Constants::FRIEND_STATUS_ACCEPTED);
	}
}
