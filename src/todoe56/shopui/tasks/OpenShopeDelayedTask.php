<?php
namespace todoe56\shopui\tasks;
use pocketmine\scheduler\Task;
use todoe56\shopui\shopui;
use todoe56\shopui\commands\ShopCommand;
use pocketmine\Player;
class OpenShopeDelayedTask extends Task{
    protected $itemClicked;
    protected $sender;
    public $cmd;
    public function __construct(ShopCommand $cmd, $itemClicked, Player $sender)
    {
        $this->itemClicked = $itemClicked;
        $this->cmd = $cmd;
        $this->sender = $sender;
    }

    public function onRun(int $currentTick) : void{
        $this->cmd->openShop($this->itemClicked, $this->sender);
    }
}