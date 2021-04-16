<?php

namespace HelpModifier\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use HelpModifier\Main;

class EnableHelpCommand extends Command implements PluginIdentifiableCommand {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        parent::__construct(
            "enablehelp",
            "Enables help",
            "/enablehelp"
        );
        $this->setPermission("help.enable");
    }

    public function getPlugin() : Plugin {
        return $this->plugin;
    }

    public function execute(CommandSender $sender, string $label, array $args) {
        if (!$this->testPermission($sender)) {
            return;
        }

        if ($this->plugin->getConfig()->get("enable-help") == true) {
            $sender->sendMessage(TextFormat::RED . "Help is already enabled");
        } else if ($this->plugin->getConfig()->get("enable-help") == false) {
            $this->plugin->getConfig()->set("enable-help", true);
            $this->plugin->getConfig()->save();
            $sender->sendMessage(TextFormat::GREEN . "Help has been enabled");
        }
    }

}
