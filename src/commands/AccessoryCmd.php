<?php

namespace LytraAccessory\commands;

use LytraAccessory\LytraAccessory;

use pocketmine\player\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class AccessoryCmd extends Command{

    public function __construct(private LytraAccessory $la){
        $this->setLabel("accessory");
        $this->setDescription("Simply adding an accessory to your server");
        $this->setAliases(["acc", "lytraaccessory", "la"]);
        $this->setPermission("lytraaccessory.command.use");
        $this->setUsage("§c[LYTRA ACCESSORY] Usage: \n/la get [string:id] [int:amount]\n/la give [string:player] [string:id] [int:amount]\n/la info [string:id]\n/la remove [string:player] [string:id]\n/la view [string:player]\n/la slots view [string:player]\n/la slots [add|remove|set] [string:player] [int:amount]");
    }

    public function execute(CommandSender $sender, string $label, array $args){
        $main = $this->la;
        $consoleusage = "§cUsage: \n/la give [string:player] [string:id] [int:amount]\n/la info [string:id]";
        if(!$sender instanceof Player && count($args) < 2){
            $sender->sendMessage($consoleusage);
            return;
        }
        if(($this->checkPerm($sender) === "noperm" && $sender instanceof Player) || ($sender instanceof Player && count($args) === 0)){
            $main->accui($sender);
            return;
        }
        if($sender instanceof Player && count($args) < 2 && count($args) > 0 && $sender->hasPermission("lytraaccessory.command.admin")){
            $sender->sendMessage($this->getUsage());
            return;
        }
        $admin = $sender->hasPermission("lytraaccessory.command.admin");
        $get = $sender->hasPermission("lytraaccessory.command.admin.get");
        $give = $sender->hasPermission("lytraaccessory.command.admin.give");
        $info = $sender->hasPermission("lytraaccessory.command.admin.info");
        $remove = $sender->hasPermission("lytraaccessory.command.admin.remove");
        $view = $sender->hasPermission("lytraaccessory.command.admin.view");
        $sview = $sender->hasPermission("lytraaccessory.command.admin.slots.view");
        $sadd = $sender->hasPermission("lytraaccessory.command.admin.slots.add");
        $sremove = $sender->hasPermission("lytraaccessory.command.admin.slots.remove");
        $sset = $sender->hasPermission("lytraaccessory.command.admin.slots.set");
        if(count($args) >= 2){
            if($args[0] === "get"){
                if(!$admin && !$get) return $sender->sendMessage("§c[LYTRA ACCESSORY] You don't have permission to use this command");
                if(!$sender instanceof Player) return $sender->sendMessage($consoleusage);
                if($main->getAccessory($args[1]) === "notexist"){
                    $sender->sendMessage("§c[LYTRA ACCESSORY] Accessory not found!");
                    return;
                }
                $amount = 0;
                if((isset($args[2]))){
                    if((int)$args[2] < 1){
                        return $sender->sendMessage("§c[LYTRA ACCESSORY] Amount must be an integer and greater than 0");
                    }
                    $amount += (int)$args[2];
                } else {
                    $amount += 1;
                }
                if($main->possibleAddItem($sender, $args[1], $amount) === "invfull") return $sender->sendMessage("§c[LYTRA ACCESSORY] Your inventory is full");
                $main->giveAccessory($sender, $args[1], $amount);
                return;
            } elseif($args[0] === "give"){
                if(!$admin && !$give) return $sender->sendMessage("§c[LYTRA ACCESSORY] You don't have permission to use this command");
                if(count($args) < 3){
                    $sender->sendMessage($this->getUsage());
                    return;
                }
                $player = $main->getServer()->getPlayerExact($args[1]);
                if(!$player instanceof Player || !$player->isOnline()){
                    $sender->sendMessage("§c[LYTRA ACCESSORY] Player not found!");
                    return;
                }
                if($main->getAccessory($args[2]) === "notexist"){
                    $sender->sendMessage("§c[LYTRA ACCESSORY] Accessory not found!");
                    return;
                }
                $amount = 0;
                if((isset($args[3]))){
                    if((int)$args[3] < 1){
                        return $sender->sendMessage("§c[LYTRA ACCESSORY] Amount must be an integer and greater than 0");
                    }
                    $amount += (int)$args[3];
                } else {
                    $amount += 1;
                }
                if($main->possibleAddItem($player, $args[2], $amount) === "invfull") return $sender->sendMessage("§c[LYTRA ACCESSORY] " . $args[2] ."'s inventory is full");
                $main->giveAccessory($player, $args[2], $amount);
                $sender->sendMessage("§a[LYTRA ACCESSORY] Successfully give (" . $player->getName() . ") an accessory with id: " . $args[2]);
                return;
            } elseif($args[0] === "info"){
                if(!$admin && !$info) return $sender->sendMessage("§c[LYTRA ACCESSORY] You don't have permission to use this command");
                if($main->getAccessory($args[1]) === "notexist"){
                    $sender->sendMessage("§c[LYTRA ACCESSORY] Accessory not found!");
                    return;
                }
                $acccfg = $main->getAccessoryFile($args[1]);
                $sender->sendMessage("§e- Accessory Info -");
                $sender->sendMessage("§bId: " . $acccfg->get("id"));
                $sender->sendMessage("§bItem Id: " . $acccfg->get("item-id"));
                $sender->sendMessage("§bCustom Name: " . $acccfg->get("custom-name"));
                $sender->sendMessage("§bLore: ");
                foreach($acccfg->get("lore") as $lore){
                    $sender->sendMessage("§b- " . $lore);
                }
                $sender->sendMessage("§bDamage Multiplier: " . $acccfg->get("damage-multiplier"));
                $sender->sendMessage("§bHP Multiplier: " . $acccfg->get("hp-multiplier"));
                $sender->sendMessage("§e-----");
                return;
            } elseif($args[0] === "remove"){
                if(!$admin && !$remove) return $sender->sendMessage("§c[LYTRA ACCESSORY] You don't have permission to use this command");
                if(count($args) < 3){
                    $sender->sendMessage($this->getUsage());
                    return;
                }
                $player = $main->getServer()->getPlayerExact($args[1]);
                if((!$player instanceof Player) || (!$player->isOnline())){
                    $sender->sendMessage("§c[LYTRA ACCESSORY] Player not found!");
                    return;
                }
                if($main->getAccessory($args[2]) === "notexist"){
                    $sender->sendMessage("§c[LYTRA ACCESSORY] Accessory not found!");
                    return;
                }
                $main->removeAccessory($player, $args[2]);
                $sender->sendMessage("§a[LYTRA ACCESSORY] Successfully removed an accessory from (" . $player->getName() . ") with id: " . $args[2]);
                return;
            } elseif($args[0] === "view"){
                if(!$admin && !$view) return $sender->sendMessage("§c[LYTRA ACCESSORY] You don't have permission to use this command");
                $player = $main->getServer()->getPlayerExact($args[1]);
                if((!$player instanceof Player) || (!$player->isOnline())){
                    $sender->sendMessage("§c[LYTRA ACCESSORY] Player not found!");
                    return;
                }
                $main->accui2($player, $sender);
                return;
            } elseif($args[0] === "slots"){
                if($args[1] === "view"){
                    if(!$admin && !$sview) return $sender->sendMessage("§c[LYTRA ACCESSORY] You don't have permission to use this command");
                    if(count($args) < 3){
                        $sender->sendMessage($this->getUsage());
                        return;
                    }
                    $player = $main->getServer()->getPlayerExact($args[2]);
                    if((!$player instanceof Player) || (!$player->isOnline())){
                        $sender->sendMessage("§c[LYTRA ACCESSORY] Player not found!");
                        return;
                    }
                    $sender->sendMessage("§a[LYTRA ACCESSORY] " . $player->getName() . " has " . $main->getSlots($player) . " slots and " . $main->getAvailableSlots($player) . " empty slots");
                    return;
                } elseif($args[1] === "add" || $args[1] === "remove" || $args[1] === "set"){
                    if(count($args) < 4){
                        $sender->sendMessage($this->getUsage());
                        return;
                    }
                    $player = $main->getServer()->getPlayerExact($args[2]);
                    if((!$player instanceof Player) || (!$player->isOnline())){
                        $sender->sendMessage("§c[LYTRA ACCESSORY] Player not found!");
                        return;
                    }
                    if($args[3] !== "0" && (int)$args[3] === 0) return $sender->sendMessage("§c[LYTRA ACCESSORY] Amount must be an integer");
                    $slots = $main->getSlots($player);
                    switch($args[1]){
                        case "add":
                            if(!$admin && !$sadd) return $sender->sendMessage("§c[LYTRA ACCESSORY] You don't have permission to use this command");
                            if(($slots + (int)$args[3]) < 0){
                                $main->setSlots($player, 0);
                                $sender->sendMessage("§a[LYTRA ACCESSORY] Successfully added " . $args[3] . " slots on (" . $player->getName() . ")'s accessory inventory with the current total " . $main->getSlots($player) . " slots");
                                return;
                            }
                            $main->setSlots($player, $slots + (int)$args[3]);
                            $sender->sendMessage("§a[LYTRA ACCESSORY] Successfully added " . $args[3] . " slots on (" . $player->getName() . ")'s accessory inventory with the current total " . $main->getSlots($player) . " slots");
                        return;
                        case "remove":
                            if(!$admin && !$sremove) return $sender->sendMessage("§c[LYTRA ACCESSORY] You don't have permission to use this command");
                            if(($slots - (int)$args[3]) < 0){
                                $main->setSlots($player, 0);
                                $sender->sendMessage("§a[LYTRA ACCESSORY] Successfully removed " . $args[3] . " slots on (" . $player->getName() . ")'s accessory inventory with the current total " . $main->getSlots($player) . " slots");
                                return;
                            }
                            $main->setSlots($player, $slots - (int)$args[3]);
                            $sender->sendMessage("§a[LYTRA ACCESSORY] Successfully removed " . $args[3] . " slots on (" . $player->getName() . ")'s accessory inventory with the current total " . $main->getSlots($player) . " slots");
                        return;
                        case "set":
                            if(!$admin && !$sset) return $sender->sendMessage("§c[LYTRA ACCESSORY] You don't have permission to use this command");
                            if((int)$args[3] < 0){
                                return $sender->sendMessage("§c[LYTRA ACCESSORY] Can't set the accessory slots to negative amount");
                            }
                            $main->setSlots($player, (int)$args[3]);
                            $sender->sendMessage("§a[LYTRA ACCESSORY] Successfully set " . $args[3] . " slots on (" . $player->getName() . ")'s accessory inventory");
                        return;
                    }
                } else {
                    return $sender->sendMessage($this->getUsage());
                }
            } else {
                $sender->sendMessage($this->getUsage());
                return;
            }
        }
    }

    public function checkPerm(Player $player){
        $admin = $player->hasPermission("lytraaccessory.command.admin");
        $get = $player->hasPermission("lytraaccessory.command.admin.get");
        $give = $player->hasPermission("lytraaccessory.command.admin.give");
        $info = $player->hasPermission("lytraaccessory.command.admin.info");
        $remove = $player->hasPermission("lytraaccessory.command.admin.remove");
        $view = $player->hasPermission("lytraaccessory.command.admin.view");
        $sview = $player->hasPermission("lytraaccessory.command.admin.slots.view");
        $sadd = $player->hasPermission("lytraaccessory.command.admin.slots.add");
        $sremove = $player->hasPermission("lytraaccessory.command.admin.slots.remove");
        $sset = $player->hasPermission("lytraaccessory.command.admin.slots.set");
        if($admin || $get || $give || $info || $remove || $view || $sview || $sadd || $sremove || $sset){
            return "permitted";
        }
        return "noperm";
    }

}