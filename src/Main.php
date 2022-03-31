<?php

declare(strict_types=1);

namespace dktapps\MovementDebugger;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Human;
use pocketmine\entity\Skin;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\player\PlayerToggleGlideEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\event\player\PlayerToggleSwimEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\MainLogger;
use pocketmine\utils\TextFormat;
use pocketmine\world\particle\RedstoneParticle;
use function implode;
use function str_repeat;
use const PHP_INT_MIN;

class Main extends PluginBase implements Listener{

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	/**
	 * @return string[]
	 */
	private function stringifyPlayerAuthInputFlags(int $flags) : array{
		$result = [];
		if(($flags & (1 << PlayerAuthInputFlags::ASCEND)) !== 0){
			$result[] = "Ascend";
		}
		if(($flags & (1 << PlayerAuthInputFlags::DESCEND)) !== 0){
			$result[] = "Descend";
		}
		if(($flags & (1 << PlayerAuthInputFlags::NORTH_JUMP)) !== 0){
			$result[] = "North Jump";
		}
		if(($flags & (1 << PlayerAuthInputFlags::JUMP_DOWN)) !== 0){
			$result[] = "Jump Down";
		}
		if(($flags & (1 << PlayerAuthInputFlags::SPRINT_DOWN)) !== 0){
			$result[] = "Sprint Down";
		}
		if(($flags & (1 << PlayerAuthInputFlags::CHANGE_HEIGHT)) !== 0){
			$result[] = "Change Height";
		}
		if(($flags & (1 << PlayerAuthInputFlags::JUMPING)) !== 0){
			$result[] = "Jumping";
		}
		if(($flags & (1 << PlayerAuthInputFlags::AUTO_JUMPING_IN_WATER)) !== 0){
			$result[] = "Auto Jumping In Water";
		}
		if(($flags & (1 << PlayerAuthInputFlags::SNEAKING)) !== 0){
			$result[] = "Sneaking";
		}
		if(($flags & (1 << PlayerAuthInputFlags::SNEAK_DOWN)) !== 0){
			$result[] = "Sneak Down";
		}
		if(($flags & (1 << PlayerAuthInputFlags::UP)) !== 0){
			$result[] = "Up";
		}
		if(($flags & (1 << PlayerAuthInputFlags::DOWN)) !== 0){
			$result[] = "Down";
		}
		if(($flags & (1 << PlayerAuthInputFlags::LEFT)) !== 0){
			$result[] = "Left";
		}
		if(($flags & (1 << PlayerAuthInputFlags::RIGHT)) !== 0){
			$result[] = "Right";
		}
		if(($flags & (1 << PlayerAuthInputFlags::UP_LEFT)) !== 0){
			$result[] = "Up Left";
		}
		if(($flags & (1 << PlayerAuthInputFlags::UP_RIGHT)) !== 0){
			$result[] = "Up Right";
		}
		if(($flags & (1 << PlayerAuthInputFlags::WANT_UP)) !== 0){
			$result[] = "Want Up";
		}
		if(($flags & (1 << PlayerAuthInputFlags::WANT_DOWN)) !== 0){
			$result[] = "Want Down";
		}
		if(($flags & (1 << PlayerAuthInputFlags::WANT_DOWN_SLOW)) !== 0){
			$result[] = "Want Down Slow";
		}
		if(($flags & (1 << PlayerAuthInputFlags::WANT_UP_SLOW)) !== 0){
			$result[] = "Want Up Slow";
		}
		if(($flags & (1 << PlayerAuthInputFlags::SPRINTING)) !== 0){
			$result[] = "Sprinting";
		}
		if(($flags & (1 << PlayerAuthInputFlags::ASCEND_BLOCK)) !== 0){
			$result[] = "Ascend Block";
		}
		if(($flags & (1 << PlayerAuthInputFlags::DESCEND_BLOCK)) !== 0){
			$result[] = "Descend Block";
		}
		if(($flags & (1 << PlayerAuthInputFlags::SNEAK_TOGGLE_DOWN)) !== 0){
			$result[] = "Sneak Toggle Down";
		}
		if(($flags & (1 << PlayerAuthInputFlags::PERSIST_SNEAK)) !== 0){
			$result[] = "Persist Sneak";
		}
		if(($flags & (1 << PlayerAuthInputFlags::START_SPRINTING)) !== 0){
			$result[] = "Start Sprinting";
		}
		if(($flags & (1 << PlayerAuthInputFlags::STOP_SPRINTING)) !== 0){
			$result[] = "Stop Sprinting";
		}
		if(($flags & (1 << PlayerAuthInputFlags::START_SNEAKING)) !== 0){
			$result[] = "Start Sneaking";
		}
		if(($flags & (1 << PlayerAuthInputFlags::STOP_SNEAKING)) !== 0){
			$result[] = "Stop Sneaking";
		}
		if(($flags & (1 << PlayerAuthInputFlags::START_SWIMMING)) !== 0){
			$result[] = "Start Swimming";
		}
		if(($flags & (1 << PlayerAuthInputFlags::STOP_SWIMMING)) !== 0){
			$result[] = "Stop Swimming";
		}
		if(($flags & (1 << PlayerAuthInputFlags::START_JUMPING)) !== 0){
			$result[] = "Start Jumping";
		}
		if(($flags & (1 << PlayerAuthInputFlags::START_GLIDING)) !== 0){
			$result[] = "Start Gliding";
		}
		if(($flags & (1 << PlayerAuthInputFlags::STOP_GLIDING)) !== 0){
			$result[] = "Stop Gliding";
		}
		if(($flags & (1 << PlayerAuthInputFlags::PERFORM_ITEM_INTERACTION)) !== 0){
			$result[] = "Perform Item Interaction";
		}
		if(($flags & (1 << PlayerAuthInputFlags::PERFORM_BLOCK_ACTIONS)) !== 0){
			$result[] = "Perform Block Actions";
		}
		if(($flags & (1 << PlayerAuthInputFlags::PERFORM_ITEM_STACK_REQUEST)) !== 0){
			$result[] = "Perform Item Stack Request";
		}
		return $result;
	}

