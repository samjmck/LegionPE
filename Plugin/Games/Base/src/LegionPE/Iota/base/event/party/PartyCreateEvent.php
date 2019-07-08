<?php

namespace LegionPE\Iota\base\event\party;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\BaseSession;
use LegionPE\Iota\base\event\BaseEvent;
use LegionPE\Iota\base\party\Party;

class PartyCreateEvent extends PartyEvent{
	public static $handlerList = null;
	/**
	 * @param BasePlugin $plugin
	 * @param Party $party
	 */
	public function __construct(BasePlugin $plugin, Party $party){
		parent::__construct($plugin, $party);
	}
}
