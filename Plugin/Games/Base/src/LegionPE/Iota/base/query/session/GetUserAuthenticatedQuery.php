<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class GetUserAuthenticatedQuery extends Query{
	private $name;
	public function __construct(BasePlugin $plugin, callable $callback, string $name){
		parent::__construct($plugin, $callback);
		$this->name = $name;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("SELECT authenticated FROM users WHERE name = '" . $mysql->escape_string($this->name) . "'");
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
			'authenticated' => self::TYPE_INT
		];
	}
}
