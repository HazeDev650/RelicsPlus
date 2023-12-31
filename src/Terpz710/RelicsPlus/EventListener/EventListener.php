<?php

namespace Terpz710\RelicsPlus\EventListener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use Terpz710\RelicsPlus\RelicsManager;

class EventListener implements Listener {
    private $plugin;
    private $relicsManager;

    public function __construct($plugin, RelicsManager $relicsManager) {
        $this->relicsManager = $relicsManager;
        $this->plugin = $plugin;
        $this->plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin);
    }

    public function onBlockBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();

        $relicRarity = $this->getRandomRelicRarity();

        if ($relicRarity !== null && $this->chanceToGetRelic($player)) {
            $relic = $this->relicsManager->createPrismarineRelic($relicRarity);
            $player->getInventory()->addItem($relic);
            $player->sendMessage("You obtained a $relicRarity Relic!");
        }
    }

    private function getRandomRelicRarity(): ?string {
        $rarities = [
            "common" => 50,
            "uncommon" => 20,
            "rare" => 10,
            "epic" => 5,
            "legendary" => 2,
        ];

        $totalChance = array_sum($rarities);
        $random = mt_rand(1, $totalChance);

        foreach ($rarities as $rarity => $chance) {
            if ($random <= $chance) {
                return $rarity;
            }
            $random -= $chance;
        }

        return null;
    }

    private function chanceToGetRelic(Player $player): bool {
        $chance = 0.01;

        return (mt_rand(1, 100) <= $chance * 100);
    }
}
