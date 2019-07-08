<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class CreateFriendRequestQuery extends Query{
	private $requesterUid;
	private $requestedUid;
	public function __construct(BasePlugin $plugin, callable $callback, int $requesterUid, int $requestedUid){
		parent::__construct($plugin, $callback);
		$this->requesterUid = $requesterUid;
		$this->requestedUid = $requestedUid;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("INSERT INTO friends (`requester_uid`, `requested_uid`, `status`, `accepted_time`) VALUES ({$this->requesterUid}, {$this->requestedUid}, 0, null})");
		$this->setQueryResult($query);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_INSERT;
	}
}
