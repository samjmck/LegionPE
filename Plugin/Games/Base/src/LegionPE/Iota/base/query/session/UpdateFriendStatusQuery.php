<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class UpdateFriendStatusQuery extends Query{
	private $status;
	private $requesterUid;
	private $requestedUid;
	private $acceptedStatus;
	public function __construct(BasePlugin $plugin, callable $callback, int $requesterUid, int $requestedUid, int $status, int $acceptedStatus){
		parent::__construct($plugin, $callback);
		$this->requesterUid = $requesterUid;
		$this->requestedUid = $requestedUid;
		$this->status = $status;
		$this->acceptedStatus = $status;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("UPDATE friends SET status = {$this->status}" . ($this->status === $this->acceptedStatus ? ", accepted_time = " . time() : "") . " WHERE requester_uid = {$this->requesterUid} AND requested_uid = {$this->requestedUid}");
		/*$this->setRowCount($this->getQueryRowCount($query));
		$this->setQueryResult($query);*/
		$this->setQueryResult($query);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_SELECT;
	}
}
