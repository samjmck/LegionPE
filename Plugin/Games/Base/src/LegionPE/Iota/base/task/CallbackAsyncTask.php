<?php

namespace LegionPE\Iota\base\task;

use LegionPE\Iota\base\BasePlugin;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

abstract class CallbackAsyncTask extends AsyncTask{
	/** @var BasePlugin */
	private $plugin;
	/** @var int */
	private $callbackId;
	public function __construct(BasePlugin $plugin, callable $callback){
		parent::__construct();
		$plugin->getServer()->getScheduler()->scheduleAsyncTask($this);
		$this->callbackId = $plugin->storeObject($callback);
	}
	public function onCompletion(Server $server){
		$plugin = BasePlugin::getInstance($server);
		$callback = $plugin->fetchObject($this->callbackId);
		$callback($this->getResult());
	}
}
