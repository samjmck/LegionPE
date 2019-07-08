<?php

namespace LegionPE\Iota\base\event;

use LegionPE\Iota\base\BasePlugin;
use pocketmine\event\plugin\PluginEvent;

abstract class BaseEvent extends PluginEvent{
	/**
	 * @param BasePlugin $plugin
	 */
	public function __construct(BasePlugin $plugin){
		parent::__construct($plugin);
	}
}
