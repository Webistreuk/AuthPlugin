<?php

declare(strict_types=1);

namespace skyss0fly\AuthPlugin;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class RegisterCommand extends Command {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        parent::__construct("register", "Register new account");
        $this->plugin = $plugin;
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage("This command can only be used in-game!");
            return false;
        }
        
        if (count($args) < 2) {
            $sender->sendMessage("Usage: /register <password> <confirm_password>");
            return false;
        }
        
        $password = $args[0];
        $confirmPassword = $args[1];
        
        $requirements = $this->plugin->getAuthManager()->getPasswordRequirements();
        
        if (strlen($password) < $requirements['min_length']) {
            $sender->sendMessage(str_replace("{min}", (string)$requirements['min_length'], 
                $this->plugin->getConfig()->getNested('messages.password-too-short')));
            return false;
        }
        
        if (strlen($password) > $requirements['max_length']) {
            $sender->sendMessage(str_replace("{max}", (string)$requirements['max_length'], 
                $this->plugin->getConfig()->getNested('messages.password-too-long')));
            return false;
        }
        
        if ($password !== $confirmPassword) {
            $sender->sendMessage($this->plugin->getConfig()->getNested('messages.password-mismatch'));
            return false;
        }
        
        if ($this->plugin->getAuthManager()->registerPlayer($sender, $password)) {
            $sender->sendMessage($this->plugin->getConfig()->getNested('messages.register-success'));
        } else {
            $sender->sendMessage($this->plugin->getConfig()->getNested('messages.already-registered'));
        }
        
        return true;
    }
}