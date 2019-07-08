<?php

namespace LegionPE\Iota\base\query\team;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class DeleteTeamPlayerQuery extends Query{
	private $id;
	private $uid;
	public function __construct(BasePlugin $plugin, callable $callback, int $id, int $uid){
		parent::__construct($plugin, $callback);
		$this->id = $id;
		$this->uid = $uid;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("DELETE FROM teams_players WHERE id = {$this->id} AND invited_uid = {$this->uid}");
		/*$this->setRowCount($this->getQueryRowCount($query));
		$this->setQueryResult($query);*/
		$this->setQueryResult($query);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_DELETE;
	}
}
