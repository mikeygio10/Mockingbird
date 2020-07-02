<?php

namespace ethaniccc\Mockingbird\cheat\movement;

use ethaniccc\Mockingbird\Mockingbird;
use ethaniccc\Mockingbird\cheat\Cheat;
use ethaniccc\Mockingbird\utils\LevelUtils;
use pocketmine\block\BlockIds;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\Player;

class NoWeb extends Cheat{

    private $jumped = [];

    public function __construct(Mockingbird $plugin, string $cheatName, string $cheatType, bool $enabled = true){
        parent::__construct($plugin, $cheatName, $cheatType, $enabled);
    }

    public function onMove(PlayerMoveEvent $event) : void{
        $player = $event->getPlayer();
        $name = $player->getName();

        if($this->hasRecentlyJumped($player)) return;

        $level = $player->getLevel();
        $position = $player->asVector3();
        $blocksAround = [
            $level->getBlock($position),
            $level->getBlock($position->add(0, 1, 0)),
            $level->getBlock($position->subtract(0, 1, 0)),
            $level->getBlock($position->add(0, 2, 0)),
            $level->getBlock($position->subtract(0, 2, 0))
        ];
        $continue = false;
        foreach($blocksAround as $block) if($block->getId() === BlockIds::COBWEB) $continue = true;
        if($continue){
            $from = $event->getFrom();
            $to = $event->getTo();

            $distX = ($to->x - $from->x);
            $distZ = ($to->z - $from->z);

            $distanceSquared = abs(($distX * $distX) + ($distZ * $distZ));
            $distance = sqrt($distanceSquared);

            $expectedDistance = 0.115;
            if($player->getEffect(1) !== null){
                $expectedDistance *= (4 / 3) * $player->getEffect(1)->getEffectLevel();
            }
            if($distance > $expectedDistance){
                $this->addViolation($name);
                $this->notifyStaff($name, $this->getName(), $this->genericAlertData($player));
            }
        }
    }

    public function onJump(PlayerJumpEvent $event) : void{
        $name = $event->getPlayer()->getName();
        $this->jumped[$name] = $this->getServer()->getTick();
    }

    private function hasRecentlyJumped(Player $player) : bool{
        return isset($this->jumped[$player->getName()]) ? $this->getServer()->getTick() - $this->jumped[$player->getName()] <= 10 : false;
    }

}