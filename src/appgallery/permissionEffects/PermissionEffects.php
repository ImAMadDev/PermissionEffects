<?php

declare(strict_types=1);

namespace appgallery\permissionEffects;

use appgallery\permissionEffects\object\EffectInfo;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

final class PermissionEffects extends PluginBase{

	/** @var EffectInfo[] */
    private array $permissionEffects = [];
	private Config $config;

	protected function onLoad(): void{
        $this->saveResource('config.json', true);
    }
    
    protected function onEnable(): void{
		$this->config = new Config($this->getDataFolder() . "config.json", Config::JSON);
        foreach ($this->config->get('effects', []) as $effectData){
            $this->permissionEffects[] = new EffectInfo($effectData, $this);
        }

		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

	public function getConfig(): Config{
		return $this->config;
	}

	public function translate(string $key): string{
		return TextFormat::colorize($this->config->get('messages')[$key] ?? "&cUnknown message effect: " . $key);
	}

	/**
	 * @return EffectInfo[]
	 */
	public function getPermissionEffects(): array{
		return $this->permissionEffects;
	}
}

