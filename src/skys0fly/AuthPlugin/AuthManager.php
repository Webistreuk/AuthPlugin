<?php

declare(strict_types=1);

namespace skyss0fly\AuthPlugin;

use pocketmine\player\Player;
use pocketmine\utils\Config;

class AuthManager {
    
    private Main $plugin;
    private array $authenticatedPlayers = [];
    
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }
    
    public function isPlayerRegistered(Player $player): bool {
        $data = $this->plugin->getPlayerData()->get(strtolower($player->getName()), []);
        return isset($data['password']);
    }
    
    public function registerPlayer(Player $player, string $password): bool {
        $username = strtolower($player->getName());
        
        if ($this->isPlayerRegistered($player)) {
            return false;
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $playerData = [
            'password' => $hashedPassword,
            'registered' => time(),
            'ip' => $player->getNetworkSession()->getIp()
        ];
        
        $this->plugin->getPlayerData()->set($username, $playerData);
        $this->plugin->getPlayerData()->save();
        
        $this->authenticatePlayer($player);
        return true;
    }
    
    public function loginPlayer(Player $player, string $password): bool {
        if (!$this->isPlayerRegistered($player)) {
            return false;
        }
        
        $username = strtolower($player->getName());
        $playerData = $this->plugin->getPlayerData()->get($username);
        $hashedPassword = $playerData['password'];
        
        if (password_verify($password, $hashedPassword)) {
            $this->authenticatePlayer($player);
            return true;
        }
        
        return false;
    }
    
    public function authenticatePlayer(Player $player): void {
        $this->authenticatedPlayers[strtolower($player->getName())] = true;
    }
    
    public function isPlayerAuthenticated(Player $player): bool {
        return isset($this->authenticatedPlayers[strtolower($player->getName())]);
    }
    
    public function logoutPlayer(Player $player): void {
        unset($this->authenticatedPlayers[strtolower($player->getName())]);
    }
    
    public function getPasswordRequirements(): array {
        $config = $this->plugin->getConfig();
        return [
            'min_length' => $config->getNested('settings.min-password-length', 4),
            'max_length' => $config->getNested('settings.max-password-length', 16)
        ];
    }
}