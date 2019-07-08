<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class NameToUidQuery extends Query{
	private $name;
	public function __construct(BasePlugin $plugin, callable $callback, string $name){
		parent::__construct($plugin, $callback);
		$this->name = $name;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("SELECT uid,`name`,status FROM users WHERE `name` = '{$this->name}'");
		/*$this->setRowCount($this->getQueryRowCount($query));
		$this->setQueryResult($this->getProcessedRowsFromResult($query));*/
		$this->setQueryResult($query);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_ASSOC;
	}
	public function getColumnTypes(): array{
		return [
			'uid' => self::TYPE_INT,
			'name' => self::TYPE_STRING,
			'status' => self::TYPE_INT
		];
	}
}
