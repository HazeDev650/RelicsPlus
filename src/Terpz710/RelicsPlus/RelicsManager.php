<?php

namespace Terpz710\RelicsPlus;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;
use pocketmine\item\StringToItemParser;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\VanillaItems;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class RelicsManager implements Listener {

    public static function createPrismarineRelic(string $rarity): Item {
        $relic = VanillaItems::PRISMARINE_SHARD();

        $relic->setCustomName("$rarity Relic");

        $rarityTag = new StringTag("Rarity", $rarity);
        $nbt = new CompoundTag("", [$rarityTag]);
        $relic->setNamedTag($nbt);

        return $relic;
    }

    public static function getAllRelics(): array {
        return ["common", "uncommon", "rare", "epic", "legendary"];
    }

    public static function isRelic(string $relicName): bool {
        $relics = self::getAllRelics();
        return in_array($relicName, $relics);
    }

    public function onRelicInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($item->getCustomName() !== null) {
            if (strpos($item->getCustomName(), " Relic") !== false) {
                $nbt = $item->getNamedTag();
                if ($nbt !== null && $nbt->getTag("Rarity")) {
                    $rarity = $nbt->getString("Rarity");

                    if (isset($this->rewards[$rarity])) {
                        $rewardData = $this->rewards[$rarity];
                        $parsedItem = StringToItemParser::getInstance()->parse($rewardData['item']);
                        $rewardItem = $parsedItem->getResult();
                        
                        foreach ($rewardData['enchantments'] as $enchantmentString) {
                            $enchantment = StringToEnchantmentParser::getInstance()->parse($enchantmentString);
                            if ($enchantment !== null) {
                                $rewardItem->addEnchantment($enchantment);
                            }
                        }

                        $player->getInventory()->addItem($rewardItem);
                        $player->getInventory()->removeItem($item);
                        $player->sendMessage("You claimed a $rarity Relic and received your reward!");
                    } else {
                        $player->sendMessage("Invalid relic rarity.");
                    }
                }
            }
        }
    }
}
