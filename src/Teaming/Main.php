<?php

declare(strict_types=1);

namespace Teaming;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Teaming\command\TeamCommand;
use Teaming\listener\CombatListener;
use Teaming\listener\ChatListener;
use Teaming\listener\JoinListener;
use Teaming\task\NametagTask;

class Main extends PluginBase{

    private static Main $instance;

    private TeamManager $teamManager;
    private InviteManager $inviteManager;
    private HomeManager $homeManager;

    private Config $messages;

    public static function getInstance() : Main{
        return self::$instance;
    }

    protected function onEnable() : void{

        self::$instance = $this;

        $this->saveDefaultConfig();
        $this->saveResource("messages.yml");

        $this->messages = new Config(
            $this->getDataFolder() . "messages.yml",
            Config::YAML
        );

        @mkdir($this->getDataFolder());

        $this->teamManager = new TeamManager($this);
        $this->inviteManager = new InviteManager();
        $this->homeManager = new HomeManager($this);

        $this->getServer()->getCommandMap()->register(
            "team",
            new TeamCommand($this)
        );

        $pm = $this->getServer()->getPluginManager();

        $pm->registerEvents(new CombatListener($this), $this);
        $pm->registerEvents(new ChatListener($this), $this);
        $pm->registerEvents(new JoinListener($this), $this);

        $this->getScheduler()->scheduleRepeatingTask(
            new NametagTask($this),
            20
        );

        $this->getLogger()->info("Teaming enabled.");
    }

    protected function onDisable() : void{

        $this->teamManager->save();
        $this->homeManager->save();
    }

    public function getTeamManager() : TeamManager{
        return $this->teamManager;
    }

    public function getInviteManager() : InviteManager{
        return $this->inviteManager;
    }

    public function getHomeManager() : HomeManager{
        return $this->homeManager;
    }

    public function msg(string $key, array $replace = []) : string{

        $message = (string)$this->messages->get($key, $key);

        foreach($replace as $search => $value){
            $message = str_replace(
                "{" . $search . "}",
                (string)$value,
                $message
            );
        }

        return str_replace("&", "§", $message);
    }
}
