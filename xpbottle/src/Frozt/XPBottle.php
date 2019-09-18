<?php

/**
 * Original plugin made by Muqsit
 * This is just a small edit/Repost Made by ItzFrozt to work with API 3.0.0
 * Posted To Help Those Who Don't Understand How To Edit Plugins
 */ 

namespace Frozt;

use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\Player;

class XPBottle extends PluginBase implements Listener{

  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  public function preventCrashes(PlayerJoinEvent $e){ //This will be removed when MCPE fixes crashes caused by meta over 32000
    $p = $e->getPlayer();
    foreach($p->getInventory()->getContents() as $item){
      if($item->getId() === 384 && $item->getDamage() >= 32000){
	      $p->sendMessage(TF::BOLD.TF::RED."(!) ".TF::RESET.TF::RED."An XP bottle in your inventory caused you to crash!");
	      $p->sendMessage(TF::YELLOW.TF::BOLD."(!) ".TF::RESET.TF::GOLD."We have refunded your XP.");
	      $p->addExperience($item->getDamage());
	      $p->getInventory()->removeItem($item);
      }
    }
  }
  
  public function calculateExpReduction($p, $exp){
    $xp = $p->getCurrentTotalXp();
    $level = $p->getXpLevel();
    $p->addXp(-$xp);
  }
  
  public function redeeemExp($player, $exp){
    $currentExp = $player->getCurrentTotalXp();
	$this->var = $exp;
    if($currentExp >= $exp){
      $player->addXp(-$exp);
      $xpBottle = Item::get(384,3,1);
      $xpBottle->setCustomName(TF::RESET.TF::GREEN.TF::BOLD."Experience Bottle §r§7(Right-Click)".TF::RESET."\n".TF::LIGHT_PURPLE."§dWithdrawer:§f ".$player->getName()."\n"."§dValue:§f ".TF::WHITE.$exp);
      $player->getInventory()->addItem($xpBottle);
      $player->sendMessage(TF::GREEN.TF::BOLD."XPBottle ".TF::RESET.TF::GREEN."You have successfully redeemed ".TF::YELLOW.$exp.TF::GREEN.".");
    }else{
      $player->sendMessage(TF::RED.TF::BOLD."XPBottle ".TF::RESET.TF::RED."You don't have enough experience. Your current experience is ".TF::YELLOW.$currentExp);
    }
  }
  
