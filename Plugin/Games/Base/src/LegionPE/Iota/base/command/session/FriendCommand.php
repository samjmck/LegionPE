<?php

namespace LegionPE\Iota\base\command\session;

use LegionPE\Iota\base\BaseSession;
use LegionPE\Iota\base\command\BaseCommand;
use LegionPE\Iota\base\FriendRelationship;
use LegionPE\Iota\base\query\session\NameToUidQuery;
use pocketmine\Player;

class FriendCommand extends BaseCommand{
	public function run(BaseSession $session, array $args){
		if(count($args) === 0){
			return $this->getCustomUsage($session->getServerLanguageCodeString());
		}
		if(($argument = $this->getArgument($session->getServerLanguageCodeString(), $args[0])) !== false){
			switch($argument['main']){
				case 'accept':
					if(!isset($args[1])){
						return $session->getPresetMessage('commands.friend.arguments.add.usage');
					}
					if(!(($friendRelationship = $session->getFriendRelationshipByUsername($args[1])) instanceof FriendRelationship)){
						$friendRelationship->accept($session);
					}else{
						return $session->getPresetMessage('commands.friend.generalMessages.noFriendRequest', ['%user%'], [$args[1]]);
					}
					break;
				case 'deny':
					if(!isset($args[1])){
						return $session->getPresetMessage('commands.friend.arguments.deny.usage');
					}
					if(($friendRelationship = $session->getFriendRelationshipByUsername($args[1])) instanceof FriendRelationship){
						$friendRelationship->deny($session);
					}else{
						return $session->getPresetMessage('commands.friend.generalMessages.noFriendRequest', ['%user%'], [$args[1]]);
					}
					break;
				case 'add':
					if(!isset($args[1])){
						return $session->getPresetMessage('commands.friend.arguments.add.usage');
					}
					if(Utils::getSessionFriends($session) >= count($session->getFriendRelationships())){
						return $session->getPresetMessage('commands.friend.generalMessages.full');
					}
					if(($fr = $session->getFriendRelationshipByUsername($args[1])) instanceof FriendRelationship){
						return $session->getPresetMessage('commands.friend.generalMessages.alreadyFriend', ['%user%'], [$fr->getRequestedName() === $args[1] ? $fr->getRequesterName() : $fr->getRequestedName()]);
					}
					if(($player = $this->getPlugin()->getServer()->getPlayerExact($args[1])) instanceof Player){
						if(($requestedSession = $this->getPlugin()->getAuthenticatedSession($player)) instanceof BaseSession){
							FriendRelationship::createFriendRequest($this->getPlugin(), $session->getUid(), $session->getPlayer()->getName(), true, $requestedSession->getUid(), $requestedSession->getPlayer()->getName(), $requestedSession->isAuthenticated());
						}else{
							return $session->getPresetMessage('commandErrors.userOffline', ['%user'], [$player->getName()]);
						}
					}else{
						new NameToUidQuery($this->getPlugin(), function($result, $rows, $error)use($session, $args){
							if($rows === 1){
								if(($fr = $session->getFriendRelationshipByUsername($args[1])) instanceof FriendRelationship){
									$session->sendPresetMessage('commands.friend.generalMessages.alreadyFriend', ['%user%'], [$fr->getRequestedName() === $args[1] ? $fr->getRequesterName() : $fr->getRequestedName()]);
								}
								FriendRelationship::createFriendRequest($this->getPlugin(), $session->getUid(), $session->getPlayer()->getName(), $session->isAuthenticated(), $result['value']['uid'], $result['value']['name'], $result['value']['online']);
							}else{
								$session->sendPresetMessage('commands.friend.arguments.add.messages.noUserFound', ['%user%'], [$args[1]]);
							}
						}, $args[1]);
					}
					break;
				case 'remove':
					if(!isset($args[1])){
						return $session->getPresetMessage('commands.friend.arguments.remove.usage');
					}
					if(($friendRelationship = $session->getFriendRelationshipByUsername($args[1])) instanceof FriendRelationship){
						$friendRelationship->remove();
						return $session->getPresetMessage('command.friend.arguments.remove.messages.success', ['%user%'], [($friendRelationship->getRequestedName() == $args[1] ? $friendRelationship->getRequestedName() : $friendRelationship->getRequesterName())]);
					}else{
						return $session->getPresetMessage('command.friend.arguments.remove.messages.noFriend', ['%user%'], [$args[1]]);
					}
					break;
				case 'list':
					$names = [];
					foreach($session->getAcceptedFriendRelationships() as $friendRelationship){
						if($friendRelationship->getRequesteduid() === $session->getUid()){
							$names[] = ($friendRelationship->getRequesterOnline() ? '§a' : '§c') . $friendRelationship->getRequesterName();
						}else{
							$names[] = ($friendRelationship->getRequestedOnline() ? '§a' : '§c') . $friendRelationship->getRequestedName();
						}
					}
					return $session->getPresetMessage('command.friend.arguments.list.messages.success', ['%friends%'], [implode('§9, §r', $names)]);
					break;
				case 'requests':
					$names = [];
					foreach($session->getRequestedFriendRelationships() as $friendRelationship){
						$names[] = '§9' . ($friendRelationship->getRequesterUid() === $session->getUid() ? $friendRelationship->getRequestedName() : $friendRelationship->getRequesterName());
					}
					return $session->getPresetMessage('command.friend.arguments.requests.messages.success', ['%requests%'], [implode('§9, §r', $names)]);
					break;
				case 'pending':
					$names = [];
					foreach($session->getPendingFriendRelationships() as $friendRelationship){
						$names[] = '§9' . ($friendRelationship->getRequesterUid() === $session->getUid() ? $friendRelationship->getRequestedName() : $friendRelationship->getRequesterName());
					}
					return $session->getPresetMessage('command.friend.arguments.pending.messages.success', ['%pending%'], [implode('§9, §r', $names)]);
					break;
			}
		}else{
			return $session->getPresetMessage('commandErrors.argumentDoesntExist', ['%argument%'], [strtolower($args[0])]);
		}
	}
}
