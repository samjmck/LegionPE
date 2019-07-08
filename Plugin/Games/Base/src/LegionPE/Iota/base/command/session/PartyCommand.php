<?php

namespace LegionPE\Iota\base\command\session;

use LegionPE\Iota\base\BaseSession;
use LegionPE\Iota\base\ChatRoom;
use LegionPE\Iota\base\command\BaseCommand;
use LegionPE\Iota\base\Constants;
use LegionPE\Iota\base\event\party\PartyCreateEvent;
use LegionPE\Iota\base\party\Party;
use LegionPE\Iota\base\party\PartyPlayer;
use LegionPE\Iota\base\Utils;

class PartyCommand extends BaseCommand{
	public function run(BaseSession $session, array $args){
		if(count($args) === 0){
			return $this->getCustomUsage($session->getServerLanguageCodeString());
		}
		if(($argument = $this->getArgument($session->getServerLanguageCodeString(), $args[0])) !== false){
			switch($argument){
				case 'create':
					if(!$this->hasArgumentPermission($session, $argument)){
						return $session->getPresetMessage('commandErrors.upgradeRank');
					}
					if(count($presetMessageErrors = $session->getPlugin()->createParty(function($result, $rows, $error)use($args, $session){
						$session->setParty($party = new Party($session->getPlugin(), $session->getUid(), $chatRoom = new ChatRoom($this->getPlugin(), $this->getPlugin()->getChatRoomPrefix() . 'PARTY-LEADER-UID-' . $session->getUid())));
						$this->getPlugin()->getServer()->getPluginManager()->callEvent(new PartyCreateEvent($this->getPlugin(), $party));
						$this->getPlugin()->addChatRoom($chatRoom);
						$session->addChatRoom($chatRoom);
						$party->addPartyPlayer($partyPlayer = new PartyPlayer($session->getPlugin(), $session->getUid(), $session->getName(), $session->getUid(), $session->getName(), Constants::PARTY_STATUS_ACCEPTED));
						$session->setPartyPlayer($partyPlayer);
						$session->sendPresetMessage('commands.team.arguments.create.success');
						$this->getPlugin()->addParty($party);
					}, $session))){
						foreach($presetMessageErrors as $error){
							$session->sendPresetMessageError($error);
						}
					}
					break;
				case 'invite':
					if(!isset($args[1])){
						return $session->getPresetMessage('commands.party.arguments.invite.usage');
					}
					if(!(($party = $session->getParty()) instanceof Party)){
						return $session->getPresetMessage('commands.party.generalMessages.notInParty');
					}
					if(Utils::getSessionMaxPartyPlayers($session) >= count($party->getPartyPlayers())){
						return $session->getPresetMessage('commands.party.arguments.invite.messages.full');
					}
					if($party->getLeaderUid() !== $session->getUid()){
						return $session->getPresetMessage('commands.party.generalMessages.noPermission');
					}
					if(($pp = $party->getPartyPlayerByName($args[1])) instanceof PartyPlayer){
						if($pp->getStatus() === Constants::PARTY_STATUS_ACCEPTED){
							return $session->getPresetMessage('commands.party.arguments.invite.messages.alreadyInParty');
						}else{
							return $session->getPresetMessage('commands.party.arguments.invite.messages.alreadyInvited');
						}
					}
					new NameToUidQuery($this->plugin, function($result, $rows, $error)use($session, $party, $args){
						if($rows === 1){
							if($result['status']){
								$party->invite($session, $result['uid'], $result['name']);
								return;
							}
							$session->sendPresetMessage('commandErrors.userOffline', ['%user%'], [$result['name']]);
							return;
						}
						$session->sendPresetMessage('commandErrors.userDoesntExist', ['%user%'], [$args[1]]);
					}, $args[1]);
					break;
				case 'disband':
					if(($party = $session->getParty()) instanceof Party){
						if($party->getLeaderUid() !== $session->getUid()){
							return $session->getPresetMessage('commands.party.generalMessages.noPermission');
						}
						$party->disband($session);
					}else{
						return $session->getPresetMessage('commands.party.generalMessages.notInParty');
					}
					break;
				case 'summon':

					break;
				case 'accept':
					if(!isset($args[1])){
						return $session->getPresetMessage('commands.party.arguments.accept.usage');
					}
					if(!(($partyPlayer = $session->getPartyPlayerInvitebyLeaderName($args[1])) instanceof PartyPlayer)){
						return $session->getPresetMessage('commands.party.generalMessages.noInvite', ['%user%'], [$args[1]]);
					}
					if(!($session->getParty() instanceof Party)){
						$partyPlayer->acceptInvite($session);
					}else{
						return $session->getPresetMessage('commands.party.generalMessages.alreadyInParty');
					}
					break;
				case 'deny':
					if(!isset($args[1])){
						return $session->getPresetMessage('commands.party.arguments.deny.usage');
					}
					if(!(($partyPlayer = $session->getPartyPlayerInvitebyLeaderName($args[1])) instanceof PartyPlayer)){
						$partyPlayer->denyInvite($session);
					}else{
						return $session->getPresetMessage('commands.party.generalMessages.noInvite', ['%user%'], [$args[1]]);
					}
					break;
				case 'kick':
					if(($party = $session->getParty()) instanceof Party){
						if($party->getLeaderUid() !== $session->getUid()){
							return $session->getPresetMessage('commands.party.generalMessages.noPermission');
						}
						if(!isset($args[1])){
							return $session->getPresetMessage('commands.party.arguments.kick.usage');
						}
						if(($partyPlayer = $party->getPartyPlayerByName($args[1])) instanceof PartyPlayer){
							$party->kick($session, $partyPlayer->getUid());
						}
						return $session->getPresetMessage('commands.party.arguments.kick.userNotInParty');
					}
					return $session->getPresetMessage('commands.party.generalMessages.notInParty');
					break;
				case 'list':
					if(!(($party = $session->getParty()) instanceof Party)){
						return $session->getPresetMessage('commands.party.generalMessages.notInParty');
					}
					$names = [];
					foreach($party->getPartyPlayersByName() as $name => $partyPlayer){
						if($partyPlayer->getStatus() === Constants::PARTY_STATUS_ACCEPTED){
							$names[] = $name;
						}
					}
					return $session->getPresetMessage('commands.party.arguments.list.success', ['%members%'], [implode('§9, §r', $names)]);
					break;
				case 'leave':
					if(!(($party = $session->getParty()) instanceof Party)){
						return $session->getPresetMessage('commands.party.generalMessages.notInParty');
					}
					if($party->getLeaderUid() !== $session->getUid()){
						$party->leave($session);
					}else{
						return $session->getPresetMessage('commands.party.arguments.leave.messages.owner');
					}
					break;
			}
		}
	}
}
