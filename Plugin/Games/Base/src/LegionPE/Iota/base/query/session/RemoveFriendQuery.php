<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class RemoveFriendQuery extends Query{
	private $requesterUid;
	private $requestedUid;
	public function __construct(BasePlugin $plugin, callable $callback, int $requesterUid, int $requestedUid){
		parent::__construct($plugin, $callback);
		$this->requesterUid = $requesterUid;
		$this->requestedUid = $requestedUid;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("DELETE FROM friends WHERE requester_uid = {$this->requesterUid} AND requested_uid = {$this->requestedUid}");
		/*$this->setRowCount($this->getQueryRowCount($query));
		$this->setQueryResult($query);*/
		$this->setQueryResult($query);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_DELETE;
	}
}
