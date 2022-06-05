<?php

namespace Zwuiix\Stick;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\player\Player;

//EVENT
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\utils\Config;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;

//ITEM
use pocketmine\block\BlockLegacyIds;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\EnderPearl;

class Main extends PluginBase implements  Listener
{

    private $antipearmcooldown = [];
    private $dantipearmcooldown = [];
    private $antibuild = [];
    private $antibuilds = [];
    public static $instance;

    protected function onEnable(): void{
        self::$instance = $this;

        @mkdir($this->getDataFolder());
        $this->saveResource("config.yml");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function removeTask($id) {
        $this->getScheduler()->cancelTask($id);
    }

    protected function onLoad(): void{
      $this->reloadConfig();
    }

    public static function getInstance() : Main {
      return self::$instance;
    }

    public function onTag(EntityDamageEvent $event){
        if(!$event instanceof EntityDamageByEntityEvent) return;
           $player = $event->getEntity();
           $damager = $event->getDamager();

           $config = new Config($this->getDataFolder()."config.yml", Config::YAML);

           if(!$damager instanceof Player && !$player instanceof Player) return;
           $item = $damager->getInventory()->getItemInHand();
           if($item->getId() == $config->get("ID-PEARL")){
            $name = $damager->getName();
            if (!isset($this->dantipearmcooldown[$name])) $this->dantipearmcooldown[$name] = time();

            if (time() < $this->dantipearmcooldown[$name]) {
                if ($event->isCancelled()) return;
                $event->cancel();
                $second = $this->dantipearmcooldown[$name] - time();
                $damager->sendTip("§4- §cVous devez attendre $second seconde(s) §4-");


            }else {

                if ($event->isCancelled()) return;
                $damager->sendMessage("§eAntiPearl §f» §aVous avez bien antipearl {$player->getName()} !");
                $player->sendMessage("§eAntiPearl §f» §aVous avez été antipearl par {$damager->getName()} !");
                $this->antipearmcooldown[$player->getName()] =  time() + $config->get("Pearl-Cooldown");
                $this->dantipearmcooldown[$damager->getName()] =  time() + $config->get("PearlD-Cooldown");
            }
           }
           if($item->getId() == $config->get("ID-ANTIBUILD")){
               $name = $damager->getName();
               if (!isset($this->antibuild[$name])) $this->antibuild[$name] = time();

               if (time() < $this->antibuild[$name]) {
                   if ($event->isCancelled()) return;
                   $event->cancel();
                   $second = $this->antibuild[$name] - time();
                   $damager->sendTip("§4- §cVous devez attendre $second seconde(s) §4-");

               }else {

                   if ($event->isCancelled()) return;
                   $damager->sendMessage("§eAntiPearl §f» §aVous avez bien antibuild {$player->getName()} !");
                   $player->sendMessage("§eAntiPearl §f» §aVous avez été antibuild par {$damager->getName()} !");
                   $this->antibuilds[$player->getName()] =  time() + $config->get("ANTIBUILD-Cooldown");
                   $this->antibuild[$damager->getName()] =  time() + $config->get("ANTIBUILD-Cooldown");
               }
           }
    }

    public function onBreak(BlockBreakEvent $event)
    {
        if (!$event->getPlayer() instanceof Player) return;
        $name = $event->getPlayer()->getName();
        if (!isset($this->antibuilds[$name])) $this->antibuilds[$name] = time();

        if (time() < $this->antibuilds[$name]) {
            if ($event->isCancelled()) return;
            $event->cancel();
            $second = $this->antibuilds[$name] - time();
            $event->getPlayer()->sendTip("§4- §cVous êtes sous antibuild pendant $second seconde(s) §4-");
        }
    }

    public function onPlace(BlockPlaceEvent $event)
    {
        if (!$event->getPlayer() instanceof Player) return;
        $name = $event->getPlayer()->getName();
        if (!isset($this->antibuilds[$name])) $this->antibuilds[$name] = time();

        if (time() < $this->antibuilds[$name]) {
            if ($event->isCancelled()) return;
            $event->cancel();
            $second = $this->antibuilds[$name] - time();
            $event->getPlayer()->sendTip("§4- §cVous êtes sous antibuild pendant $second seconde(s) §4-");
        }
    }

    public function onUseEnder(PlayerItemUseEvent $event): void
    {
        $item = $event->getItem();
        $player = $event->getPlayer();
        $config = new Config($this->getDataFolder()."config.yml", Config::YAML);
        if ($event->getItem()->getId()==ItemIds::ENDER_PEARL) {
            if(!$event->getPlayer() instanceof Player) return;
            $name = $event->getPlayer()->getName();
            if (!isset($this->antipearmcooldown[$name])) $this->antipearmcooldown[$name] = time();
            if (time() < $this->antipearmcooldown[$name]) {
                if ($event->isCancelled()) return;
                $event->cancel();
                $second = $this->antipearmcooldown[$name] - time();
                $event->getPlayer()->sendTip("§4- §cVous êtes sous antipearl pendant $second seconde(s) §4-");
            }
        }
    }

    public function onUse(PlayerInteractEvent $event):void
    {
        $player = $event->getPlayer();
        $name = $player->getName();
        $item = $player->getInventory()->getItemInHand();
        $block = $event->getBlock();

        $config = new Config($this->getDataFolder()."config.yml", Config::YAML);
        if($block->getId() == BlockLegacyIds::FENCE_GATE or $block->getId() == BlockLegacyIds::CHEST or $block->getId() == BlockLegacyIds::ENDER_CHEST or $block->getId() == BlockLegacyIds::TRAPDOOR or $block->getId() == BlockLegacyIds::WOODEN_DOOR_BLOCK){
            if(!$event->getPlayer() instanceof Player) return;
            $name = $event->getPlayer()->getName();
            if (!isset($this->antibuilds[$name])) $this->antibuilds[$name] = time();

            if (time() < $this->antibuilds[$name]) {
                if ($event->isCancelled()) return;
                $event->cancel();
                $second = $this->antibuilds[$name] - time();
                $event->getPlayer()->sendTip("§4- §cVous êtes sous antibuild pendant $second seconde(s) §4-");
            }
        }
    }
}