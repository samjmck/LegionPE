<?php

namespace LegionPE\Iota\base\query\team;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\query\Query;

class SaveTeamPlayersQuery extends Query{
	private $params;
	public function __construct(BasePlugin $plugin, callable $callback, array $params = []){
		parent::__construct($plugin, $callback);
		$this->params = $params;
	}
	public function onRun(){
		/* params structure
			'columns' => [column => datatype],
			'users' => [
				inviteduid => [column => value, column => value, ... ],
				inviteduid => [column => value, column => value, ... ],
				...
			]
		*/
		$mysql = $this->getConnection();
		$params = $this->params;
		if(count($params['users']) === 1){
			$query = 'UPDATE teams_players SET ';
			foreach($this->params['users'][0] as $key => $value){
				$query .= $key . '=' . $this->makeMySQLCompatible($value, $params['columns'][$key]) . ',';
			}
			$query = $mysql->query(rtrim($query, ',') . ' WHERE invited_uid = ' . $params['users'][0]['inviteduid']);
			$this->setQueryResult($query);
		}else{
			$query = "UPDATE teams_players\nSET ";
			$setColumns = [];
			$setValues = [];
			$uids = '';
			foreach($params['columns'] as $index => $value){
				$setColumns[$index] = "{$value} = ";
				$setValues[$index] = '(CASE ';
			}
			foreach($params['users'] as $invitedUid => $row){
				$uids .= $invitedUid . ',';
				$columnIndex = 0;
				foreach($row as $column => $value){
					$setValues[$columnIndex] .= "WHEN invited_uid = {$invitedUid} THEN " . $this->makeMySQLCompatible($value, $this->params['columns'][$column]) . "\n";
					$columnIndex++;
				}
			}
			foreach($setColumns as $index => $setColumn){
				$query .= $setColumn . $setValues[$index] . "END),\n";
			}
			$query = $mysql->query(rtrim($query, ",\n") . "\nWHERE invited_uid IN (" . rtrim($uids, ',') . ')');
			/*$this->setRowCount($this->getQueryRowCount($query));
			$this->setQueryResult($query);*/
			$this->setQueryResult($query);
		}
	}
	public function getResultType(): int{
		return self::RESULT_TYPE_RAW;
	}
	public function getQueryType(): int{
		return self::QUERY_TYPE_UPDATE;
	}
}
