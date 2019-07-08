<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class GetUserAdminLogsQuery extends Query{
	private $uid;
	private $ip;
	private $uuid;
	public function __construct(BasePlugin $plugin, callable $callback, int $uid, string $ip, $uuid){
		parent::__construct($plugin, $callback);
		$this->uid = $uid;
		$this->ip = $ip;
		$this->uuid = $uuid;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query('SELECT administration_logs.*, INET_NTOA(administration_logs.ip) AS ipstring, INET_NTOA(administration_logs.from_ip) AS from_ip_string FROM administration_logs WHERE uid = ' . $this->uid . ' OR (ip = INET_ATON(\'' . $this->ip . '\') AND ip_sensitive = 1) OR (uuid = ' . $this->uuid . ' AND uuid_sensitive = 1)');
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
			'id' => self::TYPE_INT,
			'uid' => self::TYPE_INT,
			'ip' => self::TYPE_INT,
			'uuid' => self::TYPE_STRING,
			'creation_time' => self::TYPE_INT,
			'type' => self::TYPE_INT,
			'msg' => self::TYPE_STRING,
			'from_uid' => self::TYPE_INT,
			'from_ip' => self::TYPE_STRING,
			'created' => self::TYPE_INT,
			'duration' => self::TYPE_INT,
			'ip_string' => self::TYPE_STRING,
			'from_ip_string' => self::TYPE_STRING,
			'ip_sensitive' => self::TYPE_INT,
			'uuid_sensitive' => self::TYPE_INT
		];
	}
}
