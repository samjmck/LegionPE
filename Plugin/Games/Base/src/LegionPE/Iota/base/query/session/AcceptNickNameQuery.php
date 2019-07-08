<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class AcceptNickNameQuery extends Query{
	private $status;
	private $uidChanger;
	private $nickName;
	public function __construct(BasePlugin $plugin, callable $callback, int $status, int $uidChanger, string $nickName){
		parent::__construct($plugin, $callback);
		$this->status = $status;
		$this->uidChanger = $uidChanger;
		$this->nickName = $nickName;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("UPDATE nicknames SET status = {$this->status}, uid_changer = {$this->uidChanger}, changed_time = " . time() . " WHERE nickname = '" . $mysql->escape_string($this->nickName) . "'");
		$this->setQueryResult($query);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_UPDATE;
	}
}
