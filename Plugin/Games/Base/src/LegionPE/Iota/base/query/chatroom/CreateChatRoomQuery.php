<?php

namespace LegionPE\Iota\base\query\chatroom;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class CreateChatRoomQuery extends Query{
	private $key;
	private $local;
	public function __construct(BasePlugin $plugin, callable $callback, string $key, int $local){
		parent::__construct($plugin, $callback);
		$this->key = $key;
		$this->local = $local;
	}
	public function onRun(){
		$this->setQueryResult(($mysql = $this->getConnection())->query("INSERT INTO chat_rooms (`key`, `local`) VALUES ('" . $mysql->escape_string($this->key) . "', {$this->local})"));
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_INSERT;
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
}
