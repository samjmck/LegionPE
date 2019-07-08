<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class IgnoreUserQuery extends Query{
	private $uid;
	private $ignoredUid;
	public function __construct(BasePlugin $plugin, callable $callback, int $uid, int $ignoredUid){
		parent::__construct($plugin, $callback);
		$this->uid = $uid;
		$this->ignoredUid = $ignoredUid;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("INSERT INTO ignored_uid VALUES ({$this->uid}, {$this->ignoredUid})");
		$this->setQueryResult($query);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_INSERT;
	}
}
