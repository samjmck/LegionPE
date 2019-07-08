<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class GetUserIgnoredQuery extends Query{
	private $uid;
	public function __construct(BasePlugin $plugin, callable $callback, int $uid){
		parent::__construct($plugin, $callback);
		$this->uid = $uid;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("SELECT * FROM ignored_users WHERE uid = {$this->uid}");
		/*$this->setRowCount($this->getQueryRowCount($query));
		$this->setQueryResult($this->getProcessedRowsFromResult($query));*/
		$this->setQueryResult($query);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_ASSOC;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_SELECT;
	}
	public function getColumnTypes(): array{
		return [
			'uid' => self::TYPE_INT,
			'ignored_uid' => self::TYPE_INT
		];
	}
}
