<?php

declare(strict_types=1);

namespace appgallery\permissionEffects\object;

use appgallery\permissionEffects\PermissionEffects;
use JsonSerializable;
use pocketmine\color\Color;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\utils\Limits;
use RuntimeException;

class EffectInfo implements JsonSerializable{

	private EffectInstance $effect;

	public function __construct(private readonly array $data, private readonly PermissionEffects $plugin){
		$effect = StringToEffectParser::getInstance()->parse($this->data['effectName']);
		if($effect === null){
			throw new RuntimeException('Effect not found!');
		}
		$color = null;
		if(!empty($this->data['overrideColor'])){
			$color = new Color(...$this->data['overrideColor']);
		}

		$this->effect = new EffectInstance($effect, Limits::INT32_MAX, $this->getAmplifier(), $this->data['visibleBubbles'], $this->data['ambient'], $color);
		PermissionManager::getInstance()->getPermission('permissionEffects.*')?->addChild($this->getPermission(), true);
	}

	public function getEffect(): EffectInstance{
		return clone $this->effect;
	}

	public function getAmplifier(): int{
		return $this->data['amplifier'];
	}

	public function getPermission(): string{
		return 'permissionEffects.' . $this->data['effectName'];
	}

	public function isAllowedWorld(string $worldName): bool{
		return in_array($worldName, $this->data['allowedWorlds'], true);
	}

	protected function tryActivate(Player $player): bool{
		return $player->hasPermission($this->getPermission());
	}

	public function activate(Player $player): void{
		if(!$this->tryActivate($player)){
			return;
		}

		$player->getEffects()->add($this->getEffect());
		$player->sendMessage($this->plugin->translate('playerEffectsGiven'));
	}

	public function JsonSerialize(): array{
		return $this->data;
	}
}