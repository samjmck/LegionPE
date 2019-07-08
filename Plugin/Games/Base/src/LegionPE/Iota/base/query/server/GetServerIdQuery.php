<?php

namespace LegionPE\Iota\base\query\server;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class GetServerIdQuery extends Query{
	private $ip;
	private $port;
	public function __construct(BasePlugin $plugin, callable $callback, string $ip, int $port){
		parent::__construct($plugin, $callback);
		$this->ip = $ip;
		$this->port = $port;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("SELECT id FROM servers WHERE ip = INET_ATON('{$this->ip}') AND port = {$this->port}");
		$this->setQueryResult($query);
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_SELECT;
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_ASSOC;
	}
	public function getColumnTypes(): array{
		return [
			'id' => self::TYPE_INT
		];
	}
}
