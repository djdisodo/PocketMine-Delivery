<?php
namespace delivery;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class PlayerJoinListener implements Listener {
	private $plugin;
	public function __construct(Delivery $plugin) {
		$this->plugin = $plugin;
	}
	public function onJoin(PlayerJoinEvent $e) {
		$this->plugin->onJoin($e->getPlayer());
		return true;
	}
}