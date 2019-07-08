<?php

namespace LegionPE\Iota\base\query\team;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class CreateTeamQuery extends Query{
	private $name;
	private $acronym;
	private $leaderUid;
	private $ownerRank;
	public function __construct(BasePlugin $plugin, callable $callback, string $name, string $acronym, int $leaderUid, int $ownerRank){
		parent::__construct($plugin, $callback);
		$this->name = $name;
		$this->acronym = $acronym;
		$this->leaderUid = $leaderUid;
		$this->ownerRank = $ownerRank;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("INSERT INTO teams (`name`, `acronym`, `creation_time`, `leader_uid`) VALUES ('" . $mysql->escape_string($this->name) . "', '" . $mysql->escape_string($this->acronym) . "', " . time() . ", {$this->leaderUid})");
		if(!$query){
			//$this->setRowCount($this->getQueryRowCount($query));
			return;
		}
		$lastInsertIdQuery = $mysql->query('SELECT LAST_INSERT_ID()');
		$lastInsertIdArray = $lastInsertIdQuery->fetch_assoc();
		/*$this->setRowCount($this->getQueryRowCount($lastInsertIdQuery));
		$this->setQueryResult($this->processRow($lastInsertIdArray));*/
		$this->setQueryResult($lastInsertIdQuery);
		$mysql->query("INSERT INTO teams_players (`id`, `creation_time`, `accepted_time`, `invite_duration`, `inviter_uid`, `invited_uid`, `rank`, `status`) VALUES (" . $lastInsertIdArray["LAST_INSERT_ID()"] . ", " . time() . ", 0, 0, {$this->leaderUid}, {$this->leaderUid}, {$this->ownerRank}, 1)");
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_ASSOC;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_SELECT;
	}
	public function getColumnTypes(): array{
		return [
			'LAST_INSERT_ID()' => self::TYPE_INT
		];
	}
}
