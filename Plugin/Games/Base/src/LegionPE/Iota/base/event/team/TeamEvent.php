<?php

namespace LegionPE\Iota\base\event\team;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\event\BaseEvent;
use LegionPE\Iota\base\team\Team;

abstract class TeamEvent extends BaseEvent{
	/** @var Team */
	protected $team;
	/**
	 * @param BasePlugin $plugin
	 * @param Team $team
	 */
	public function __construct(BasePlugin $plugin, Team $team){
		parent::__construct($plugin);
		$this->team = $team;
	}
	/**
	 * @return Team
	 */
	public function getTeam(){
		return $this->team;
	}
}
