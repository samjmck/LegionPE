<?php

namespace LegionPE\Iota\base;

use LegionPE\Iota\base\query\Query;

class DeletePartyPlayersQuery extends Query{
	/** @var int */
	private $leaderUid;
	/**
	 * @param BasePlugin $plugin
	 * @param callable $callback
	 * @param int $leaderUid
	 */
	public function __construct(BasePlugin $plugin, callable $callback, int $leaderUid){
		parent::__construct($plugin, $callback);
		$this->leaderUid = $leaderUid;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("DELETE FROM parties_players WHERE leader_uid = {$this->leaderUid}");
		$this->setQueryResult($query);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_DELETE;
	}
}
