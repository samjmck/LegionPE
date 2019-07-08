<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class GetUserPartyInvitesQuery extends Query{
	private $uid;
	public function __construct(BasePlugin $plugin, callable $callback, int $uid){
		parent::__construct($plugin, $callback);
		$this->uid = $uid;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query('SELECT parties_players.leader_uid, parties_players.uid, parties_players.status, parties_players.invite_duration, users.name as `leader_name` FROM parties_players JOIN users ON parties_players.leader_uid = users.uid WHERE uid = ' . $this->uid . ' AND status = 0');
		/*$this->setRowCount($this->getQueryRowCount($query));
		$this->setQueryResult($this->getProcessedRowsFromResult($query));*/
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
			'leader_uid' => self::TYPE_INT,
			'uid' => self::TYPE_INT,
			'status' => self::TYPE_INT,
			'invite_duration' => self::TYPE_INT,
			'leader_name' => self::TYPE_STRING
		];
	}
}
