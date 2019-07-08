<?php

namespace LegionPE\Iota\base\command\session;

use LegionPE\Iota\base\BaseSession;
use LegionPE\Iota\base\ChatRoom;
use LegionPE\Iota\base\command\BaseCommand;
use LegionPE\Iota\base\Constants;
use LegionPE\Iota\base\event\team\TeamCreateEvent;
use LegionPE\Iota\base\query\session\NameToUidQuery;
use LegionPE\Iota\base\team\Team;
use LegionPE\Iota\base\team\TeamPlayer;

class TeamCommand extends BaseCommand{
	public function run(BaseSession $session, array $args){
		if(count($args) === 0){
			return $this->getCustomUsage($session->getServerLanguageCodeString());
		}
		if(($argument = $this->getArgument($session->getServerLanguageCodeString(), $args[0])) !== false){
			switch($argument['main']){
				case 'create':
					if(!$this->hasArgumentPermission($session, $argument)){
						return $session->getPresetMessage('commandErrors.upgradeRank');
					}
					if(!(isset($args[1]) and isset($args[2]))){
						return $session->getPresetMessage('commands.team.arguments.create.usage');
					}
					/*if(!($session->getTeam() instanceof Team)){
						return $session->getPresetMessage('commands.team.generalMessages.inTeam');
					}
					if(strlen($args[1]) < 4){
						return $session->getPresetMessage('commands.team.arguments.create.messages.nameTooShort');
					}
					if(strlen($args[1]) > 32){
						return $session->getPresetMessage('commands.team.arguments.create.messages.nameTooLong');
					}
					if(!preg_match(Constants::REGEX_NUMBERS_LETTERS_SYMBOLS, $args[1])){
						return $session->getPresetMessage('commands.team.arguments.create.messages.nameOnlyLettersNumbersAndSymbols');
					}
					if(strlen($args[2]) === 4){
						return $session->getPresetMessage('commands.team.arguments.create.messages.acronymNotRightLength');
					}
					if(!preg_match(Constants::REGEX_NUMBERS_LETTERS, $args[2])){
						return $session->getPresetMessage('commands.team.arguments.create.messages.acronymOnlyLettersAndNumbers');
					}*/
					$session->teamStage = BaseSession::TEAM_STAGE_JOINING;
					if(count($presetMessageErrors = $session->getPlugin()->createTeam(function($result, $rows, $error)use($args, $session){
						if($error === 'Duplicate entry \'' . $args[1] . '\' for key \'name\''){
							$session->sendPresetMessage('commands.team.arguments.create.messages.nameExists');
							return;
						}elseif($error === 'Duplicate entry \'' . strtoupper($args[2]) . '\' for key \'acronym\''){
							$session->sendPresetMessage('commands.team.arguments.create.messages.acronymExists');
							return;
						}
						$session->setTeam($team = new Team($session->getPlugin(), $tid = $result['value']['LAST_INSERT_ID()'], $args[1], strtoupper($args[2]), $session->getUid(), ($creationTime = time()), $chatRoom = new ChatRoom($this->getPlugin(), $this->getPlugin()->getChatRoomPrefix() . 'TEAM-ID-' . $tid, false)));
						$this->getPlugin()->getServer()->getPluginManager()->callEvent(new TeamCreateEvent($this->getPlugin(), $team));
						$this->getPlugin()->addChatRoom($chatRoom);
						$session->addChatRoom($chatRoom);
						$team->addTeamPlayer($teamPlayer = new TeamPlayer($session->getPlugin(), $team->getId(), $team->getName(), Constants::TEAM_STATUS_ACCEPTED, $creationTime, 0, $creationTime, $session->getUid(), $session->getUid(), $session->getPlayer()->getName(), 1, Constants::TEAM_RANK_OWNER));
						$session->setTeamPlayer($teamPlayer);
						$session->teamStage = BaseSession::TEAM_STAGE_DEFAULT;
						$this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_TEAM_CREATE, ['id' => $team->getId(), 'name' => $team->getName()]);
						$session->sendPresetMessage('commands.team.arguments.create.messages.success', ['%team%', '%acronym%'], [$team->getName(), $team->getAcronym()]);
					}, $session, $args[1], strtoupper($args[2]), Constants::TEAM_RANK_OWNER))){
						foreach($presetMessageErrors as $error){
							$session->sendPresetMessageError($error);
						}
					}
					break;
				case 'leave':
					if(!(($team = $session->getTeam()) instanceof Team)){
						return $session->getPresetMessage('commands.team.generalMessages.notInTeam');
					}
					if(!($team->getRank($session) & Constants::TEAM_RANK_OWNER)){
						$team->leave($session);
					}else{
						return $session->getPresetMessage('commands.team.arguments.leave.messages.owner');
					}
					break;
				case 'accept':
					if(!isset($args[1])){
						return $session->getPresetMessage('commands.team.arguments.accept.usage');
					}
					if(!(($teamPlayer = $session->getTeamPlayerInviteByTeamName($args[1])) instanceof TeamPlayer)){
						return $session->getPresetMessage('commands.team.generalMessages.noInvite');
					}
					if(!($session->getTeam() instanceof Team)){
						$teamPlayer->acceptInvite($session);
					}else{
						return $session->getPresetMessage('commands.team.generalMessages.inTeam');
					}
					break;
				case 'deny':
					if(!isset($args[1])){
						return $session->getPresetMessage('commands.team.arguments.accept.usage');
					}
					if(!(($teamPlayer = $session->getTeamPlayerInviteByTeamName($args[1])) instanceof TeamPlayer)){
						$teamPlayer->denyInvite($session);
					}else{
						return $session->getPresetMessage('commands.team.generalMessages.noInvite');
					}
					break;
				case 'chat':
					// todo
					break;
				case 'promote':
					if(!isset($args[1])){
						return $session->getPresetMessage('commands.team.arguments.promote.usage');
					}
					if(!(($team = $session->getTeam()) instanceof Team)){
						return $session->getPresetMessage('commands.team.generalMessages.notInTeam');
					}
					if(!$this->hasArgumentCustomPermission('promote', ($sesTeamPlayer = $team->getTeamPlayer($session))->getRank())){
						return $session->getPresetMessage('commands.team.generalMessages.noPermission');
					}
					if(!(($teamPlayer = $team->getTeamPlayerByName($args[1])) instanceof TeamPlayer)){
						return $session->getPresetMessage('commands.team.generalMessages.userNotInTeam');
					}
					if($teamPlayer->getRank() < $sesTeamPlayer->getRank()){
						$team->promote($session, $teamPlayer);
					}else{
						return $session->getPresetMessage('commands.team.arguments.promote.messages.higherRank', ['%user%'], [$teamPlayer->getName()]);
					}
					break;
				case 'demote':
					if(!isset($args[1])){
						return $session->getPresetMessage('commands.team.arguments.demote.usage');
					}
					if(!(($team = $session->getTeam()) instanceof Team)){
						return $session->getPresetMessage('commands.team.generalMessages.notInTeam');
					}
					if(!$this->hasArgumentCustomPermission('promote', ($sesTeamPlayer = $team->getTeamPlayer($session))->getRank())){
						return $session->getPresetMessage('commands.team.generalMessages.noPermission');
					}
					if(!(($teamPlayer = $team->getTeamPlayerByName($args[1])) instanceof TeamPlayer)){
						return $session->getPresetMessage('commands.team.generalMessages.userNotInTeam');
					}
					if($teamPlayer->getRank() < $sesTeamPlayer->getRank()){
						$team->demote($session, $teamPlayer);
					}else{
						return $session->getPresetMessage('commands.team.arguments.demote.messages.higherRank', ['%user%'], [$teamPlayer->getName()]);
					}
					break;
				case 'invite':
					if(!isset($args[1])){
						return $session->getPresetMessage('commands.team.arguments.invite.usage');
					}
					if(!(($team = $session->getTeam()) instanceof Team)){
						return $session->getPresetMessage('commands.team.generalMessages.notInTeam');
					}
					if(Utils::getSessionMaxTeamPlayers($session) >= count($team->getTeamPlayers())){
						return $session->getPresetMessage('commands.team.generalMessages.full');
					}
					if(!$this->hasArgumentCustomPermission('invite', $team->getTeamPlayer($session)->getRank())){
						return $session->getPresetMessage('commands.team.generalMessages.noPermission');
					}
					if(($teamPlayer = $team->getTeamPlayerByName($args[1])) instanceof TeamPlayer){
						if($teamPlayer->getStatus() === Constants::TEAM_STATUS_ACCEPTED){
							return $session->getPresetMessage('commands.team.arguments.invite.messages.alreadyInTeam');
						}else{
							return $session->getPresetMessage('commands.team.arguments.invite.messages.alreadyInvited');
						}
					}
					new NameToUidQuery($this->getPlugin(), function($result, $rows, $error)use($session, $team, $args){
						if($rows === 1){
							if($result['status']){
								$team->invite($session, $result['uid'], $result['name']);
								return;
							}
							$session->sendPresetMessage('commandErrors.userOffline', ['%user%'], [$result['name']]);
							return;
						}
						$session->sendPresetMessage('commandErrors.userDoesntExist', ['%user%'], [$args[1]]);
					}, $args[1]);
					break;
				case 'list':
					if(($team = $session->getTeam()) instanceof Team){
						$names = [];
						foreach($team->getTeamPlayers() as $teamPlayer){
							$names[] = ($teamPlayer->isAuthenticated() ? '§a' : '§c') . $teamPlayer->getName();
						}
						return $session->getPresetMessage('commands.team.arguments.list.messages.success', ['%team%', '%players%'], [$team->getName(), implode("§9, §r", $names)]);
					}
					return $session->getPresetMessage('commands.team.generalMessages.notInTeam');
					break;
				case 'info':
					if(($team = $session->getTeam()) instanceof Team){
						$names = [];
						$ownerName = "";
						$numberOfMembers = 0;
						$offlineMembers = 0;
						$onlineMembers = 0;
						foreach($team->getTeamPlayers() as $teamPlayer){
							$names[] = ($teamPlayer->isAuthenticated() ? '§a' : '§c') . $teamPlayer->getName();
							++$numberOfMembers;
							if($teamPlayer->isAuthenticated()){
								++$onlineMembers;
							}else{
								++$offlineMembers;
							}
							if($teamPlayer->getRank() & Constants::TEAM_RANK_OWNER){
								$ownerName = $teamPlayer->getName();
							}
						}
						return $session->getPresetMessage('commands.team.arguments.info.messages.success', ['%team%', '%owner%', '%creationdate%', '%acronym%', '%players%', '%numberOfMembers%', '%numberOfOnlineMembers%', '%numberOfOfflineMembers%'], [$team->getName(), $ownerName, date('d/m/Y', $team->getCreationTime()), $team->getAcronym(), implode('§9, §r', $names), $numberOfMembers, $onlineMembers, $offlineMembers]);
					}
					return $session->getPresetMessage('commands.team.generalMessages.notInTeam');
					break;
				case 'disband':
					if(($team = $session->getTeam()) instanceof Team){
						if(!$this->hasArgumentCustomPermission('disband', $team->getTeamPlayer($session)->getRank())){
							return $session->getPresetMessage('commands.team.generalMessages.noPermission');
						}
						$team->disband($session);
					}else{
						return $session->getPresetMessage('commands.team.generalMessages.notInTeam');
					}
					break;
				case 'kick':
					if(($team = $session->getTeam()) instanceof Team){
						if(!$this->hasArgumentCustomPermission('disband', $team->getTeamPlayer($session)->getRank())){
							return $session->getPresetMessage('commands.team.generalMessages.noPermission');
						}
						if(!isset($args[1])){
							return $session->getPresetMessage('commands.team.arguments.kick.usage');
						}
						if(($teamPlayer = $team->getTeamPlayerByName($args[1])) instanceof TeamPlayer){
							if($teamPlayer === $session->getTeamPlayer()){
								return $session->getPresetMessage('commands.team.arguments.kick.messages.cantKickSelf');
							}
							$team->kick($session, $teamPlayer->getUid());
						}
						return $session->getPresetMessage('commands.team.arguments.kick.messages.userNotInTeam');
					}
					return $session->getPresetMessage('commands.team.generalMessages.notInTeam');
					break;
			}
		}else{
			return $session->getPresetMessage('commandErrors.argumentDoesntExist', ['%argument%'], [strtolower($args[0])]);
		}
	}
}
