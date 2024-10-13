<?php

declare(strict_types=1);

namespace appgallery\permissionEffects;

use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\item\MilkBucket;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;

class EventListener implements Listener{

	public function __construct(
		private readonly PermissionEffects $plugin
	){}

	/**
	 * @param PlayerJoinEvent $event
	 * @priority HIGH
	 * @return void
	 */
	public function handleJoin(PlayerJoinEvent $event): void{
		$this->applyEffects($event->getPlayer());
	}

	/**
	 * @param PlayerRespawnEvent $event
	 * @priority HIGH
	 * @return void
	 */
	public function handleRespawn(PlayerRespawnEvent $event): void{
		$player = $event->getPlayer();

		$this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function()use($player){
			if(!$player->isConnected())
				return;

			$this->applyEffects($player);
		}), 20*3);
	}

	/**
	 * @param EntityTeleportEvent $event
	 * @priority HIGH
	 * @return void
	 */
	public function handleTeleport(EntityTeleportEvent $event): void{
		$player = $event->getEntity();
		if(!($player instanceof Player))
			return;

		if($event->getTo()->getWorld() === $event->getFrom()->getWorld())
			return;

		$this->applyEffects($player);
	}

	/**
	 * @param PlayerItemConsumeEvent $event
	 * @priority HIGH
	 * @return void
	 */
	public function handleMilk(PlayerItemConsumeEvent $event): void{
		$player = $event->getPlayer();
		if($event->getItem() instanceof MilkBucket){
			$this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function()use($player){
				if(!$player->isConnected())
					return;

				$this->applyEffects($player);
			}), 20*3);
		}
	}

	private function applyEffects(Player $player): void{
		foreach ($this->plugin->getPermissionEffects() as $permissionEffect){
			if(!$permissionEffect->isAllowedWorld($player->getWorld()->getFolderName())){
				$player->getEffects()->remove($permissionEffect->getEffect()->getType());
			}

			$permissionEffect->activate($player);
		}
	}

}