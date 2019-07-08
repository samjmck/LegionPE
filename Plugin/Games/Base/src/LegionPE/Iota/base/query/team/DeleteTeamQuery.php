<?php

namespace LegionPE\Iota\base\query\team;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class DeleteTeamQuery extends Query{
	private $id;
	public function __construct(BasePlugin $plugin, callable $callback, int $id){
		parent::__construct($plugin, $callback);
		$this->id = $id;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("DELETE FROM teams WHERE id = {$this->id}");
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
