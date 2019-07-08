<?php

namespace LegionPE\Iota\base\event\party\player;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\event\party\PartyEvent;
use LegionPE\Iota\base\party\Party;
use LegionPE\Iota\base\party\PartyPlayer;

abstract class PartyPlayerEvent extends PartyEvent{
	/** @var PartyPlayer */
	protected $partyPlayer;
	/**
	 * @param BasePlugin $plugin
	 * @param Party $party
	 */
	public function __construct(BasePlugin $plugin, Party $party, PartyPlayer $partyPlayer){
		parent::__construct($plugin, $party);
		$this->partyPlayer = $partyPlayer;
	}
	/**
	 * @return PartyPlayer
	 */
	public function getPartyPlayer(): PartyPlayer{
		return $this->partyPlayer;
	}
}
