<?php

namespace LegionPE\Iota\base\event\party;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\event\BaseEvent;
use LegionPE\Iota\base\party\Party;

abstract class PartyEvent extends BaseEvent{
	/** @var Party */
	protected $party;
	/**
	 * @param BasePlugin $plugin
	 * @param Party $party
	 */
	public function __construct(BasePlugin $plugin, Party $party){
		parent::__construct($plugin);
		$this->party = $party;
	}
	/**
	 * @return Party
	 */
	public function getParty(): Party{
		return $this->party;
	}
}
