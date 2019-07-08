<?php

namespace LegionPE\Iota\base\task;

use LegionPE\Iota\base\BasePlugin;

class ValidatePasswordTask extends CallbackAsyncTask{
	/** @var string */
	private $password;
	/** @var string */
	private $hash;
	public function __construct(BasePlugin $plugin, callable $callback, string $password, string $hash){
		parent::__construct($plugin, $callback);
		$this->password = $password;
		$this->hash = $hash;
	}
	public function onRun(){
		$verified = password_verify($this->password, $this->hash);
		$this->setResult($verified);
	}
}
