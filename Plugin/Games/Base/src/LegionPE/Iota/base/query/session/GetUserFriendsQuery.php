<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class GetUserFriendsQuery extends Query{
	private $uid;
	public function __construct(BasePlugin $plugin, callable $callback, int $uid){
		parent::__construct($plugin, $callback);
		$this->uid = $uid;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("SELECT friendData.*, requesterData.name AS requester_name, requesterData.status AS requester_status, requestedData.name AS requested_name, requestedData.status AS requested_status FROM friends AS friendData LEFT JOIN users AS requesterData ON requesterData.uid = friendData.requester_uid LEFT JOIN users AS requestedData ON requestedData.uid = friendData.requested_uid WHERE friendData.requester_uid = {$this->uid} OR friendData.requested_uid = {$this->uid}");
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
			'requested_uid' => self::TYPE_INT,
			'requester_uid' => self::TYPE_INT,
			'accepted_time' => self::TYPE_INT,
			'status' => self::TYPE_INT,
			'requester_name' => self::TYPE_STRING,
			'requester_online' => self::TYPE_INT,
			'requested_name' => self::TYPE_STRING,
			'requested_online' => self::TYPE_INT
		];
	}
}
