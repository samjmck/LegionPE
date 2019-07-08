<?php

namespace LegionPE\Iota\base\query\session;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class CreateAdministrationLogQuery extends Query{
	private $uid;
	private $uuid;
	private $type;
	private $msg;
	private $fromUid;
	private $duration;
	private $ipSensitive;
	private $uuidSensitive;
	public function __construct(BasePlugin $plugin, callable $callback, int $uid, $uuid, int $type, string $msg, int $fromUid, int $duration, bool $ipSensitive, bool $uuidSensitive){
		parent::__construct($plugin, $callback);
		$this->uid = $uid;
		$this->uuid = $uuid;
		$this->type = $type;
		$this->msg = $msg;
		$this->fromUid = $fromUid;
		$this->duration = $duration;
		$this->ipSensitive = (int) $this->ipSensitive;
		$this->uuidSensitive = (int) $this->uuidSensitive;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("INSERT INTO administration_logs (id, uid, ip, uuid, creation_time, `type`, msg, from_uid, from_ip, duration, ip_sensitive, uuid_sensitive) VALUES ('{$this->uid}', X'{$this->uuid}', " . time() . ", {$this->type}, " . ($this->msg == '' or $this->msg === null ? null : "'" . $mysql->escape_string($this->msg) . "'") . ", {$this->fromUid}, {$this->duration}, {$this->ipSensitive}, {$this->uuidSensitive})");
		$this->setQueryResult($query, false, $this->processRow($mysql->query('SELECT LAST_INSERT_ID()')->fetch_assoc()));
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_ASSOC;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_INSERT;
	}
	public function getColumnTypes(): array{
		return [
			'LAST_INSERT_ID()' => self::TYPE_INT
		];
	}
}
