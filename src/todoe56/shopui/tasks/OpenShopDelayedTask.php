<?php
namespace todoe56\shopui\tasks;
use pocketmine\scheduler\Task;
use todoe56\shopui\shopui;
use todoe56\shopui\commands\ShopCommand;
use pocketmine\Player;
use pocketmine\item\Item;
class OpenShopDelayedTask extends Task{
protected $itemClicked;
protected $sender;
public $cmd;
    public function __construct(ShopCommand $cmd, Item $itemClicked, Player $sender)
    {
        $this->itemClicked = $itemClicked;
        $this->cmd = $cmd;
        $this->sender = $sender;
    }

    public function onRun(int $currentTick) : void{
        $this->cmd->openShop($this->itemClicked->getName(), $this->sender);
    }
}