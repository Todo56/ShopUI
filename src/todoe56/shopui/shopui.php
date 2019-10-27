<?php
namespace todoe56\shopui;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandMap;
use todoe56\shopui\libs\muqsit\invmenu\{InvMenu, InvMenuHandler};
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\utils\Config;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use todoe56\shopui\commands\ShopCommand;
class shopui extends PluginBase implements Listener
{
    private $config;
    private $economy;
    private $category;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        $this->saveResource("shop.yml");
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }
        $this->config = new Config($this->getDataFolder() . "shop.yml", Config::YAML);
        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        if (!$this->economy) {
            $this->getLogger()->error("Please install EconomyAPI");
        }
        $commandMap = $this->getServer()->getCommandMap();
        $commandMap->register($this->config->get("shopcommand"), new ShopCommand($this->config->get("shopcommand"), $this->getDataFolder(), $this->economy));
    }


}
