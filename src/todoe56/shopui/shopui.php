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
		$this->initVirions();

        $this->config = new Config($this->getDataFolder() . "shop.yml", Config::YAML);
        $this->economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
        if (!$this->economy) {
            $this->getLogger()->error("Please install EconomyAPI");
        }
        $commandMap = $this->getServer()->getCommandMap();
        $commandMap->register("ShopUI", new ShopCommand($this->config->get("shopcommand"), $this->getDataFolder(), $this->economy, $this->getScheduler()));
    }
    private function initVirions() : void{
        if(!class_exists(InvMenuHandler::class)){
            throw new \RuntimeError($this->getName() . " depends upon 'InvMenu' virion for it's functioning. If you would still like to continue running " . $this->getName() . " from source, install the DEVirion plugin and download InvMenu to the /virions folder. Alternatively, you can download the pre-compiled PlayerVaults .phar file from poggit and not worry about installing the dependencies separately.");
        }
        if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }
    }

}
