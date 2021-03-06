<?php

namespace ethaniccc\Mockingbird\cheat\movement\velocity;

use ethaniccc\Mockingbird\cheat\Cheat;
use ethaniccc\Mockingbird\event\MoveEvent;
use ethaniccc\Mockingbird\Mockingbird;
use ethaniccc\Mockingbird\utils\MathUtils;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\Player;

class VelocityB extends Cheat{

    private $lastX, $lastZ, $moveDelta, $ticksSinceSend = [];

    public function __construct(Mockingbird $plugin, string $cheatName, string $cheatType, bool $enabled){
        parent::__construct($plugin, $cheatName, $cheatType, $enabled);
    }

    public function onMotion(EntityMotionEvent $event) : void{
        $entity = $event->getEntity();
        if($entity instanceof Player){
            $name = $entity->getName();
            $motion = $event->getVector();
            $this->lastX[$name] = $motion->x;
            $this->lastZ[$name] = $motion->z;
            $this->moveDelta[$name] = $this->getPlugin()->getUserManager()->get($entity)->getMoveDelta();
            $this->ticksSinceSend[$name] = 0;
            $this->lowerPreVL($name, 0);
        }
    }

    public function onMove(MoveEvent $event) : void{
        $player = $event->getPlayer();
        $user = $this->getPlugin()->getUserManager()->get($player);
        $name = $player->getName();
        $attacked = isset($this->ticksSinceSend[$name]) && $player->isAlive();
        if($attacked){
            if(in_array($event->getMode(), [MoveEvent::MODE_TELEPORT, MoveEvent::MODE_RESET])){
                unset($this->lastX[$name]);
                unset($this->lastZ[$name]);
                unset($this->moveDelta[$name]);
                unset($this->ticksSinceSend[$name]);
                return;
            }
            ++$this->ticksSinceSend[$name];
            $maxTicks = (int) ($player->getPing() / 50)  + 5;
            $xDelta = $event->getDistanceX();
            $zDelta = $event->getDistanceZ();
            $expectedXDelta = $this->lastX[$name];
            $expectedZDelta = $this->lastZ[$name];
            if($this->ticksSinceSend[$name] <= $maxTicks){
                $previousMoveDelta = clone $this->moveDelta[$name];
                // TODO: I hate it here
            } else {
                if($this->getPreVL($name) >= $maxTicks){
                    $this->fail($player, "$name's horizontal velocity is less than normal.");
                }
                $this->lowerPreVL($name, 0);
                unset($this->lastX[$name]);
                unset($this->lastZ[$name]);
                unset($this->moveDelta[$name]);
                unset($this->ticksSinceSend[$name]);
            }
        } else {
            $this->lowerPreVL($name, 0);
        }
    }

}