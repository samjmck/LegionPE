<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class RemoveNickNameQuery extends Query{
	private $uid;
	public function __construct(BasePlugin $plugin, callable $callback, int $uid){
		parent::__construct($plugin, $callback);
		$this->uid = $uid;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("DELETE FROM nicknames WHERE uid = {$this->uid}");
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
