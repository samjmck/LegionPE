<?php

namespace LegionPE\Iota\base\command\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\BaseSession;
use LegionPE\Iota\base\command\BaseCommand;
use LegionPE\Iota\base\Constants;

class PunishCommand extends BaseCommand{
	private $reasons = [];
	public function __construct(BasePlugin $plugin, array $data){
		parent::__construct($plugin, $data);
		foreach($data as $languageCode => $extraData){
			foreach($extraData['extra'] as $key => $extra){
				if(!isset($this->reasons[$languageCode])){
					$this->reasons[$languageCode] = [];
				}
				$this->reasons[$languageCode][$argument = $extra['name']] = [
					'main' => $key,
				];
				foreach($extra['aliases'] as $alias){
					$this->reasons[$languageCode][$alias] = $this->reasons[$languageCode][$argument];
					$this->reasons[$languageCode][$argument]['isAlias'] = true;
				}
			}
		}
	}
	public function run(BaseSession $session, array $args){
		if(count($args) === 0){
			return $this->getCustomUsage($session->getServerLanguageCodeString());
		}
		if(!isset($args[1])){
			return $session->getPresetMessage('commands.punish.generalMessages.reasonNotSet', ['%reasons%'], [implode(", ", $this->getReasons($session->getServerLanguageCodeString()))]);
		}
		if(!($reason = $this->getReason($session->getServerLanguageCodeString(), $args[1]))){
			return $session->getPresetMessage('commands.punish.generalMessages.reasonDoesntExist', ['%reason%', '%reasons%'], [strtolower($args[1]), implode(", ", $this->getReasons($session->getServerLanguageCodeString()))]);
		}
		if(($player = $this->getPlugin()->getServer()->getPlayerExact($args[0])) instanceof Player){
			if(($punishedSession = $this->getPlugin()->findSession($player)) instanceof BaseSession){
				if($punishedSession->isRegistered()){
					$this->punish($session, $punishedSession, $reason['main'], implode(" ", array_slice($args, 2)));
				}
			}
		}else{
			new NameToUidQuery($this->getPlugin(), function($result, $rows, $error)use($session, $args, $reason){
				if($rows === 1){
					$this->punish($session, $result['uid'], $reason['main'], implode(" ", array_slice($args, 2)));
				}else{
					$session->sendPresetMessage('commandErrors.userDoesntExist', ['%user%'], [$args[0]]);
				}
			}, $args[1]);
		}
	}
	/**
	 * @param string $languageCode
	 * @return array
	 */
	public function getReasons(string $languageCode){
		$reasons = [];
		foreach($this->reasons[$languageCode] as $key => $argument){
			if($argument['isAlias']) $reasons[] = $key;
		}
		return $reasons;
	}
	/**
	 * @param string $languageCode
	 * @param string $reason
	 * @return bool
	 */
	public function getReason(string $languageCode, string $reason){
		return array_key_exists($reason, $this->reasons[$languageCode]) ? $this->reasons[$languageCode][$reason] : false;
	}
	/**
	 * @param BaseSession $sender
	 * @param BaseSession|int $punished
	 * @param string $reason
	 */
	public function punish(BaseSession $sender, $punished, string $reason, $extra = null){
		if($punished instanceof BaseSession){
			self::routerPunish($sender->getUid(), $sender->getPlayer()->getAddress(), $punished, $reason, $extra);
		}else{
			$this->getPlugin()->getNetworkSyncThread()->callEvent(Constants::ROUTER_EVENT_PUNISH, ['senderUid' => $sender->getUid(), 'senderIp' => $sender->getPlayer()->getAddress(), 'punishedUid' => $punished, 'reason' => $reason, 'extra' => $extra]);
		}
	}
	public static function routerPunish(int $senderUid, string $senderIp, BaseSession $punished, string $reason, string $extra){
		switch($reason){
			case 'bugexploiting':
				BaseSession::routerBan($punished, $senderUid, $senderIp, 'Bug exploiting' . ($extra !== null or $extra !== "" ? ': ' . $extra : ''), Constants::BAN_DURATION_BUG_EXPLOITING, false, true);
				break;
			case 'impersonation':
				BaseSession::routerBan($punished, $senderUid, $senderIp, 'Impersonation' . ($extra !== null or $extra !== "" ? ': ' . $extra : ''), Constants::BAN_DURATION_IMPERSONATION, false, true);
				break;
			case 'hacking':
				BaseSession::routerBan($punished, $senderUid, $senderIp, 'Hacking' . ($extra !== null or $extra !== "" ? ': ' . $extra : ''), Constants::BAN_DURATION_HACKING, true, true);
				break;
			case 'swearing':
				BaseSession::routerMute($punished, $senderUid, $senderIp, 'Swearing' . ($extra !== null or $extra !== "" ? ': ' . $extra : ''), Constants::MUTE_DURATION_SWEARING, true, true);
				break;
			case 'spam':
				BaseSession::routerMute($punished, $senderUid, $senderIp, 'Spam' . ($extra !== null or $extra !== "" ? ': ' . $extra : ''), Constants::MUTE_DURATION_SPAM, true, true);
				break;
			case 'advertising':
				BaseSession::routerMute($punished, $senderUid, $senderIp, 'Advertising' . ($extra !== null or $extra !== "" ? ': ' . $extra : ''), Constants::MUTE_DURATION_ADVERTISING, true, true);
				break;
		}
	}
}
