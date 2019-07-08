<?php

namespace LegionPE\Iota\KitPvP;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\BaseSession;
use pocketmine\Player;

class KitPvPPlugin extends BasePlugin{
	/** @var string[] */
	protected $worldNames = ['world'];
	/**
	 * @param BasePlugin $plugin
	 * @param Player $player
	 * @return KitPvPSession
	 */
	public function createSession(BasePlugin $plugin, Player $player): BaseSession{
		return (new KitPvPSession($this, $player));
	}
}
