<?php

declare(strict_types=1);

namespace skyss0fly\AuthPlugin\events;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use skyss0fly\AuthPlugin\Main;

class PlayerJoinListener implements Listener {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }
    
    public function onPlayerJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $authManager = $this->plugin->getAuthManager();
        
        if (!$authManager->isPlayerRegistered($player)) {
            $player->sendMessage($this->plugin->getConfig()->getNested('messages.register-required'));
        } else {
            $player->sendMessage($this->plugin->getConfig()->getNested('messages.login-required'));
        }
    }
    
    public function onPlayerMove(PlayerMoveEvent $event): void {
        $player = $event->getPlayer();
        
        if (!$this->plugin->getAuthManager()->isPlayerAuthenticated($player)) {
            $event->cancel();
        }
    }
    
    public function onPlayerChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        
        if (!$this->plugin->getAuthManager()->isPlayerAuthenticated($player)) {
            $player->sendMessage($this->plugin->getConfig()->getNested('messages.login-required'));
            $event->cancel();
        }
    }
    
    public function onPlayerCommand(PlayerCommandPreprocessEvent $event): void {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        
        if (!$this->plugin->getAuthManager()->isPlayerAuthenticated($player)) {
            $allowedCommands = ['/login', '/register'];
            
            $isAllowed = false;
            foreach ($allowedCommands as $cmd) {
                if (str_starts_with($message, $cmd)) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if (!$isAllowed) {
                $player->sendMessage($this->plugin->getConfig()->getNested('messages.login-required'));
                $event->cancel();
            }
        }
    }
    
    public function onPlayerQuit(PlayerQuitEvent $event): void {
        $this->plugin->getAuthManager()->logoutPlayer($event->getPlayer());
    }
}