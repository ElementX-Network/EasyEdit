<?php

namespace platz1de\EasyEdit\task\selection;

use platz1de\EasyEdit\pattern\Pattern;
use platz1de\EasyEdit\selection\BlockListSelection;
use platz1de\EasyEdit\selection\DynamicBlockListSelection;
use platz1de\EasyEdit\selection\Selection;
use platz1de\EasyEdit\selection\StaticBlockListSelection;
use platz1de\EasyEdit\task\EditTask;
use pocketmine\level\Position;
use pocketmine\level\utils\SubChunkIteratorManager;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;

class PasteTask extends EditTask
{
	/**
	 * PasteTask constructor.
	 * @param BlockListSelection $selection
	 * @param Position                  $place
	 */
	public function __construct(BlockListSelection $selection, Position $place)
	{
		parent::__construct($selection, new Pattern([], []), $place);
	}

	/**
	 * @return string
	 */
	public function getTaskName(): string
	{
		return "paste";
	}

	/**
	 * @param SubChunkIteratorManager $iterator
	 * @param CompoundTag[]           $tiles
	 * @param Selection               $selection
	 * @param Pattern                 $pattern
	 * @param Vector3                 $place
	 * @param BlockListSelection      $toUndo
	 */
	public function execute(SubChunkIteratorManager $iterator, array &$tiles, Selection $selection, Pattern $pattern, Vector3 $place, BlockListSelection $toUndo): void
	{
		/** @var DynamicBlockListSelection $selection */
		$place = $place->subtract($selection->getPoint());
		for ($x = 0; $x <= $selection->getXSize(); $x++) {
			for ($z = 0; $z <= $selection->getZSize(); $z++) {
				for ($y = 0; $y <= $selection->getYSize(); $y++) {
					$selection->getIterator()->moveTo($x, $y, $z);
					$blockId = $selection->getIterator()->currentSubChunk->getBlockId($x & 0x0f, $y & 0x0f, $z & 0x0f);
					if ($blockId !== 0) {
						if ($blockId === 217) {
							$blockId = 0;
						}
						$iterator->moveTo($x + $place->getX(), $y + $place->getY(), $z + $place->getZ());
						$toUndo->addBlock($x + $place->getX(), $y + $place->getY(), $z + $place->getZ(), $iterator->currentSubChunk->getBlockId($x + $place->getX() & 0x0f, $y + $place->getY() & 0x0f, $z + $place->getZ() & 0x0f), $iterator->currentSubChunk->getBlockData($x + $place->getX() & 0x0f, $y + $place->getY() & 0x0f, $z + $place->getZ() & 0x0f));
						$iterator->currentSubChunk->setBlock(($x + $place->getX()) & 0x0f, ($y + $place->getY()) & 0x0f, ($z + $place->getZ()) & 0x0f, $blockId, $selection->getIterator()->currentSubChunk->getBlockData($x & 0x0f, $y & 0x0f, $z & 0x0f));
					}
				}
			}
		}
	}

	/**
	 * @param Selection $selection
	 * @param Vector3   $place
	 * @param string    $level
	 * @return StaticBlockListSelection
	 */
	public function getUndoBlockList(Selection $selection, Vector3 $place, string $level): BlockListSelection
	{
		/** @var DynamicBlockListSelection $selection */
		Selection::validate($selection, DynamicBlockListSelection::class);
		return new StaticBlockListSelection($selection->getPlayer(), $level, $place->subtract($selection->getPoint()), $selection->getXSize(), $selection->getYSize(), $selection->getZSize());
	}
}