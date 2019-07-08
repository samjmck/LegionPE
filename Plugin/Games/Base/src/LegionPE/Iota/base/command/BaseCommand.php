<?php

namespace LegionPE\Iota\base\command;

use LegionPE\Iota\base\BasePlugin;
use LegionPE\Iota\base\BaseSession;
use LegionPE\Iota\base\command\session\FriendCommand;
use LegionPE\Iota\base\command\session\TeamCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandMap;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

abstract class BaseCommand extends Command implements PluginIdentifiableCommand{
	/** @var BasePlugin */
	protected $plugin;
	/** @var string[] */
	private $descriptions = [];
	/** @var string[] */
	private $usages = [];
	/** @var array */
	private $arguments = [];
	/** @var array */
	private $argumentsPermissions = [];
	/** @var array */
	private $argumentsCustomPermissions = [];
	/**
	 * @param BasePlugin $plugin
	 * @param array $data
	 */
	public function __construct(BasePlugin $plugin, array $data){
		parent::__construct($data['en']['name'], $data['en']['description'], $data['en']['usage'], []);
		$this->plugin = $plugin;
		$aliases = [];
		foreach($data as $languageCode => $info){
			if($languageCode != 'en') $aliases[] = $info['name'];
			foreach($info['aliases'] as $alias){
				$aliases[] = $alias;
			}
			$this->setCustomDescription($languageCode, $info['description']);
			$this->setCustomUsage($languageCode, $info['usage']);
			if(isset($data['en']['permissions'])){
				$this->setArgumentsPermissions($data['en']['permissions']);
			}
			if(isset($data['en']['customPermissions'])){
				$this->setArgumentsCustomPermissions($data['en']['customPermissions']);
			}
			foreach($info['arguments'] as $key => $argumentInfo){
				$this->addArgument($languageCode, $key, $argumentInfo['name'], $argumentInfo['usage'], $argumentInfo['description'], (array_key_exists('aliases', $argumentInfo) ? $argumentInfo['aliases'] : []));
			}
		}
		$this->setAliases($aliases);
	}
	/**
	 * @param $permissions
	 */
	public function setArgumentsPermissions(int $permissions){
		$this->argumentsPermissions = $permissions;
	}
	/**
	 * @param $permissions
	 */
	public function setArgumentsCustomPermissions(int $permissions){
		$this->argumentsCustomPermissions = $permissions;
	}
	/**
	 * @param BaseSession $session
	 * @param string $argumentMain
	 * @return bool
	 */
	public function hasArgumentPermission(BaseSession $session, string $argumentMain): bool{
		foreach($this->argumentsPermissions[$argumentMain] as $argumentPermissionValue){
			if($session->getRank() & $argumentPermissionValue){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param string $argumentMain
	 * @param int $permissionValue
	 * @return bool
	 */
	public function hasArgumentCustomPermission(string $argumentMain, int $permissionValue): bool{
		foreach($this->argumentsCustomPermissions[$argumentMain] as $argumentPermissionValue){
			if($permissionValue & $argumentPermissionValue){
				return true;
			}
		}
		return false;
	}
	/**
	 * @param string $argumentMain
	 * @return array
	 */
	public function getArgumentPermissions(string $argumentMain): array{
		return $this->argumentsPermissions[$argumentMain];
	}
	/**
	 * @param string $languageCode
	 * @param string $key
	 * @param string $argument
	 * @param string $usage
	 * @param string $description
	 * @param array $aliases
	 */
	private function addArgument(string $languageCode, string $key, string $argument, string $usage, string $description, array $aliases = []){
		if(!isset($this->arguments[$languageCode])){
			$this->arguments[$languageCode] = [];
		}
		$this->arguments[$languageCode][$argument] = [
			'main' => $key,
			'usage' => $usage,
			'description' => $description
		];
		foreach($aliases as $alias){
			$this->arguments[$languageCode][$alias] = $this->arguments[$languageCode][$argument];
			$this->arguments[$languageCode][$argument]['isAlias'] = true;
		}
	}
	/**
	 * @param string $languageCode
	 * @param string $argument
	 * @return mixed
	 */
	public function getArgument(string $languageCode, string $argument){
		return array_key_exists($argument, $this->arguments[$languageCode]) ? $this->arguments[$languageCode][$argument] : false;
	}
	/**
	 * @param string $languageCode
	 * @return array
	 */
	public function getArguments(string $languageCode): array{
		$arguments = [];
		foreach($this->arguments[$languageCode] as $name => $argument){
			if(!isset($argument['isAlias'])){
				$arguments[$argument['main']] = [
					'name' => $name,
					'usage' => $argument['usage'],
					'description' => $argument['description']
				];
			}
		}
		return $arguments;
	}
	/**
	 * @param string $languageCode
	 * @param string $description
	 */
	public function setCustomDescription(string $languageCode, string $description){
		$this->descriptions[$languageCode] = $description;
	}
	/**
	 * @param string $languageCode
	 * @return string
	 */
	public function getCustomDescription(string $languageCode): string{
		return $this->descriptions[$languageCode];
	}
	/**
	 * @param string $languageCode
	 * @param string $usage
	 */
	public function setCustomUsage(string $languageCode, string $usage){
		$this->usages[$languageCode] = $usage;
	}
	/**
	 * @param $languageCode
	 * @return string
	 */
	public function getCustomUsage(string $languageCode): string{
		return $this->usages[$languageCode];
	}
	/**
	 * @param BasePlugin $main
	 * @param CommandMap $map
	 */
	public static function registerAll(BasePlugin $main, CommandMap $map){
		foreach(
			[
				"version",
				"tell",
				"w",
				"msg",
				"defaultgamemode",
				"ban",
				"ban-ip",
				"banlist",
				"pardon",
				"pardon-ip",
				"say",
				"me",
				"difficulty",
				"kick",
				"op",
				"deop",
				"whitelist",
				"save-on",
				"save-off",
				"save-all",
				"spawnpoint",
				"setworldspawn",
				"tp",
				"reload",
				"status",
				"kill",
			] as $cmd){
			self::unregisterCommand($map, $cmd);
		}
		$resourceManager = $main->getResourceManager();
		$map->registerAll("legionpe", [
			new TeamCommand($main, $resourceManager->getCommandData('team')),
			new FriendCommand($main, $resourceManager->getCommandData('friend'))
		]);
	}
	/**
	 * @param CommandMap $map
	 * @param string $name
	 * @return bool
	 */
	private static function unregisterCommand(CommandMap $map, string $name): bool{
		$cmd = $map->getCommand($name);
		if($cmd instanceof Command){
			$cmd->setLabel($name . "_deprecated");
			$cmd->unregister($map);
			return true;
		}
		return false;
	}
	/**
	 * @return BasePlugin
	 */
	public function getPlugin(): Plugin{
		return $this->plugin;
	}
	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param array $args
	 * @return bool
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): bool{
		if(!($sender instanceof Player)){
			return false;
		}
		if(!(($session = $this->getPlugin()->getAuthenticatedSession($sender)) instanceof BaseSession)){
			return false;
		}
		if(is_string(($message = $this->run($session, $args))) and $message !== "") $sender->sendMessage($message);
	}
	/**
	 * @param BaseSession $session
	 * @param array $args
	 */
	protected abstract function run(BaseSession $session, array $args);
}
