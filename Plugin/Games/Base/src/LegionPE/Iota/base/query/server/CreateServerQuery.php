<?php

namespace LegionPE\Iota\base\query\server;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class CreateServerQuery extends Query{
	private $ip;
	private $port;
	private $status;
	public function __construct(BasePlugin $plugin, callable $callback, string $ip, string $port, int $status){
		parent::__construct($plugin, $callback);
		$this->ip = $ip;
		$this->port = $port;
		$this->status = $status;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$mysql->query("INSERT INTO `servers` (`status`, `ip`, `port`) VALUES ({$this->status}, INET_ATON('{$this->ip}'), {$this->port})");
		$query = $mysql->query('SELECT LAST_INSERT_ID()');
		$this->setQueryResult($query, false, $this->processRow($query->fetch_assoc())['LAST_INSERT_ID()']);
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_UPDATE;
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
	public function getColumnTypes(): array{
		return [
			'LAST_INSERT_ID()' => self::TYPE_INT
		];
	}
}
