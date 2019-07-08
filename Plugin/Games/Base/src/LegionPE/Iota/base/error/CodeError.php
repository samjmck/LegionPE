<?php

namespace LegionPE\Iota\base\error;

class CodeError{
	/** @var int */
	private $code;
	/**
	 * @param int $code
	 */
	public function __construct(int $code){
		$this->code = $code;
	}
	/**
	 * @return int
	 */
	public function getCode(): int{
		return $this->code;
	}
}
