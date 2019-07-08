<?php

namespace LegionPE\Iota\base;

use LegionPE\Iota\base\event\session\SessionLoginEvent;
use LegionPE\Iota\base\event\session\SessionLogoutEvent;
use LegionPE\Iota\base\event\session\SessionRegisterEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBedLeaveEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\Player;

class EventListener implements Listener{
	/** @var BasePlugin */
	private $plugin;
	/**
	 * @param BasePlugin $plugin
	 */
	public function __construct(BasePlugin $plugin){
		$this->plugin = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}
	/**
	 * @param PlayerPreLoginEvent $event
	 */
	public function PlayerPreLoginEvent(PlayerPreLoginEvent $event){
		$session = $this->plugin->createSession($this->plugin, $event->getPlayer());
		$session->PlayerPreLoginEvent($event);
	}
	/**
	 * @param PlayerLoginEvent $event
	 */
	public function PlayerLoginEvent(PlayerLoginEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			$session->PlayerLoginEvent($event);
		}
	}
	/**
	 * @param PlayerJoinEvent $event
	 */
	public function PlayerJoinEvent(PlayerJoinEvent $event){
		$event->setJoinMessage("");
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			$session->PlayerJoinEvent($event);
		}
	}
	/**
	 * @param PlayerQuitEvent $event
	 */
	public function PlayerQuitEvent(PlayerQuitEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			$session->PlayerQuitEvent($event);
		}
	}
	/**
	 * @param PlayerChatEvent $event
	 */
	public function PlayerChatEvent(PlayerChatEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			if($session->PlayerChatEvent($event) === false){
				$event->setCancelled();
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param PlayerKickEvent $event
	 */
	public function PlayerKickEvent(PlayerKickEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			$session->PlayerKickEvent($event);
		}
	}
	/**
	 * @param EntityDamageEvent $event
	 */
	public function EntityDamageEvent(EntityDamageEvent $event){
		if(($player = $event->getEntity()) instanceof Player){
			if(($session = $this->plugin->findSession($player)) instanceof BaseSession){
				if($session->EntityDamageEvent($event) === false){
					$event->setCancelled();
				}
			}else{
				$event->setCancelled();
			}
		}
	}
	/**
	 * @param PlayerDeathEvent $event
	 */
	public function PlayerDeathEvent(PlayerDeathEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			$session->PlayerDeathEvent($event);
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param PlayerDropItemEvent $event
	 */
	public function PlayerDropItemEvent(PlayerDropItemEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			if($session->PlayerDropItemEvent($event) === false){
				$event->setCancelled();
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param PlayerInteractEvent $event
	 */
	public function PlayerInteractEvent(PlayerInteractEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			if($session->PlayerInteractEvent($event) === false){
				$event->setCancelled();
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param PlayerItemConsumeEvent $event
	 */
	public function PlayerItemConsumeEvent(PlayerItemConsumeEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			if($session->PlayerItemConsumeEvent($event) === false){
				$event->setCancelled();
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param PlayerItemHeldEvent $event
	 */
	public function PlayerItemHeldEvent(PlayerItemHeldEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			if($session->PlayerItemHeldEvent($event) === false){
				$event->setCancelled();
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param PlayerMoveEvent $event
	 */
	public function PlayerMoveEvent(PlayerMoveEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			if($session->PlayerMoveEvent($event) === false){
				$event->setCancelled();
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param PlayerRespawnEvent $event
	 */
	public function PlayerRespawnEvent(PlayerRespawnEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			$session->PlayerRespawnEvent($event);
		}
	}
	/**
	 * @param PlayerToggleSneakEvent $event
	 */
	public function PlayerToggleSneakEvent(PlayerToggleSneakEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			if($session->PlayerToggleSneakEvent($event) === false){
				$event->setCancelled();
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param PlayerToggleSprintEvent $event
	 */
	public function PlayerToggleSprintEvent(PlayerToggleSprintEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			if($session->PlayerToggleSprintEvent($event) === false){
				$event->setCancelled();
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param PlayerBedEnterEvent $event
	 */
	public function PlayerBedEnterEvent(PlayerBedEnterEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			if($session->PlayerBedEnterEvent($event) === false){
				$event->setCancelled();
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param PlayerBedLeaveEvent $event
	 */
	public function PlayerBedLeaveEvent(PlayerBedLeaveEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			if($session->PlayerBedLeaveEvent($event) === false){
				$event->setCancelled();
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param PlayerGameModeChangeEvent $event
	 */
	public function PlayerGameModeChangeEvent(PlayerGameModeChangeEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			if($session->PlayerGameModeChangeEvent($event) === false){
				$event->setCancelled();
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param BlockBreakEvent $event
	 */
	public function BlockBreakEvent(BlockBreakEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			if($session->BlockBreakEvent($event) === false){
				$event->setCancelled();
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param BlockPlaceEvent $event
	 */
	public function BlockPlaceEvent(BlockPlaceEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			if($session->BlockPlaceEvent($event) === false){
				$event->setCancelled();
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param SignChangeEvent $event
	 */
	public function SignChangeEvent(SignChangeEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			if($session->SignChangeEvent($event) === false){
				$event->setCancelled();
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param PlayerCommandPreprocessEvent $event
	 */
	public function PlayerCommandPreprocessEvent(PlayerCommandPreprocessEvent $event){
		if(($session = $this->plugin->findSession($event->getPlayer())) instanceof BaseSession){
			if($session->PlayerCommandPreprocessEvent($event) === false){
				$event->setCancelled();
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param DataPacketReceiveEvent $event
	 */
	public function DataPacketReceiveEvent(DataPacketReceiveEvent $event){
		if($event->getPacket() instanceof LoginPacket){
			// ClientData -> CurrentInputMode
			// ClientData -> DeviceOS
			var_dump($event->getPacket());
		}
	}
	/**
	 * @param DataPacketSendEvent $event
	 */
	public function DataPacketSendEvent(DataPacketSendEvent $event){

	}
	/**
	 * @param QueryRegenerateEvent $event
	 */
	public function QueryRegenerateEvent(QueryRegenerateEvent $event){

	}
	/**
	 * @param EntityInventoryChangeEvent $event
	 */
	public function EntityInventoryChangeEvent(EntityInventoryChangeEvent $event){
		if(($player = $event->getEntity()) instanceof Player){
			if(($session = $this->plugin->findSession($player)) instanceof BaseSession){
				if($session->EntityInventoryChangeEvent($event) === false){
					$event->setCancelled();
				}
			}
		}
	}
	/**
	 * @param EntityLevelChangeEvent $event
	 */
	public function EntityLevelChangeEvent(EntityLevelChangeEvent $event){
		if(($player = $event->getEntity()) instanceof Player){
			if(($session = $this->plugin->findSession($player)) instanceof BaseSession){
				if($session->EntityLevelChangeEvent($event) === false){
					$event->setCancelled();
				}
			}
		}
	}
	/**
	 * @param EntityRegainHealthEvent $event
	 */
	public function EntityRegainHealthEvent(EntityRegainHealthEvent $event){
		if(($player = $event->getEntity()) instanceof Player){
			if(($session = $this->plugin->findSession($player)) instanceof BaseSession){
				if($session->EntityRegainHealthEvent($event) === false){
					$event->setCancelled();
				}
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param EntityShootBowEvent $event
	 */
	public function EntityShootBowEvent(EntityShootBowEvent $event){
		if(($player = $event->getEntity()) instanceof Player){
			if(($session = $this->plugin->findSession($player)) instanceof BaseSession){
				if($session->EntityShootBowEvent($event) === false){
					$event->setCancelled();
				}
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param EntityTeleportEvent $event
	 */
	public function EntityTeleportEvent(EntityTeleportEvent $event){
		if(($player = $event->getEntity()) instanceof Player){
			if(($session = $this->plugin->findSession($player)) instanceof BaseSession){
				if($session->EntityTeleportEvent($event) === false){
					$event->setCancelled();
				}
			}
		}else{
			$event->setCancelled();
		}
	}
	/**
	 * @param SessionLoginEvent $event
	 */
	public function SessionLoginEvent(SessionLoginEvent $event){
		$event->getSession()->SessionLoginEvent($event);
	}
	/**
	 * @param SessionRegisterEvent $event
	 */
	public function SessionRegisterEvent(SessionRegisterEvent $event){
		$event->getSession()->SessionRegisterEvent($event);
	}
	/**
	 * @param SessionLogoutEvent $event
	 */
	public function SessionLogoutEvent(SessionLogoutEvent $event){
		$event->getSession()->SessionLogoutEvent($event);
	}

}
