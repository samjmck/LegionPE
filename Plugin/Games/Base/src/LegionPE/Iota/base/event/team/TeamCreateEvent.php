<?php

namespace LegionPE\Iota\base\event\team;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\team\Team;

class TeamCreateEvent extends TeamEvent{
	public static $handlerList = null;
	/**
	 * @param BasePlugin $plugin
	 * @param Team $team
	 */
	public function __construct(BasePlugin $plugin, Team $team){
		parent::__construct($plugin, $team);
	}
}
