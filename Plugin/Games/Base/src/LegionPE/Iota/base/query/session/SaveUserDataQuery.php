<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class SaveUserDataQuery extends Query{
	private $uid;
	private $params;
	private $stats;
	public function __construct(BasePlugin $plugin, callable $callback, int $uid, array $params, array $stats){
		parent::__construct($plugin, $callback);
		$this->uid = $uid;
		$this->params = $params;
		$this->stats = $stats;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$params = $this->params;
		$stats = $this->stats;
		$queryString = 'UPDATE users SET ';
		foreach($params as $column => $values){
			$queryString .= "{$column} = " . $this->makeMySQLCompatible($values[0], $values[1])  . ',';
		}
		$query = $mysql->query(rtrim($queryString, ',') . ' WHERE uid = ' . $this->uid);
		if(count($stats) !== 0){
			$queryString = 'UPDATE users_stats SET ';
			foreach($stats as $column => $values){
				$queryString .= "{$column} = " . $this->makeMySQLCompatible($values[0], $values[1])  . ',';
			}
			$query = $mysql->query(rtrim($queryString, ',') . ' WHERE uid = ' . $this->uid);
		}
		/*$this->setRowCount($this->getQueryRowCount($query));
		$this->setQueryResult($query);*/
		$this->setQueryResult($query);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_UPDATE;
	}
}
