<?php

declare(strict_types=1);

namespace skyss0fly\AuthPlugin;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use skyss0fly\AuthPlugin\events\PlayerJoinListener;

class Main extends PluginBase {
    
    private AuthManager $authManager;
    private Config $playerData;
    
    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->authManager = new AuthManager($this);
        
        // Initialize player data storage
        $this->playerData = new Config($this->getDataFolder() . "players.yml", Config::YAML);
        
        // Register events
        $this->getServer()->getPluginManager()->registerEvents(new PlayerJoinListener($this), $this);
        
        // Register commands
        $this->getServer()->getCommandMap()->register("authplugin", new LoginCommand($this));
        $this->getServer()->getCommandMap()->register("authplugin", new RegisterCommand($this));
        
        $this->getLogger()->info("AuthPlugin enabled successfully!");
    }
    
    public function getAuthManager(): AuthManager {
        return $this->authManager;
    }
    
    public function getPlayerData(): Config {
        return $this->playerData;
    }
    
    public function onDisable(): void {
        $this->playerData->save();
        $this->getLogger()->info("AuthPlugin disabled!");
    }
}