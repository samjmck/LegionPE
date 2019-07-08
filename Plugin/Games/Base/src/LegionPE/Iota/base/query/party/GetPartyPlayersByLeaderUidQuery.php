<?php

namespace LegionPE\Iota\base\query\party;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class GetPartyPlayersByLeaderIdQuery extends Query{
	private $leaderUid;
	/**
	 * @param BasePlugin $plugin
	 * @param callable $callback
	 * @param $leaderUid
	 */
	public function __construct(BasePlugin $plugin, callable $callback, int $leaderUid){
		parent::__construct($plugin, $callback);
		$this->leaderUid = $leaderUid;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query('SELECT parties_players.uid, parties_players.status, parties_players.invite_duration, users.name FROM parties_players JOIN users ON parties_players.uid = users.uid AND parties_players.leader_uid = ' . $this->leaderUid);
		/*if(!($query instanceof \mysqli_result)){
			$this->setQueryResult([]);
			return;
		}*/
		$this->setQueryResult($query);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_ASSOC;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_SELECT;
	}
	public function getColumnTypes(): array{
		return [
			'uid' => self::TYPE_INT,
			'status' => self::TYPE_INT,
			'invite_duration' => self::TYPE_INT,
			'name' => self::TYPE_STRING
		];
	}
}
