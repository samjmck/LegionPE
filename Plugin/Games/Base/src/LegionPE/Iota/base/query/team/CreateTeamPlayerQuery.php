<?php

namespace LegionPE\Iota\base\query\team;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class CreateTeamPlayerQuery extends Query{
	private $id;
	private $inviteDuration;
	private $inviterUid;
	private $invitedUid;
	private $rank;
	private $status;
	public function __construct(BasePlugin $plugin, callable $callback, int $id, int $inviteDuration, int $inviterUid, int $invitedUid, int $rank, int $status){
		parent::__construct($plugin, $callback);
		$this->id = $id;
		$this->inviteDuration = $inviteDuration;
		$this->inviterUid = $inviterUid;
		$this->invitedUid = $invitedUid;
		$this->rank = $rank;
		$this->status = $status;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$query = $mysql->query("INSERT INTO teams_players (`id`, `creation_time`, `accepted_time`, `invite_duration`, `inviter_uid`, `invited_uid`, `rank`, `status`) VALUES ({$this->id}, " . time() . ", null, {$this->inviteDuration}, {$this->inviterUid}, {$this->invitedUid}, {$this->rank}, {$this->status})");
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
