<?php

namespace LegionPE\Iota\base\query\team;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class GetTeamPlayersQuery extends Query{
	private $id;
	public function __construct(BasePlugin $plugin, callable $callback, int $id){
		parent::__construct($plugin, $callback);
		$this->id = $id;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query('SELECT teams_players.id, teams_players.status AS `status`, teams_players.creation_time, teams_players.invite_duration, teams_players.accepted_time, teams_players.inviter_uid, teams_players.invited_uid, users.name, users.authenticated, teams_players.rank FROM teams_players JOIN users ON teams_players.invited_uid = users.uid AND teams_players.id = ' . $this->id);
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
			'id' => self::TYPE_INT,
			'status' => self::TYPE_INT,
			'creation_time' => self::TYPE_INT,
			'invite_duration' => self::TYPE_INT,
			'accepted_time' => self::TYPE_INT,
			'inviter_uid' => self::TYPE_INT,
			'invited_uid' => self::TYPE_INT,
			'name' => self::TYPE_STRING,
			'online' => self::TYPE_INT
		];
	}
}
