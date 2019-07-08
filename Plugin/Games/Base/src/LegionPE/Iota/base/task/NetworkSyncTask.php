<?php

namespace LegionPE\Iota\base\task;

use LegionPE\Iota\base\AdminLog;
use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\BaseSession;
use LegionPE\Iota\base\ChatRoom;
use LegionPE\Iota\base\command\session\PunishCommand;
use LegionPE\Iota\base\Constants;
use LegionPE\Iota\base\FriendRelationship;
use LegionPE\Iota\base\party\Party;
use LegionPE\Iota\base\team\Team;
use LegionPE\Iota\base\team\TeamPlayer;
use pocketmine\scheduler\PluginTask;

class NetworkSyncTask extends PluginTask{
	public function onRun(int $currentTick){
		/** @var \LegionPE\Iota\base\BasePlugin $plugin */
		if(($plugin = $this->getOwner()) instanceof BasePlugin){
			while(($message = $plugin->getNetworkSyncThread()->getNextReceivedMessage())){
				$message = json_decode($message, true);
				if(isset($message['event'])){
					switch($message['event']){
						case Constants::ROUTER_EVENT_TEAM_INVITE:
							if(($team = $plugin->getTeam($message['id'])) instanceof Team){
								Team::routerInvite($team, $message['inviterUid'], $message['invitedUid'], $message['invitedName']);
							}
							break;
						case Constants::ROUTER_EVENT_TEAM_LEAVE:
							if(($team = $plugin->getTeam($message['id'])) instanceof Team){
								if(($teamPlayer = $team->getTeamPlayerByUid($message['uid'])) instanceof TeamPlayer){
									Team::routerLeave($team, $message['uid']);
								}
							}
							break;
						case Constants::ROUTER_EVENT_TEAM_KICK:
							if(($team = $plugin->getTeam($message['id'])) instanceof Team){
								if(($teamPlayer = $team->getTeamPlayerByUid($message['kickedUid'])) instanceof TeamPlayer){
									Team::routerKick($team, $message['kickerUid'], $message['kickedUid']);
								}
							}
							break;
						case Constants::ROUTER_EVENT_TEAM_PROMOTE:
							if(($team = $plugin->getTeam($message['id'])) instanceof Team){
								Team::routerPromote($team, $message['promoterUid'], $message['promotedUid']);
							}
							break;
						case Constants::ROUTER_EVENT_TEAM_DEMOTE:
							if(($team = $plugin->getTeam($message['id'])) instanceof Team){
								Team::routerDemote($team, $message['demoterUid'], $message['demotedUid']);
							}
							break;
						case Constants::ROUTER_EVENT_TEAM_ACCEPT_INVITE:
							if(($team = $plugin->getTeam($message['id'])) instanceof Team){
								if(($teamPlayer = $team->getTeamPlayerByUid($message['uid'])) instanceof TeamPlayer){
									TeamPlayer::routerAcceptInvite($teamPlayer);
								}
							}
							break;
						case Constants::ROUTER_EVENT_TEAM_DENY_INVITE:
							if(($team = $plugin->getTeam($message['id'])) instanceof Team){
								if(($teamPlayer = $team->getTeamPlayerByUid($message['uid'])) instanceof TeamPlayer){
									TeamPlayer::routerDenyInvite($teamPlayer);
								}
							}
							break;
						case Constants::ROUTER_EVENT_TEAM_DISBAND:
							if(($team = $plugin->getTeam($message['id'])) instanceof Team){
								Team::routerDisband($team, $message['disbanderUid']);
							}
							break;
						case Constants::ROUTER_EVENT_TEAM_BROADCAST:
							if(($team = $plugin->getTeam($message['id'])) instanceof Team){
								$team->broadcast($message['key'], true);
							}
							break;
						case Constants::ROUTER_EVENT_TEAM_BROADCAST_PRESET_MESSAGE:
							if(($team = $plugin->getTeam($message['id'])) instanceof Team){
								$team->broadcastPresetMessage($message['key'], $message['search'], $message['replace'], true);
							}
							break;

						case Constants::ROUTER_EVENT_FRIEND_ACCEPT_REQUEST:
							if(($session = $plugin->findSessionByUid($message['friendUid'])) instanceof BaseSession){
								if(($friendRelationship = $session->getFriendRelationshipByUid($message['friendUid'])) instanceof FriendRelationship){
									FriendRelationship::routerAccept($friendRelationship);
								}
							}
							break;
						case Constants::ROUTER_EVENT_FRIEND_DENY_REQUEST:
							if(($session = $plugin->findSessionByUid($message['friend'])) instanceof BaseSession){
								if(($friendRelationship = $session->getFriendRelationshipByUid($message['friendUid'])) instanceof FriendRelationship){
									FriendRelationship::routerDeny($friendRelationship);
								}
							}
							break;
						case Constants::ROUTER_EVENT_FRIEND_REMOVE:
							if(($session = $plugin->findSessionByUid($message['requestedUid'])) instanceof BaseSession){
								if(($friendRelationship = $session->getFriendRelationshipByUid($message['requestedUid'])) instanceof FriendRelationship){
									FriendRelationship::routerRemove($friendRelationship);
									break;
								}
							}
							if(($session = $plugin->findSessionByUid($message['requesterUid'])) instanceof BaseSession){
								if(($friendRelationship = $session->getFriendRelationshipByUid($message['requesterUid'])) instanceof FriendRelationship){
									FriendRelationship::routerRemove($friendRelationship);
								}
							}
							break;
						case Constants::ROUTER_EVENT_FRIEND_ONLINE_CHANGE:
							if(($session = $plugin->findSessionByUid($message['friendUid'])) instanceof BaseSession){
								if(($friendRelationship = $session->getFriendRelationshipByUid($message['uid'])) instanceof FriendRelationship){
									if($message['uid'] === $friendRelationship->getRequesterUid()){
										$friendRelationship->setRequesterOnline($message['status'], false);
									}
								}
							}
							break;
						case Constants::ROUTER_EVENT_PARTY_ACCEPT_INVITE:
							if(($party = $plugin->getParty($message['leaderUid'])) instanceof Party){
								if(($partyPlayer = $party->getPartyPlayerByUid($message['uid'])) instanceof PartyPlayer){
									PartyPlayer::routerAcceptInvite($partyPlayer);
								}
							}
							break;
						case Constants::ROUTER_EVENT_PARTY_DENY_INVITE:
							if(($party = $plugin->getParty($message['leaderUid'])) instanceof Party){
								if(($partyPlayer = $party->getPartyPlayerByUid($message['uid'])) instanceof PartyPlayer){
									PartyPlayer::routerDenyInvite($partyPlayer);
								}
							}
							break;
						case Constants::ROUTER_EVENT_PARTY_INVITE:
							if(($party = $plugin->getParty($message['leaderUid'])) instanceof Party){
								Party::routerInvite($party, $message['inviterUid'], $message['invitedUid'], $message['invitedName']);
							}
							break;
						case Constants::ROUTER_EVENT_PARTY_KICK:
							if(($party = $plugin->getParty($message['leaderUid'])) instanceof Party){
								Party::routerKick($party, $message['kickerUid'], $message['kickedUid']);
							}
							break;
						case Constants::ROUTER_EVENT_PARTY_LEAVE:
							if(($party = $plugin->getParty($message['leaderUid'])) instanceof Party){
								if(($partyPlayer = $party->getPartyPlayerByUid($message['uid'])) instanceof PartyPlayer){
									Party::routerLeave($party, $message['uid']);
								}
							}
							break;
						case Constants::ROUTER_EVENT_CHAT_ROOM_BROADCAST:
							if(($chatRoom = $plugin->getChatRoom($message['key'])) instanceof ChatRoom){
								ChatRoom::routerBroadcast($chatRoom, $message['message']);
							}
							break;
						case Constants::ROUTER_EVENT_CHAT_ROOM_BROADCAST_PRESET_MESSAGE:
							if(($chatRoom = $plugin->getChatRoom($message['key'])) instanceof ChatRoom){
								ChatRoom::routerBroadcastPresetMessage($chatRoom, $message['messageIdentifier'], $message['search'], $message['replace']);
							}
							break;
						case Constants::ROUTER_EVENT_ADMIN_LOG_CREATE:
							if(($session = $plugin->findSessionByUid($message['uid'])) instanceof BaseSession){
								$session->addAdminLog(new AdminLog($message['id'], $message['uid'], $message['uuid'], $message['ip'], $message['creationTime'], $message['type'], $message['message'], $message['fromUid'], $message['fromIp'], $message['duration'], $message['ipSensitive'], $message['uuidSensitive']));
							}
							break;
						case Constants::ROUTER_EVENT_ADMIN_LOG_REMOVE:
							if(($session = $plugin->findSessionByUid($message['uid'])) instanceof BaseSession){
								$session->removeAdminLogById($message['id']);
							}
							break;
						case Constants::ROUTER_EVENT_PUNISH:
							if(($session = $plugin->findSessionByUid($message['punishedUid'])) instanceof BaseSession){
								PunishCommand::routerPunish($message['senderUid'], $message['senderIp'], $session, $message['reason'], $message['extra']);
							}
							break;
					}
				}
			}
		}
	}
}
