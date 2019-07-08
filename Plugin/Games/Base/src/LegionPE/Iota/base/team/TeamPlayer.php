<?php

namespace LegionPE\Iota\base\team;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\BaseSession;
use LegionPE\Iota\base\ChatRoom;
use LegionPE\Iota\base\Constants;
use LegionPE\Iota\base\query\Query;
use LegionPE\Iota\base\query\team\DeleteTeamPlayerQuery;
use LegionPE\Iota\base\query\team\GetTeamByIdQuery;
use LegionPE\Iota\base\query\team\SaveTeamPlayersQuery;

class TeamPlayer{
	/** @var BasePlugin */
	private $plugin;
	/** @var int */
	private $id;
	/** @var string */
	private $teamName;
	/** @var int */
	private $status;
	/** @var int */
	private $creationTime;
	/** @var int */
	private $inviteDuration;
	/** @var int */
	private $acceptedTime;
	/** @var int */
	private $inviterUid;
	/** @var int */
	private $invitedUid;
	/** @var string */
	private $name;
	/** @var int */
	private $online;
	/** @var int */
	private $rank;
	/**
	 * @param BasePlugin $plugin
	 * @param int $id
	 * @param string $teamName
	 * @param int $status
	 * @param int $creationTime
	 * @param int $inviteDuration
	 * @param int $acceptedTime
	 * @param int $inviterUid
	 * @param int $invitedUid
	 * @param string $name
	 * @param int $online
	 * @param int $rank
	 */
	public function __construct(BasePlugin $plugin, int $id, string $teamName, int $status, int $creationTime, int $inviteDuration, int $acceptedTime, int $inviterUid, int $invitedUid, string $name, int $online, int $rank){
		$this->plugin = $plugin;
		$this->id = $id;
		$this->teamName = $teamName;
		$this->status = $status;
		$this->creationTime = $creationTime;
		$this->inviteDuration = $inviteDuration;
		$this->acceptedTime = $acceptedTime;
		$this->inviterUid = $inviterUid;
		$this->invitedUid = $invitedUid;
		$this->name = $name;
		$this->online = (int) $online;
		$this->rank = $rank;
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
	public function getTeamName(): string{
		return $this->teamName;
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
	public function getStatus(): int{
		return $this->status;
	}
	/**
	 * @param bool|int $status
	 */
	public function setStatus($status){
		$this->status = (int) $status;
	}
	/**
	 * @return int
	 */
	public function getCreationTime(): int{
		return $this->creationTime;
	}
	/**
	 * @return int
	 */
	public function getAcceptedTime(): int{
		return $this->acceptedTime;
	}
	/**
	 * @param int $time
	 */
	public function setAcceptedTime(int $time){
		$this->acceptedTime = $time;
	}
	/**
	 * @return int
	 */
	public function getInviterUid(): int{
		return $this->inviterUid;
	}
	/**
	 * @return int
	 */
	public function getUid(): int{
		return $this->invitedUid;
	}
	/**
	 * @return string
	 */
	public function getName(): string{
		return $this->name;
	}
	/**
	 * @return bool
	 */
	public function isOnline(): int{
		return (bool) $this->online;
	}
	/**
	 * @param bool|int $online
	 */
	public function setOnline($online){
		$this->online = (int) $online;
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
	 * @param BaseSession $accepter
	 */
	public function acceptInvite(BaseSession $accepter){
		self::routerAcceptInvite($this);
		if(($team = $this->getPlugin()->getTeam($this->getId())) instanceof Team){
			$accepter->setTeam($team);
			$accepter->setTeamPlayer($this);
			$accepter->removeTeamPlayerInvite($this);
			$accepter->sendPresetMessage('commands.team.arguments.accept.messages.success', ['%team%'], [$this->getTeamName()]);
		}else{
			new GetTeamByIdQuery($this->getPlugin(), function($result, $rows, $error)use($accepter){
				if($rows >= 1){
					$accepter->sendPresetMessage('commands.team.arguments.accept.messages.success', ['%team%'], [$this->getTeamName()]);
					$teamData = $result;
					if(($team = $this->getPlugin()->getTeam($teamData['id'])) instanceof Team){
						$accepter->setTeam($team);
						$accepter->setTeamPlayer($this);
						$accepter->addChatRoom($team->getChatRoom());
						$accepter->removeTeamPlayerInvite($this);
						return;
					}
					$accepter->setTeam($team = new Team($this->getPlugin(), $teamData['id'], $teamData['name'], $teamData['acronym'], $teamData['leader_uid'], $teamData['creation_time'], $chatRoom = new ChatRoom($this->getPlugin(), $this->getPlugin()->getChatRoomPrefix() . 'TEAM-ID-' . $teamData['id'], false)));
					$accepter->addChatRoom($chatRoom);
					$this->getPlugin()->addChatRoom($chatRoom);
					foreach($teamData['players'] as $players){
						if($players['inviteduid'] === $this->getUid()){
							$team->addTeamPlayer($this);
							continue;
						}
						if($players['status'] === Constants::TEAM_STATUS_REQUESTED){
							if(($ses = $this->getPlugin()->findSessionByUid($players['invited_uid'])) instanceof BaseSession){
								if(($teamPlayer = $ses->getTeamPlayerInvite($players['id'])) instanceof TeamPlayer){
									$team->addTeamPlayer($teamPlayer);
									continue;
								}
							}
						}
						$team->addTeamPlayer(new TeamPlayer($this->getPlugin(), $players['id'], $team->getName(), $players['status'], $players['creation_time'], $players['invite_duration'], $players['accepted_time'], $players['inviter_uid'], $players['invited_uid'], $players['name'], $players['online'], $players['rank']));
					}
					$accepter->setTeamPlayer($this);
					$this->getPlugin()->addTeam($team);
					$accepter->removeTeamPlayerInvite($this);
				}else{
					$accepter->sendPresetMessage('commands.team.arguments.accept.messages.teamDoesntExist');
					$accepter->removeTeamPlayerInvite($this);
				}
			}, $this->getId());
		}
		$this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_TEAM_ACCEPT_INVITE, ['id' => $this->getId(), 'uid' => $this->getUid()]);
		$this->saveData();
	}
	/**
	 * @param TeamPlayer $teamPlayer
	 */
	public static function routerAcceptInvite(TeamPlayer $teamPlayer){
		$teamPlayer->setAcceptedTime(time());
		$teamPlayer->setOnline(true);
		$teamPlayer->setStatus(Constants::TEAM_STATUS_ACCEPTED);
		if(($team = $teamPlayer->getPlugin()->getTeam($teamPlayer->getId())) instanceof Team){
			$team->broadcastPresetMessage('commands.team.arguments.accept.messages.broadcastMessage', ['%player%'], [$teamPlayer->getName()], true);
		}
	}
	/**
	 * @param BaseSession $denier
	 */
	public function denyInvite(BaseSession $denier){
		self::routerDenyInvite($this);
		$denier->sendPresetMessage('commands.team.arguments.deny.messages.success');
		$denier->removeTeamPlayerInvite($this);
		$this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_TEAM_DENY_INVITE, ['id' => $this->getId(), 'uid' => $this->getUid()]);
		new DeleteTeamPlayerQuery($this->getPlugin(), function($result, $rows, $error){}, $this->getId(), $this->getUid());
	}
	/**
	 * @param TeamPlayer $teamPlayer
	 */
	public static function routerDenyInvite(TeamPlayer $teamPlayer){
		if(($team = $teamPlayer->getPlugin()->getTeam($teamPlayer->getId())) instanceof Team){
			$team->removeTeamPlayer($teamPlayer);
		}
	}
	public function saveData(){
		new SaveTeamPlayersQuery($this->plugin, function($result, $rows, $error){}, [
			'columns' => [
				'rank' => Query::DATA_TYPE_NUMERIC,
				'status' => Query::DATA_TYPE_NUMERIC,
				'acceptedtime' => Query::DATA_TYPE_NUMERIC
			],
			'users' => [
				$this->getUid() => ['rank' => $this->getRank(), 'status' => $this->getStatus(), 'acceptedtime' => $this->getAcceptedTime()]
			]
		]);
	}
}
