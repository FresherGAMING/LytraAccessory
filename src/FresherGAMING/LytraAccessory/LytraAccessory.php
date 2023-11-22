<?php

namespace FresherGAMING\LytraAccessory;

use FresherGAMING\LytraAccessory\commands\AccessoryCmd;
use jojoe77777\FormAPI\SimpleForm;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\math\Vector3;

class LytraAccessory extends PluginBase {

    public $damagemulti;
    public $hpmulti;
    private static $instance = null;
    public function onEnable() : void{
        self::$instance = $this;
        $this->saveResource("config.yml");
        $this->saveResource("messages.yml");
        $this->saveResource("examples.yml");
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML, []);
        foreach($config->get("loaded-accessory") as $acc) {
            if(!file_exists($this->getDataFolder() . $acc)){
                $this->getLogger()->warning($acc . " is not loaded");
            }
        }
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register("LytraAccessory", new AccessoryCmd($this));
    }

    public static function getInstance(){
        return self::$instance;
    }

    public function getAccessoryFile($accid){
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML, []);
        foreach($config->get("loaded-accessory") as $cfg){
            if(file_exists($this->getDataFolder() . $cfg)){
              $acccfg = new Config($this->getDataFolder() . $cfg);
              if($acccfg->get("id") === $accid){
                return $acccfg;
              }
            }
        }
    }

    public function data(){
        $cfg = new Config($this->getDataFolder() . "data.yml", Config::YAML, []);
        return $cfg;
    }

    public function getData(string $player){
        $cfg = new Config($this->getDataFolder() . "data.yml", Config::YAML, []);
        return $cfg->get($player);
    }

    public function getMessage(string $msg){
        $cfg = new Config($this->getDataFolder() . "messages.yml", Config::YAML, []);
        return $cfg->get($msg);
    }

    public function getAccessory(string $accstring, $count = 1){
        $config = new Config($this->getDataFolder() . "config.yml", Config::YAML, []);
        foreach($config->get("loaded-accessory") as $cfg){
          if(file_exists($this->getDataFolder() . $cfg)){
            $acccfg = new Config($this->getDataFolder() . $cfg);
            if($acccfg->get("id") === $accstring){
                $acc = StringToItemParser::getInstance()->parse($acccfg->get("item-id"));
                $acc->setCount($count);
                $acc->setCustomName(($acccfg->get("custom-name") === null) ? $acc->getVanillaName() : $acccfg->get("custom-name"));
                $acc->setLore(($acccfg->get("lore") === null) ? [] : $acccfg->get("lore"));
                $acc->getNamedTag()->setString("LytraAccessory", "true");
                $acc->getNamedTag()->setString("AccessoryID", $accstring);
              return $acc;
            }
          }
        }
        return "notexist";
    }

    public function possibleAddItem(Player $player, $id, int $count){
        if($this->getAccessory($id) === "notexist"){
            return;
        }
        $acc = $this->getAccessory($id, $count);
        if(!$player->getInventory()->canAddItem($acc)){
            $config = new Config($this->getDataFolder() . "config.yml", Config::YAML, []);
            if($config->get("add-item-while-full") === false){
                return "invfull";
            }
            if($config->get("add-item-while-full") === true){
                return "ok-true";
            }
        }
        return "ok";    
    }

    public function checkForAcc(Player $player, string $id){
        $data = $this->getData($player->getName());
        $acc = array_search($id, $data);
        if($acc === false){
            return "noacc";
        }
    }

    public function giveAccessory(Player $player, $id, $count = 1){
        if($this->getAccessory($id) === "notexist"){
            return null;
        }
        $acc = $this->getAccessory($id, $count);
        if($this->possibleAddItem($player, $id, $count) === "ok"){
            return $player->getInventory()->addItem($acc);
        }
        if($this->possibleAddItem($player, $id, $count) === "ok-true"){
            $inv = $player->getInventory();
            $remainingslot = 0;
            foreach($inv->getContents(true) as $slot){
                if($slot->getTypeId() === 0){
                    $remainingslot += 64;
                }
            }
            foreach($inv->all($acc) as $slot){
                if($slot->getNamedTag()->getTag("LytraAccessory") && $slot->getNamedTag()->getString("AccessoryID") === $id){
                    $remainslot = $slot->getMaxStackSize() - $slot->getCount();
                    $remainingslot += $remainslot;
                }
            }
            $defaultcount = $acc->getCount();
            $remainingcount = $defaultcount - $remainingslot;
            $pos = $player->getPosition();
            $player->getInventory()->addItem($acc->setCount($remainingslot));
            $pos->getWorld()->dropItem(new Vector3($pos->getX(), $pos->getY(), $pos->getZ()), $acc->setCount($remainingcount));
        }
    }

    public function updateHealth(Player $player){
        $hp = $this->getConfig()->get("default-health") * $this->hpmulti[$player->getName()] / 100;
        $player->setMaxHealth($this->getConfig()->get("default-health") + $hp);
        if($player->getHealth() > $player->getMaxHealth()){
           $player->setHealth($player->getMaxHealth());
        }
    }

    public function addAccessory(Player $player, $id){
        $lastdamage = ($this->damagemulti[$player->getName()] === null) ? 0 : $this->damagemulti[$player->getName()];
        $lasthp = ($this->hpmulti[$player->getName()] === null) ? 0 : $this->hpmulti[$player->getName()];
        $acccfg = $this->getAccessoryFile($id);
        $acclist = $this->getData($player->getName());
        $acclist[] = $acccfg->get("id");
        $data = $this->data();
        $data->set($player->getName(), $acclist);
        $data->save();
        $dmgadds = (substr($acccfg->get("damage-multiplier"), -1) === "%") ? (int)$acccfg->get("damage-multiplier") : (int)$acccfg->get("damage-multiplier") * 100;
        $hpadds = (substr($acccfg->get("hp-multiplier"), -1) === "%") ? (int)$acccfg->get("hp-multiplier") : (int)$acccfg->get("hp-multiplier") * 100;
        $this->damagemulti[$player->getName()] = $lastdamage + (int)$dmgadds;
        $this->hpmulti[$player->getName()] = $lasthp + (int)$hpadds;
        $this->updateHealth($player);
    }

    public function removeAccessory(Player $player, $id){
        $data = $this->getData($player->getName());
        $acc = array_search($id, $data);
        if($acc === false){
            return "noacc";
        }
        unset($data[$acc]);
        $newdata = array_values($data);
        $datacfg = $this->data();
        $datacfg->set($player->getName(), $newdata);
        $datacfg->save();
        $file = $this->getAccessoryFile($id);
        $dmgreduce = (substr($file->get("damage-multiplier"), -1) === "%") ? (int)$file->get("damage-multiplier") : (int)$file->get("damage-multiplier") * 100;
        $hpreduce = (substr($file->get("hp-multiplier"), -1) === "%") ? (int)$file->get("hp-multiplier") : (int)$file->get("hp-multiplier") * 100;
        $newdmg = $this->damagemulti[$player->getName()] - $dmgreduce;
        $newhp = $this->hpmulti[$player->getName()] - $hpreduce;
        unset($this->damagemulti[$player->getName()]);
        unset($this->hpmulti[$player->getName()]);
        $this->damagemulti[$player->getName()] = $newdmg;
        $this->hpmulti[$player->getName()] = $newhp;
        $this->updateHealth($player);
    }

    public function getSlotConfig(){
        $cfg = new Config($this->getDataFolder() . "slots.yml", Config::YAML, []);
        return $cfg;
    }

    public function getSlots(Player $player){
        return $this->getSlotConfig()->get($player->getName());
    }

    public function getAvailableSlots(Player $player){
        $acclist = count($this->getData($player->getName()));
        $slot = $this->getSlots($player);
        $avslot = $slot - $acclist;
        return $avslot;
    }

    public function setSlots(Player $player, int $amount){
        $cfg = $this->getSlotConfig();
        $cfg->set($player->getName(), $amount);
        $cfg->save();
    }

    public function accui(Player $player){
        $form = new SimpleForm(function($player, $data){
            if($data === null) return;
            if($this->possibleAddItem($player, $data, 1) === "invfull") return $player->sendMessage($this->getMessage("inv-full"));
            $this->removeAccessory($player, $data);
            $acccfg = $this->getAccessoryFile($data);
            $player->sendMessage(str_replace("{acc-name}", $acccfg->get("custom-name"), $this->getMessage("remove-accessory")));
            $this->giveAccessory($player, $data, 1);
            $this->accui($player);
            return;
        });
        $form->setTitle($this->getMessage("form-title"));
        $slotused = count($this->getData($player->getName()));
        $slotamount = $this->getSlots($player);
        $content = $this->getMessage("form-description");
        $content = str_replace(["{slot-used}", "{slot-amount}"], [$slotused, $slotamount], $content);
        $form->setContent($content);
        foreach($this->getData($player->getName()) as $acc){
            $file = $this->getAccessoryFile($acc);
            $button = $this->getMessage("form-button");
            $button = str_replace("{accessory}", $file->get("custom-name"), $button);
            $form->addButton($button, -1, "", $file->get("id"));
        }
        $form->sendToPlayer($player);
    }

    public function accui2(Player $player, Player $viewer){
        $form = new SimpleForm(function($player, $data) use($viewer){
            if($data === null) return;
            if(!$player->isOnline()) return $viewer->sendMessage("§c[LYTRA ACCESSORY] The player went offline");
               $this->removeAccessory($player, $data);
               $viewer->sendMessage("§a[LYTRA ACCESSORY] Successfully removed an accessory from (" . $player->getName() . ") with id: " . $data);
               return;
        });
        $form->setTitle("§e" . $player->getName() . "'s Accessory");
        $slotused = count($this->getData($player->getName()));
        $slotamount = $this->getSlots($player);
        $content = $this->getMessage("form-description");
        $content = str_replace(["{slot-used}", "{slot-amount}"], [$slotused, $slotamount], $content);
        $form->setContent($content);
        foreach($this->getData($player->getName()) as $acc){
            $file = $this->getAccessoryFile($acc);
            $button = $this->getMessage("form-button");
            $button = str_replace("{accessory}", $file->get("custom-name"), $button);
            $form->addButton($button, -1, "", $file->get("id"));
        }
        $form->sendToPlayer($viewer);
    }

}