  public function onInteract(PlayerInteractEvent $e){
    $p = $e->getPlayer();
    $i = $e->getItem();
      if($i->getId() === 384 && $i->getDamage() > 0){
        $i->setCount($i->getCount() - 1);
        $p->getInventory()->setItemInHand($i);
        $p->addXp($this->var);
        $e->setCancelled();
    }
  }
  
    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
    switch(strtolower($cmd->getName())){
      case "exp":
        $sender->sendMessage(TF::GREEN.TF::BOLD."§r§eYou currently have §a".$sender->getCurrentTotalXp()." EXP§e.");
        return true;
      break;
      case "xpbottle":
        if(!$sender->hasPermission("redeem.exp")) return true;
        if(!isset($args[0])) {
			$sender->sendMessage(TF::YELLOW."/xpbottle <amount>\n".TF::GRAY."Check your current experience using the command ".TF::YELLOW."/exp");
			return true;
		}
        if(isset($args[0])){
          if(is_numeric($args[0])) $this->redeeemExp($sender, $args[0]);
          else $sender->sendMessage(TF::RED.TF::BOLD."XPBottle ".TF::RESET.TF::RED."You have provided an invalid amount.");
          return true;
        }
      break;
      case "addxp":
      if(isset($args[0]) && !empty($args[0])){
        if(is_numeric($args[0])){
          if(intval($args[0]) >= 0 && intval($args[0]) <= 24790){
            if($sender instanceof Player){
              if($sender->hasPermission("xp.addown")){
                if($sender->getXpLevel() + intval($args[0]) < 24790){
                  $sender->setXpLevel(($sender->getXpLevel() + intval($args[0])));
                  $sender->sendMessage(TF::GREEN . intval($args[0]) . "XP has been added!");
                  return true;
                }
                else{
                  $sender->sendMessage(TF::RED . "The requested level is larger than the maximum possible (24790)!");
                  return true;
                }
              }
              else{
                $sender->sendMessage(TF::RED . "You do not have permission to use this command.");
                return true;
              }
            }
            else{
              $sender->sendMessage(TF::RED . "Please use this command in-game!");
              return true;
            }
          }
          else{
            $sender->sendMessage(TF::RED . "Invalid xp level ammount! Please provide a number between 0 and 24790!");
            return true;
          }
        }
        else if(!is_numeric($args[0]) && isset($args[1]) && !empty($args[1]) && is_numeric($args[1])){
          $target = $this->getServer()->getPlayer($args[0]);
          
          if($target instanceof Player){
            if(intval($args[1]) >= 0 && intval($args[1]) <= 24791){
              if($sender->hasPermission("xp.addothers")){
                if($target->getXpLevel() + intval($args[1]) < 24790){
                  $target->setXpLevel(($sender->getXpLevel() + intval($args[1])));
                  $target->sendMessage(TF::GREEN . intval($args[1]) . " xp levels have been added to your xp by " . $sender->getName());
                  $sender->sendMessage(TF::GREEN . "Added " . intval($args[1]) . " to " . $target->getName() . "'s XP level");
                  return true;
                }
                else{
                  $sender->sendMessage(TF::RED . "This XP level is larger than the maximum possible (24790)!");
                  return true;
                }
              }
              else{
                $sender->sendMessage(TF::RED . "You don't have permission to use that command.");
                return true;
              }
            }
            else{
              $sender->sendMessage(TF::RED . "Invalid xp level ammount! Please define a number between 0 and 24791!");
              return true;
            }
          }
          else{
            $sender->sendMessage(TF::RED . "Didn't find an online player with the name  " . $args[0]);
            return true;
          }
        }
        else{
          $sender->sendMessage(TF::GOLD . "Usage: /addxp <player> <level>");
          return true;
        }
      }
      else{
        $sender->sendMessage(TF::GOLD . "Usage: /addxp <player> <level>");
        return true;
      }
      break;
      case "removexp":
					if(isset($args[0]) && !empty($args[0])){
						if(is_numeric($args[0])){
							if(intval($args[0]) >= 0 && intval($args[0]) <= 24790){
								if($sender instanceof Player){
									if($sender->hasPermission("xp.removeown")){
										if($sender->getXpLevel() - intval($args[0]) >= 0){
											$sender->setXpLevel(($sender->getXpLevel() - intval($args[0])));
											$sender->sendMessage(TF::GREEN . intval($args[0]) . " Levels have been removed from your XP level");
											return true;
										}
										else{
											$sender->sendMessage(TF::RED . "The resulting XP level cannot be lower than 0!");
											return true;
										}
									}
									else{
										$sender->sendMessage(TF::RED . "You do not have permission to use that command.");
										return true;
									}
								}
								else{
									$sender->sendMessage(TF::RED . "Please use this command in-game!");
									return true;
								}
							}
							else{
								$sender->sendMessage(TF::RED . "Invalid xp level ammount! Please provide a number between 0 and 24790!");
								return true;
							}
						}
						else if(!is_numeric($args[0]) && isset($args[1]) && !empty($args[1]) && is_numeric($args[1])){
							$target = $this->getServer()->getPlayer($args[0]);
							
							if($target instanceof Player){
								if(intval($args[1]) >= 0 && intval($args[1]) <= 24791){
									if($sender->hasPermission("xp.removeothers")){
										if($target->getXpLevel() - intval($args[1]) >= 0){
											$target->setXpLevel(($sender->getXpLevel() - intval($args[1])));
											$target->sendMessage(TF::GREEN . intval($args[1]) . " xp levels have been removed from your xp by " . $sender->getName());
											$sender->sendMessage(TF::GREEN . "Removed " . intval($args[1]) . " from " . $target->getName() . "'s XP level");
											return true;
										}
										else{
											$sender->sendMessage(TF::RED . "The resulting XP level cannot be lower than 0!");
											return true;
										}
									}
									else{
										$sender->sendMessage(TF::RED . "You don't have permission to use this command.");
										return true;
									}
								}
								else{
									$sender->sendMessage(TF::RED . "Invalid xp level ammount! Please provide a number between 0 and 24791!");
									return true;
								}
							}
							else{
								$sender->sendMessage(TF::RED . "Didnt find an online player with the name " . $args[0]);
								return true;
							}
						}
						else{
							$sender->sendMessage(TF::GOLD . "Ussage: /removexp <player> <level>");
							return true;
						}
					}
					else{
						$sender->sendMessage(TF::GOLD . "Ussage: /removexp <player> <level>");
						return true;
					}
					break;
    }
  }
}
