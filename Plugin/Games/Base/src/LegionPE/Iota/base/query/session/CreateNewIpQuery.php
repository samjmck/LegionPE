<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class CreateNewIpQuery extends Query{
	public function __construct(BasePlugin $plugin, callable $callback, int $uid, string $ip){
		parent::__construct($plugin, $callback);
		$this->uid = $uid;
		$this->ip = $ip;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query('INSERT IGNORE INTO ips_game VALUES (' . $this->uid . ", INET_ATON('". $this->ip . "'))");
		$this->setQueryResult($query);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_INSERT;
	}
}
