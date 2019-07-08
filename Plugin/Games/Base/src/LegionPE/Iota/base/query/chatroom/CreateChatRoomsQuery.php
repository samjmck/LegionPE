<?php

namespace LegionPE\Iota\base\query\chatroom;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class CreateChatRoomsQuery extends Query{
	private $values;
	public function __construct(BasePlugin $plugin, callable $callback, array $values){
		parent::__construct($plugin, $callback);
		$this->values = $values;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$add = "";
		foreach($this->values as $value){
			$add .= "('" . $mysql->escape_string($value['key']) . "', " . $value['local'] . "),";
		}
		$this->setQueryResult($mysql->query("INSERT INTO chat_rooms (`key`, `local`) VALUES " . substr($add, 0, -1)));
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_INSERT;
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
}
