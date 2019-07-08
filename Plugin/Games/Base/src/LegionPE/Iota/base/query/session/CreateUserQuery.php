<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class CreateUserQuery extends Query{
	private $params;
	public function __construct(BasePlugin $plugin, callable $callback, array $params){
		parent::__construct($plugin, $callback);
		$this->params = $params;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$partOne = 'INSERT INTO users (';
		$partTwo = 'VALUES (';
		$params = $this->params;
		$stats = $this->stats;
		foreach($params as $column => $values){
			$partOne .= "`{$column}`,";
			$partTwo .= $this->makeMySQLCompatible($values[0], $values[1]) . ",";
		}
		$partOne = rtrim($partOne, ',') . ')';
		$partTwo = rtrim($partTwo, ',') . ')';
		$mysql->query($partOne . ' ' . $partTwo);
		$uid = $this->processRow(($query = $mysql->query('SELECT LAST_INSERT_ID()'))->fetch_assoc());
		$mysql->query("INSERT INTO users_stats (`uid`) VALUES({$uid})");
		$this->setQueryResult($query, false, $uid);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_ASSOC;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_INSERT;
	}
	public function getColumnTypes(): array{
		return [
			'LAST_INSERT_ID()' => self::TYPE_INT
		];
	}
}
