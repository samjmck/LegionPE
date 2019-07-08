<?php

namespace LegionPE\Iota\base\event\party;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\event\BaseEvent;
use LegionPE\Iota\base\team\Team;
use LegionPE\Iota\base\team\TeamPlayer;

abstract class TeamPlayerEvent extends TeamEvent{
	/** @var TeamPlayer */
	protected $teamPlayer;
	/**
	 * @param BasePlugin $plugin
	 * @param Team $team
	 * @param TeamPlayer $teamPlayer
	 */
	public function __construct(BasePlugin $plugin, Team $team, TeamPlayer $teamPlayer){
		parent::__construct($plugin, $team);
		$this->teamPlayer = $teamPlayer;
	}
	/**
	 * @return TeamPlayer
	 */
	public function getTeamPlayer(): TeamPlayer{
		return $this->teamPlayer;
	}
}
