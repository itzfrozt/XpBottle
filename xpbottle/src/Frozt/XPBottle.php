<?php

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
	    if(!$sender->hasPermission("addexp.exp")) return true;
		if(!isset($args[0]) && !isset($args[1])) {
			$sender->sendMessage(TF::YELLOW."§r§cIncorrect usage. /addxp (Player) (Amount)");
			return true;
		}
		if(count($args) < 2){
			$sender->sendMessage(TF::YELLOW."§r§cIncorrect usage. /addxp (Player) (Amount)");
			return true;
		}
		if(isset($args[0])){
			if($player = $this->getServer()->getPlayer($args[0])){
				$player->addXp($args[1]);
				$sender->sendMessage("§r§eGave§a ".$player->getName()." ".$args[1]." §eEXP.");
				$player->sendMessage("§eYou have been given§a ".$args[1]." §eEXP.");
			}
			return true;
		}
    }
  }
}
