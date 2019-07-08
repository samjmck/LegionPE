<?php

namespace LegionPE\Iota\base;

class AdminLog{
	/** @var int */
	private $id;
	/** @var int */
	private $uid;
	private $uuid;
	/** @var string */
	private $ip;
	/** @var int */
	private $creationTime;
	/** @var int */
	private $type;
	/** @var string */
	private $message;
	/** @var int */
	private $fromUid;
	/** @var string */
	private $fromIp;
	/** @var int */
	private $duration;
	/** @var int */
	private $ipSensitive;
	/** @var int */
	private $uuidSensitive;
	public function __construct(int $id, int $uid, $uuid, string $ip, int $creationTime, int $type, string $message, int $fromUid, string $fromIp, int $duration, int $ipSensitive, int $uuidSensitive){
		$this->id = $id;
		$this->uid = $uid;
		$this->uuid = $uuid;
		$this->ip = $ip;
		$this->creationTime = $creationTime;
		$this->type = $type;
		$this->message = $message;
		$this->fromUid = $fromUid;
		$this->fromIp = $fromIp;
		$this->duration = $duration;
		$this->ipSensitive = $ipSensitive;
		$this->uuidSensitive = $uuidSensitive;
	}
	/**
	 * @return int
	 */
	public function getId(): int{
		return $this->id;
	}
	/**
	 * @return int
	 */
	public function getUid(): int{
		return $this->uid;
	}
	/**
	 * @return mixed
	 */
	public function getUuid(){
		return $this->uuid;
	}
	/**
	 * @return string
	 */
	public function getIp(): string{
		return $this->ip;
	}
	/**
	 * @return int
	 */
	public function getCreationTime(): int{
		return $this->creationTime;
	}
	/**
	 * @return int
	 */
	public function getType(): int{
		return $this->type;
	}
	/**
	 * @return string
	 */
	public function getMessage(): string{
		return $this->message;
	}
	/**
	 * @return int
	 */
	public function getFromUid(): int{
		return $this->fromUid;
	}
	/**
	 * @return string
	 */
	public function getFromIp(): string{
		return $this->fromIp;
	}
	/**
	 * @return int
	 */
	public function getDuration(): int{
		return $this->duration;
	}
	/**
	 * @return bool
	 */
	public function isUuidSensitive(): bool{
		return (bool) $this->uuidSensitive;
	}
	/**
	 * @return bool
	 */
	public function isIpSensitive(): bool{
		return (bool) $this->ipSensitive;
	}
}
