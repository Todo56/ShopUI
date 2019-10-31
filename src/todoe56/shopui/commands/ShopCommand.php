<?php
namespace todoe56\shopui\commands;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use todoe56\shopui\libs\muqsit\invmenu\{InvMenu, InvMenuHandler};
use pocketmine\item\Item;
use pocketmine\Player;
use todoe56\shopui\shopui;
use todoe56\shopui\tasks\OpenShopDelayedTask;
use todoe56\shopui\tasks\OpenShopoDeyaledTask;
use todoe56\shopui\tasks\OpenShopeDelayedTask;

class ShopCommand extends Command {
    protected $description;
    protected $usageMessage;
    protected $config;
    protected $getDataFolder;
    protected $economy;
    public $itemClicked;
    public $sender;
    protected $scheduler;
    public function __construct($command, $datafolder, $economy, $scheduler) {
        parent::__construct($command);
        $this->description = "Shop command by Todoe56.";
        $this->usageMessage = "/$command";
        $this->setPermission("$command" . ".command");
        $this->getDataFolder = $datafolder;
        $this->economy = $economy;
        $this->scheduler = $scheduler;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        $this->config = new Config($this->getDataFolder . "shop.yml", Config::YAML);
        if ($sender instanceof Player) {
            $this->openShopo($sender);
        } else {
            $sender->sendMessage("Only players can use this command.");
        }
    }
    public function openShopo(Player $sender)
    {
        $all = $this->config->getAll();
        $item = Item::get(35, 14);
        $item->setCustomName("§4Close");
        if ($this->config->get("shoptype") == "double") {
            $menu = new InvMenu(InvMenu::TYPE_DOUBLE_CHEST);
            $menu->getInventory()->setItem(53, $item);
        } else {
            $menu = InvMenu::create(InvMenu::TYPE_CHEST);
            $menu->getInventory()->setItem(26, $item);
        }
        foreach ($all["categories"] as $o) {
            $item = Item::get($o["item"], $o["meta"]);
            $item->setCustomName($o["name"]);
            if (isset($o["lore"])) {
                $item->setLore($o["lore"]);
            }
            $menu->getInventory()->addItem($item);
        }
        $menu->readonly();
        $menu->setListener(function (Player $sender, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action): bool {
            if ($itemClicked->getName() == "§4Close") {
                $sender->removeWindow($action->getInventory());
            }
            if ($itemClicked != "Air") {
                $sender->removeWindow($action->getInventory());
                $this->itemClicked = $itemClicked;
                $this->sender = $sender;

                $this->scheduler->scheduleDelayedTask(new OpenShopDelayedTask($this, $itemClicked, $sender), 2);
            }
            return true;
        });
        $menu->setName($this->config->get("shopname"));

        $menu->send($sender);
    }

    public function OpenShop($category, Player $player)
    {
        $this->category = $category;
        $all = $this->config->getAll();

        foreach ($all["categories"] as $o) {
            if ($o["name"] == $category) {
                if ($o["type"] == "double") {
                    $item = Item::get(35, 14);
                    $item->setCustomName("§4Go Back");
                    $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
                    $menu->getInventory()->setItem(53, $item);
                } else {
                    $item = Item::get(35, 14);
                    $item->setCustomName("§4Go Back");
                    $menu = InvMenu::create(InvMenu::TYPE_CHEST);
                    $menu->getInventory()->setItem(26, $item);
                }
                $menu->readonly();
                $menu->setName($category);

                foreach ($o["items"] as $itemm) {
                    $item = Item::get($itemm["id"], $itemm["meta"]);
                    $item->setCustomName($itemm["name"]);
                    $cost = $itemm["cost"];
                    $item->setCount($itemm["amount"]);
                    $item->setLore(["Cost: $cost"]);
                    if (isset($itemm["enchantments"])) {
                        foreach ($itemm["enchantments"] as $encha) {
                            $ench = explode(":", $encha);
                            $enchantment = Enchantment::getEnchantmentByName($ench[0]);
                            if ($enchantment == null) {
                                $ench1 = $ench[0];
                                return $player->sendMessage("There has been an error getting the enchantment '$ench1'.");
                            }
                            $enchInstance = new EnchantmentInstance($enchantment, $ench[1]);
                            $item->addEnchantment($enchInstance);
                        }
                    }
                    $menu->getInventory()->addItem($item);
                }

                $menu->setListener(function (Player $sender, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action): bool {
                    $name = $itemClicked->getName();
                    $all = $this->config->getAll();
                    if ($itemClicked->getName() == "§4Go Back") {
                        $sender->removeWindow($action->getInventory());
                        $this->scheduler->scheduleDelayedTask(new OpenShopoDeyaledTask($this, $sender), 2);

                    }
                    foreach ($all["categories"] as $o) {
                        foreach ($o["items"] as $itemm) {
                            if ($itemm["name"] == $name) {
                                $sender->removeWindow($action->getInventory());
                                $this->scheduler->scheduleDelayedTask(new OpenShopeDelayedTask($this, $this->category, $sender), 2);
                                $item = Item::get($itemm["id"], $itemm["meta"]);
                                $item->setCount($itemm["amount"]);
                                if ($sender->getInventory()->canAddItem($item)) {
                                    $cost = $itemm["cost"];
                                    if($this->economy->myMoney($sender->getName()) >= $cost){
                                        $this->economy->reduceMoney($sender->getName(), $itemm["cost"]);
                                        $sender->sendMessage("You bought $name for $cost.");
                                        if (isset($itemm["enchantments"])) {
                                            foreach ($itemm["enchantments"] as $encha) {
                                                $ench = explode(":", $encha);
                                                $enchantment = Enchantment::getEnchantmentByName($ench[0]);
                                                if ($enchantment == null) {
                                                    $ench1 = $ench[0];
                                                    return $sender->sendMessage("There has been an error getting the enchantment '$ench1'.");
                                                }
                                                $enchInstance = new EnchantmentInstance($enchantment, $ench[1]);
                                                $item->addEnchantment($enchInstance);
                                            }
                                        }
                                        if(isset($itemm["keepname"])){
                                            if($itemm["keepname"] == true){
                                                $item->setCustomName($name);
                                            }
                                        }
                                        if(isset($itemm["commands"])){
                                            foreach($itemm["commands"] as $command){
                                                $sender->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", $sender->getName(), $command));
                                            }
                                        }
                                        $sender->getInventory()->addItem($item);
                                    } else {
                                     $sender->sendMessage("You don't have enough money to buy that.");
                                    }
                                } else {
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