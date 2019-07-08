<?php

namespace LegionPE\Iota\base;

class ResourceManager{
	/** @var string[] */
	private $messages = [];
	/** @var array */
	private $messagesArray = [];
	/**
	 * @param BasePlugin $plugin
	 */
	public function __construct(BasePlugin $plugin){
		$messages = stream_get_contents(($stream = $plugin->getResource('messages.json')));
		fclose($stream);
		$jsonArray = json_decode($messages, true);
		$this->messagesArray = $jsonArray;
		$this->messages = $this->collapse($jsonArray);
	}
	/**
	 * @param string $languageCode
	 * @return array
	 */
	public function getLanguageArray(string $languageCode): array{
		return $this->messagesArray[$languageCode];
	}
	/**
	 * @param string $languageCode
	 * @param string $key
	 * @param string[] $search
	 * @param string[] $replace
	 * @return string
	 */
	public function getMessage(string $languageCode, string $key, array $search = [], array $replace = []): string{
		$message = $this->messages["{$languageCode}.{$key}"];
		if(count($search) === 0 and count($replace) === 0){
			return $message;
		}
		return str_replace($search, $replace, $message);
	}
	/**
	 * @param string $command
	 * @return array
	 */
	public function getCommandData(string $command): array{
		$data = [];
		foreach($this->messagesArray as $languageCode => $commandData){
			$data[$languageCode] = $commandData['commands'][$command];
		}
		return $data;
	}
	/**
	 * @param array $array
	 * @return string[]
	 */
	private function collapse(array $array): array{
		$result = array();
		foreach($array as $key => $val){
			if(is_array($val)){
				foreach($this->collapse($val) as $nested_key => $nested_val){
					$result[$key . '.' . $nested_key] = $nested_val;
				}
			}else{
				$result[$key] = $val;
			}
		}
		return $result;
	}
}
