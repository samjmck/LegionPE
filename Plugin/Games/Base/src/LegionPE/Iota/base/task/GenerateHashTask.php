<?php

namespace LegionPE\Iota\base\task;

use LegionPE\Iota\base\BasePlugin;

class GenerateHashTask extends CallbackAsyncTask{
	/** @var string */
	private $password;
	/** @var string */
	private $hash;
	public function __construct(BasePlugin $plugin, callable $callback, string $password){
		parent::__construct($plugin, $callback);
		$this->password = $password;
	}
	public function onRun(){
		$hash = password_hash($this->password, PASSWORD_BCRYPT, ['cost' => 12]);
		$this->setResult($hash);
	}
}
