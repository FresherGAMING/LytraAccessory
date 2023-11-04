<?php

namespace LytraAccessory;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\utils\Config;

class EventListener implements Listener {

    public function __construct(private LytraAccessory $main){}

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $data = $this->main->data();
        $slots = $this->main->getSlotConfig();
        if(!$player->hasPlayedBefore() || $data->get($player->getName()) === null){
            $data->set($player->getName(), []);
            $data->save();
        }
        if(!$player->hasPlayedBefore() || $slots->get($player->getName()) === null){
            $slots->set($player->getName(), $this->main->getConfig()->get("default-acc-slot"));
            $slots->save();
        }
        $damage = 0;
        $hp = 0;
        foreach($this->main->data()->get($player->getName()) as $acc){
            if($this->main->getAccessoryFile($acc) !== null){
                if(substr((string)$this->main->getAccessoryFile($acc)->get("damage-multiplier"), -1) === "%"){
                    $damage += (int)$this->main->getAccessoryFile($acc)->get("damage-multiplier");
                } else {
                    $damage += (int)$this->main->getAccessoryFile($acc)->get("damage-multiplier") * 100;
                }
                if(substr((string)$this->main->getAccessoryFile($acc)->get("hp-multiplier"), -1) === "%"){
                    $hp += (int)$this->main->getAccessoryFile($acc)->get("hp-multiplier");
                } else {
                    $hp += (int)$this->main->getAccessoryFile($acc)->get("hp-multiplier") * 100;
                }
            }
        }
        $this->main->damagemulti[$player->getName()] = $damage;
        $this->main->hpmulti[$player->getName()] = $hp;
        $this->main->updateHealth($player);
    }

    public function onUse(PlayerItemUseEvent $event){
        $player = $event->getPlayer();
        $item = $event->getItem();
        if($item->getNamedTag()->getTag("LytraAccessory")){
         if($item->getNamedTag()->getString("LytraAccessory") === "true"){
            $id = $item->getNamedTag()->getString("AccessoryID");
            $acccfg = $this->main->getAccessoryFile($id);
            $acclist = $this->main->getData($player->getName());
            $acclist[] = $acccfg->get("id");
            if($acccfg === null){
                return;
            }
            if($this->main->getAvailableSlots($player) < 1){
                $player->sendMessage($this->main->getMessage("out-of-slots"));
                return;
            }
            foreach($acclist as $data){
                if($data === $acccfg->get("id") && $this->main->getConfig()->get("use-same-accessory") === false) return $player->sendMessage($this->main->getMessage("use-same-accessory"));
            }
            $this->main->addAccessory($player, $id);
            $player->getInventory()->setItemInHand($item->setCount($item->getCount() - 1));
         }
        }
    }

    public function onDamage(EntityDamageByEntityEvent $event){
        $damage1 = $event->getBaseDamage();
        $player = $event->getDamager();
        $damage2 = ($this->main->damagemulti[$player->getName()] === null) ? 0 : $this->main->damagemulti[$player->getName()];
        $damage2 = $damage1 * $damage2 / 100;
        $event->setBaseDamage($damage1 + $damage2);
    }
}