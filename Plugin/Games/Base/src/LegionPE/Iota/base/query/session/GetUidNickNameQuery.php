<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class GetUidNickNameQuery extends Query{
	private $uid;
	public function __construct(BasePlugin $plugin, callable $callback, int $uid){
		parent::__construct($plugin, $callback);
		$this->uid = $uid;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query('SELECT nickname, status FROM nicknames WHERE uid = ' . $this->uid);
		//$this->setQueryResult($this->processRow($query->fetch_assoc()));
		$this->setQueryResult($query);
	}
	public function getColumnTypes(): array{
		return [
			'nickname' => self::TYPE_STRING,
			'status' => self::TYPE_INT
		];
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_ASSOC;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_SELECT;
	}
}
