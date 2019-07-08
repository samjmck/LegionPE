<?php

namespace LegionPE\Iota\base\query;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\Utils;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

abstract class Query extends AsyncTask{
	const KEY_MYSQL = "legionpe.iota.query.mysql";

	const RESULT_TYPE_RAW   = 0;
	const RESULT_TYPE_ASSOC = 1;

	const QUERY_TYPE_SELECT = 0;
	const QUERY_TYPE_INSERT = 1;
	const QUERY_TYPE_UPDATE = 2;
	const QUERY_TYPE_DELETE = 3;

	const TYPE_INT      = 0;
	const TYPE_STRING   = 1;
	const TYPE_FLOAT    = 2;

	const DATA_TYPE_RAW     = 0;
	const DATA_TYPE_NUMERIC = 1;
	const DATA_TYPE_STRING  = 2;
	const DATA_TYPE_IP      = 3;
	const DATA_TYPE_FLOAT   = 4;

	/** @var BasePlugin */
	protected $plugin;
	protected $result;
	protected $callbackId;
	protected $rowCount;
	public function __construct(BasePlugin $plugin, callable $callback){
		parent::__construct(null);
		$plugin->getServer()->getScheduler()->scheduleAsyncTask($this);
		$this->callbackId = $plugin->storeObject($callback);
	}
	/**
	 * @return \mysqli
	 */
	protected function getConnection(){
		$mysql = $this->getFromThreadStore(self::KEY_MYSQL);
		if(!($mysql instanceof \mysqli)){
			$mysql = Utils::getMySQLiConnection();
			$this->saveToThreadStore(self::KEY_MYSQL, $mysql);
		}
		return $mysql;
	}
	/**
	 * @return int
	 */
	public abstract function getResultType(): int;
	/**
	 * @return int
	 */
	public abstract function getQueryType(): int;
	/**
	 * @return array
	 */
	public function getColumnTypes(): array{

	}
	/**
	 * @param $row
	 */
	protected function processRow($row){
		foreach($this->getColumnTypes() as $name => $type){
			if(isset($row[$name])){
				switch($type){
					case self::TYPE_INT:
						$row[$name] = (int) $row[$name];
						break;
					case self::TYPE_FLOAT:
						$row[$name] = (float) $row[$name];
						break;
				}
			}
		}
		return $row;
	}
	/**
	 * @param $result
	 * @param bool $processRows
	 * @param $customResult
	 */
	public function setQueryResult($result, bool $processRows = true, $customResult = null){
		$rows = 0;
		switch($this->getQueryType()){
			case self::QUERY_TYPE_SELECT:
				$rows = $result->num_rows;
				break;
			case self::QUERY_TYPE_DELETE:
			case self::QUERY_TYPE_UPDATE:
				$rows = $this->getConnection()->affected_rows;
				break;
		}
		$value = $result;
		if(!is_null($customResult)){
			$value = $customResult;
		}else{
			switch($this->getResultType()){
				case self::RESULT_TYPE_ASSOC:
					if($processRows){
						$value = $this->getProcessedRowsFromResult($result);
					}
					break;
			}
		}
		parent::setResult(
			[
				'value' => $value,
				'rows' => $rows,
				'error' => $this->getConnection()->error
			],
			true
		);
	}
	public function makeMySQLCompatible($value, int $dataType){
		$newValue = null;
		switch($dataType){
			case self::DATA_TYPE_RAW:
			case self::DATA_TYPE_NUMERIC:
				$newValue = $value;
				break;
			case self::DATA_TYPE_STRING:
				$newValue = "'" . $this->getConnection()->escape_string($value) . "'";
				break;
			case self::DATA_TYPE_IP:
				$newValue = "INET_ATON('{$value}')";
				break;
		}
		return $newValue;
	}
	public function onCompletion(Server $server){
		$plugin = BasePlugin::getInstance($server);
		$callback = $plugin->fetchObject($this->callbackId);
		$result = $this->getResult();
		$callback($result['value'], $result['rows'], $result['error']);
	}
	/**
	 * @param \mysqli_result $result
	 * @return array
	 */
	protected function getProcessedRowsFromResult(\mysqli_result $result){
		$rows = [];
		while($row = $result->fetch_assoc()){
			$rows[] = $this->processRow($row);
		}
		return $rows;
	}
}
