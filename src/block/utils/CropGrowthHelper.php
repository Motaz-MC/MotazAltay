<?php

/*
 *
 *      _    _ _
 *     / \  | | |_ __ _ _   _
 *    / _ \ | | __/ _` | | | |
 *   / ___ \| | || (_| | |_| |
 *  /_/   \_\_|\__\__,_|\__, |
 *                       |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Original work by the PocketMine Team.
 * https://www.pocketmine.net/
 *
 * @author Altay Team
 * @link https://github.com/altayofficial
 */

declare(strict_types=1);

namespace pocketmine\block\utils;

use pocketmine\block\Block;
use pocketmine\block\Farmland;
use function mt_rand;

final class CropGrowthHelper{
	private const IMPROPER_ARRANGEMENT_DIVISOR = 2;

	private function __construct(){
		//NOOP
	}

	public static function calculateMultiplier(Block $block) : float{
		$result = 1;

		$position = $block->getPosition();
		$world = $position->getWorld();
		$baseX = $position->getFloorX();
		$baseY = $position->getFloorY();
		$baseZ = $position->getFloorZ();

		$result += 3;

		$xRow = false;
		$zRow = false;
		$improperArrangement = false;

		for($x = -1; $x <= 1; $x++){
			for($z = -1; $z <= 1; $z++){
				if($x === 0 && $z === 0){
					continue;
				}
				$nextFarmland = $world->getBlockAt($baseX + $x, $baseY - 1, $baseZ + $z);

				if(!$nextFarmland instanceof Farmland){
					continue;
				}

				$result += 0.75;

				if(!$improperArrangement){
					$nextCrop = $world->getBlockAt($baseX + $x, $baseY, $baseZ + $z);
					if($nextCrop->hasSameTypeId($block)){
						match(0){
							$x => $zRow ? $improperArrangement = true : $xRow = true,
							$z => $xRow ? $improperArrangement = true : $zRow = true,
							default => $improperArrangement = true,
						};
					}
				}
			}
		}

		if($improperArrangement){
			$result /= self::IMPROPER_ARRANGEMENT_DIVISOR;
		}

		return $result;
	}


	public static function canGrow(Block $block) : bool{
		return mt_rand(0, (int) (25 / self::calculateMultiplier($block))) === 0;
	}
}
