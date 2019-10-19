<?php

declare(strict_types=1);

namespace Benda95280\MyEntities\entities\vehicle;

use pocketmine\entity\EntityIds;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;

class CarVehicle extends CustomVehicle
{
	public const NETWORK_ID = EntityIds::PLAYER;

	protected $uuid;

	protected $gravity = 0.0008;

	protected $baseOffset = 1.62;

	protected $maxTurn = 10;//maximum degrees the car can turn left/right

	public function __construct(Level $level, CompoundTag $nbt)
	{
		parent::__construct($level, $nbt);
	}

	/**
	 * This is controller axis input
	 * WASD and phone input is mapped to controller axis input
	 * @see https://docs.unity3d.com/560/Documentation/Manual/ConventionalGameInput.html
	 * @param float $xAxisInput LEFT = 1, RIGHT = -1 Yes, Minecraft got this one messed up -.- It is inverted
	 * @param float $yAxisInput UP/FORWARD = 1, DOWN/BACKWARDS = -1
	 */
	public function input(float $xAxisInput, float $yAxisInput): void
	{
		//TODO step up stairs and slabs
		$xAxisInput = -$xAxisInput;//negotiate x input so left=-1 and right=1
		$currentDirection = $this->getDirectionPlane();
		//TODO figure out if isGoingBackwards
		//Calculate turn
		if ($xAxisInput < 0) $this->yaw = ($this->yaw - $this->maxTurn) % 360;
		else if ($xAxisInput > 0) $this->yaw = ($this->yaw + $this->maxTurn) % 360;
		//Calculate speed and acceleration
		/** @var VehicleProperties $properties */
		$properties = $this->properties;
		//if controller is for example pushed half way, 0.5 of max speed is wanted as speed
		$wantedSpeed = $this->calculateInputFactor($xAxisInput, $yAxisInput) * $properties->maxSpeed;
		if ($yAxisInput < 0) $wantedSpeed *= -0.8;//go backwards, with 0.8 times the speed
		$speed = $wantedSpeed;
		/* TODO acceleration
		$acceleration = $properties->acceleration;
		$currentSpeed = $speed = (new Vector2($this->motion->x, $this->motion->z))->length();
		$f = $this->getLevel()->getBlock($this->down())->getFrictionFactor();
		if ($currentSpeed < $wantedSpeed) {
			$diff = $wantedSpeed - $currentSpeed;
			$speed = $currentSpeed + min($diff, $acceleration) * (0.98*$f);//kind of linear
		} else if ($currentSpeed > $wantedSpeed) {
			$acceleration *= 0.8;//backwards going is less speed
			$speed = $currentSpeed + ($wantedSpeed - $currentSpeed)*$acceleration * (0.98*$f);
		}
		*/
		//TODO If input is forward and we went backwards before OR if input is backwards and we went forward direction is switching, must get slower
		//todo add brake function/event
		//Set motion. Negative values result in backwards movement
		$this->setMotion($this->getDirectionVector()->multiply($speed));
	}
}