<?php
namespace todoe56\shopui;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\utils\Config;

class shopui extends PluginBase implements Listener{
    private $config;
    private $economy;
    private $category;
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        $this->saveResource("shop.yml");
        if(!InvMenuHandler::isRegistered()){ InvMenuHandler::register($this); }
        $this->config = new Config($this->getDataFolder() . "shop.yml", Config::YAML);
        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        if(!$this->economy){
            $this->getLogger()->error("Please install EconomyAPI");
        }

    }
    public function onCommand(CommandSender $sender, Command $cmd, String $label, array $args): bool
    {
        switch ($cmd->getName()){
            case "shop":
                if($sender instanceof Player){
                    $this->openShopo($sender);
                }
                return true;
                break;
        }
        return true;
    }
    public function openShopo(Player $sender){
        $all = $this->config->getAll();

        $menu = InvMenu::create(InvMenu::TYPE_CHEST);
        foreach ($all["categories"] as $o){
            $item = Item::get($o["item"], $o["meta"]);
            $item->setCustomName($o["name"]);
            $menu->getInventory()->addItem($item);
        }
        $menu->readonly();
        $item = Item::get(35, 14);
        $item->setCustomName("§4Close");
        $menu->getInventory()->setItem(26, $item);


        $menu->setListener(function(Player $sender, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : bool{
            if($itemClicked->getName() == "§4Close"){
                $sender->removeWindow($action->getInventory());

            }
            if($itemClicked != "Air") {
                $sender->removeWindow($action->getInventory());
                usleep(200000);
                $this->openShop($itemClicked->getName(), $sender);
            }
            return true;
        });
        $menu->setName($this->config->get("shopname"));

        $menu->send($sender);
    }
    public function openShop($category, Player $player){
        $this->category = $category;
        $all = $this->config->getAll();

        foreach ($all["categories"] as $o) {
            if($o["name"] == $category){
                $menu = InvMenu::create(InvMenu::TYPE_CHEST);
                $menu->readonly();
                $menu->setName($category);

                foreach ($o["items"] as $itemm){
                    $item = Item::get($itemm["id"], $itemm["meta"]);
                    $item->setCustomName($itemm["name"]);
                    $cost = $itemm["cost"];
                    $item->setCount($itemm["amount"]);
                    $item->setLore(["Cost: $cost"]);
                    $menu->getInventory()->addItem($item);

                }
                $item = Item::get(35, 14);
                $item->setCustomName("§4Go Back");

                $menu->getInventory()->setItem(26, $item);
                $menu->setListener(function(Player $sender, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action) : bool{
                    $name = $itemClicked->getName();
                    $all = $this->config->getAll();
                    if($itemClicked->getName() == "§4Go Back"){
                        $sender->removeWindow($action->getInventory());
                        usleep(200000);
                        $this->openShopo($sender);
                    }
                    foreach ($all["categories"] as $o){
                        foreach ($o["items"] as $itemm){
                             if($itemm["name"] == $name){
                                 $sender->removeWindow($action->getInventory());
                                 usleep(200000);
                                 $this->openShop($this->category, $sender);
                                 $item = Item::get($itemm["id"], $itemm["meta"]);
                                 $item->setCount($itemm["amount"]);
                                 if($sender->getInventory()->canAddItem($item)){
                                     $this->economy->reduceMoney($sender->getName(), $itemm["cost"]);
                                     $cost  = $itemm["cost"];
                                     $sender->sendMessage("You bought $name for $cost.");
                                     $sender->getInventory()->addItem($item);
                                 }else{
                                     $sender->sendMessage("Your inventory is full!");
                                 }

                             }
                        }
                    }
                    return true;
                });
                $menu->send($player);



            }
        }
    }
}
