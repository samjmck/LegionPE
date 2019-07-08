<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class CreateNickNameRequestQuery extends Query{
	private $uid;
	private $nickName;
	public function __construct(BasePlugin $plugin, callable $callback, int $uid, string $nickName){
		parent::__construct($plugin, $callback);
		$this->uid = $uid;
		$this->nickName = $nickName;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("INSERT INTO nicknames VALUES ({$this->uid}, '" . $mysql->escape_string($this->nickName) . "', 0, " . time() . ", null, null)");
		$this->setQueryResult($query);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_INSERT;
	}
}
