<?php

declare(strict_types=1);

namespace skyss0fly\AuthPlugin;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class LoginCommand extends Command {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        parent::__construct("login", "Login to your account");
        $this->plugin = $plugin;
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game!");
            return false;
        }
        
        if (count($args) < 1) {
            $sender->sendMessage("Usage: /login <password>");
            return false;
        }
        
        $password = $args[0];
        
        if ($this->plugin->getAuthManager()->loginPlayer($sender, $password)) {
            $sender->sendMessage($this->plugin->getConfig()->getNested('messages.login-success'));
        } else {
            $sender->sendMessage($this->plugin->getConfig()->getNested('messages.wrong-password'));
        }
        
        return true;
    }
}