	public function onPacket(DataPacketReceiveEvent $event) : void{
		$packet = $event->getPacket();
		if($packet instanceof PlayerAuthInputPacket){
			$flags = $this->stringifyPlayerAuthInputFlags($packet->getInputFlags());
			if(count($flags) !== 0){
				$event->getOrigin()->getPlayer()?->sendTip("Tick " . $this->getServer()->getTick() . " - Input flags: " . implode(", ", $flags));
				$event->getOrigin()->getLogger()->debug("Input flags: " . implode(", ", $flags));
			}
		}
	}

	public function onSneak(PlayerToggleSneakEvent $event) : void{
		$this->getLogger()->debug($event->isSneaking() ? "start sneak" : "stop sneak");
	}

	public function onSprint(PlayerToggleSprintEvent $event) : void{
		$this->getLogger()->debug($event->isSprinting() ? "start sprint" : "stop sprint");
	}

	public function onFly(PlayerToggleFlightEvent $event) : void{
		$this->getLogger()->debug($event->isFlying() ? "start fly" : "stop fly");
	}

	public function onSwim(PlayerToggleSwimEvent $event) : void{
		$this->getLogger()->debug($event->isSwimming() ? "start swim" : "stop swim");
	}

	public function onJump(PlayerJumpEvent $event) : void{
		$this->getLogger()->debug("jump");
	}

	public function onGlide(PlayerToggleGlideEvent $event) : void{
		$this->getLogger()->debug($event->isGliding() ? "start glide" : "stop glide");
	}

	public function onMove(PlayerMoveEvent $event) : void{
		if($event->getPlayer()->isFlying() || $event->getFrom()->distanceSquared($event->getTo()) < 0.0001){
			return;
		}
		if($event->getPlayer()->isOnGround()){
			$this->getLogger()->debug("Detected touchdown");
		}
		$skin = $event->getPlayer()->isOnGround() ? new Skin("Standard_Custom", str_repeat("\xff\x00\x00\xff", 2048)) : $event->getPlayer()->getSkin();
		$mob = new Human($event->getTo(), $skin);
		$mob->setHasGravity(false);
		$mob->setCanSaveWithChunk(false);
		$yDiff = -10.0;
		$mobBox = $mob->getBoundingBox();
		foreach($mob->getWorld()->getCollisionBoxes($mob, $mobBox->addCoord(0, $yDiff, 0), false) as $box){
			$yDiff = $box->calculateYOffset($mobBox, $yDiff);
		}
		$pos = $mob->getPosition();
		$nameTag = "Pos: $pos->x, $pos->y, $pos->z";
		$diff = $event->getTo()->subtractVector($event->getFrom());
		$nameTag .= "\nDiff: $diff->x, $diff->y, $diff->z";
		if($yDiff > -10.0){
			$nameTag .= "\nDistance from ground: " . $yDiff;
		}
		$mob->setNameTag($nameTag);
		$mob->setScale(0.3);
		$mob->spawnToAll();
	}
}
