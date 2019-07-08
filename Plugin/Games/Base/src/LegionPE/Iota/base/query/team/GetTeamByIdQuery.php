<?php

namespace LegionPE\Iota\base\query\team;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class GetTeamByIdQuery extends Query{
	private $id;
	public function __construct(BasePlugin $plugin, callable $callback, int $id){
		parent::__construct($plugin, $callback);
		$this->id = $id;
	}
	public function onRun(){
		$mysql = $this->getConnection();
		$data = [];
		$query = $mysql->query('SELECT * FROM teams WHERE id = ' . $this->id);
		if(!($query instanceof \mysqli_result)){
			/*$this->setRowCount(0);
			$this->setQueryResult([]);*/
			$this->setQueryResult($query, false, []);
			return;
		}
		$assoc = $query->fetch_assoc();
		$data['id'] = $this->id;
		$data['name'] = $assoc['name'];
		$data['acronym'] = $assoc['acronym'];
		$data['creation_time'] = $assoc['creation_time'];
		$data['leader_uid'] = $assoc['leader_uid'];
		$query = $mysql->query('SELECT teams_players.id, teams_players.status AS `status`, teams_players.creation_time, teams_players.invite_duration, teams_players.accepted_time, teams_players.inviter_uid, teams_players.invited_uid, users.name, users.authenticated, teams_players.rank FROM teams_players JOIN users ON teams_players.invited_uid = users.uid AND teams_players.id = ' . $this->id);
		$data['players'] = [];
		while($row = $query->fetch_assoc()){
			$playerData = [];
			$playerData['id'] = (int) $row['id'];
			$playerData['status'] = (int) $row['status'];
			$playerData['creation_time'] = (int)$row['creation_time'];
			$playerData['invite_duration'] = (int) $row['invite_duration'];
			$playerData['accepted_time'] = (int) $row['accepted_time'];
			$playerData['inviter_uid'] = (int) $row['inviter_uid'];
			$playerData['invited_uid'] = (int) $row['invited_uid'];
			$playerData['name'] = $row['name'];
			$playerData['online'] = (int) $row['online'];
			$playerData['rank'] = (int) $row['rank'];
			$data['players'][] = $playerData;
		}
		/*$this->setRowCount($this->getQueryRowCount($query));
		$this->setQueryResult($data);*/
		$this->setQueryResult($query, false, $data);
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_ASSOC;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_SELECT;
	}
}
