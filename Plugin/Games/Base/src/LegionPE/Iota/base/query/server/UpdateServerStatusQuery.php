<?php

namespace LegionPE\Iota\base\query\server;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class UpdateServerStatusQuery extends Query{
	private $id;
	private $status;
	public function __construct(BasePlugin $plugin, callable $callback, int $id, int $status){
		parent::__construct($plugin, $callback);
		$this->id = $id;
		$this->status = $status;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("UPDATE servers SET `status` = {$this->status} WHERE `id` = {$this->id}");
		$this->setQueryResult($query);
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_UPDATE;
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
}
