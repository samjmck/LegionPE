<?php

namespace LegionPE\Iota\KitPvP;

use LegionPE\Iota\base\BaseSession;
use LegionPE\Iota\base\Constants;
use LegionPE\Iota\base\query\Query;

class KitPvPSession extends BaseSession{
	/** @var int */
	private $kills;
	/** @var int */
	private $deaths;
	/** @var int */
	private $maxKillstreak;
	protected function initData(array $data){
		parent::initData($data);
		$this->kills = $data['pvp_kills'];
		$this->deaths = $data['pvp_deaths'];
		$this->maxKillstreak = $data['pvp_maxstreak'];
	}
	protected function setRegisterVars(string $hash){
		parent::setRegisterVars($hash);
		$this->kills = Constants::DEFAULT_VALUE_PVP_KILLS;
		$this->deaths = Constants::DEFAULT_VALUE_PVP_DEATHS;
		$this->maxKillstreak = Constants::DEFAULT_VALUE_PVP_MAXSTREAK;
	}
	protected function getSaveData(){
		$data = parent::getSaveData();
		return $data;
	}
	protected function getStatsData(){
		return [];
	}
}
