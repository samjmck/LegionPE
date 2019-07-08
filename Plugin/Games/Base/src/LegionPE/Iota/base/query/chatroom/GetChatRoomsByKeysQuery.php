<?php

namespace LegionPE\Iota\base\query\chatroom;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class GetChatRoomsByKeysQuery extends Query{
	private $keys;
	public function __construct(BasePlugin $plugin, callable $callback, array $keys){
		parent::__construct($plugin, $callback);
		$this->keys = $keys;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$keys = "";
		foreach($this->keys as $key){
			$keys .= '\'' . $key . '\',';
		}
		$keys = substr($keys, 0, -1);
		$query = $mysql->query('SELECT * FROM chat_rooms WHERE `key` IN (' . $keys . ')');
		$this->setQueryResult($query);
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_SELECT;
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_ASSOC;
	}
	public function getColumnTypes(): array{
		return [
			'key' => self::TYPE_STRING,
			'local' => self::TYPE_INT
		];
	}
}
