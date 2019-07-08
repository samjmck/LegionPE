<?php

namespace LegionPE\Iota\base\query\party;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class CreatePartyPlayerQuery extends Query{
	/** @var int */
	private $leaderUid;
	/** @var int */
	private $uid;
	/** @var int */
	private $status;
	/**
	 * @param BasePlugin $plugin
	 * @param callable $callback
	 * @param int $leaderUid
	 * @param int $uid
	 * @param int $status
	 */
	public function __construct(BasePlugin $plugin, callable $callback, int $leaderUid, int $uid, int $status){
		parent::__construct($plugin, $callback);
		$this->leaderUid = $leaderUid;
		$this->uid = $uid;
		$this->status = $status;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("INSERT INTO parties_players (leader_uid, uid, status) VALUES ({$this->leaderUid}, {$this->uid}, {$this->status})");
		$this->setQueryResult($query);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_INSERT;
	}
}
