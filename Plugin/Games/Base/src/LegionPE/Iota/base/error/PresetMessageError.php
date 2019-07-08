<?php

namespace LegionPE\Iota\base;

class PresetMessageError{
	/** @var string */
	private $presetMessageKey;
	/** @var int */
	private $code;
	/**
	 * @param string $presetMessageKey
	 * @param int $code
	 */
	public function __construct(string $presetMessageKey, int $code){
		$this->presetMessageKey = $presetMessageKey;
		$this->code = $code;
	}
	/**
	 * @return string
	 */
	public function getPresetMessageKey(): stirng{
		return $this->presetMessageKey;
	}
	/**
	 * @param string $languageCode
	 * @return string
	 */
	public function getPresetMessage(BasePlugin $plugin, string $languageCode): string{
		return $plugin->getResourceManager()->getMessage($languageCode, $this->getPresetMessageKey());
	}
	/**
	 * @return int
	 */
	public function getCode(): int{
		return $this->code;
	}
}
