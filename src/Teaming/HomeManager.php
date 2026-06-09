<?php

declare(strict_types=1);

namespace Teaming;

use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\Server;

class HomeManager{

    private Main $plugin;
    private Config $homes;

    public function __construct(Main $plugin){

        $this->plugin = $plugin;

        $this->homes = new Config(
            $plugin->getDataFolder() . "homes.yml",
            Config::YAML
        );
    }

    public function save() : void{
        $this->homes->save();
    }

    public function setHome(string $team, Position $position) : void{

        $this->homes->set($team, [
            "world" => $position->getWorld()->getFolderName(),
            "x" => $position->getX(),
            "y" => $position->getY(),
            "z" => $position->getZ()
        ]);

        $this->save();
    }

    public function hasHome(string $team) : bool{
        return $this->homes->exists($team);
    }

    public function getHome(string $team) : ?Position{

        if(!$this->hasHome($team)){
            return null;
        }

        $data = $this->homes->get($team);

        $world = Server::getInstance()->getWorldManager()->getWorldByName(
            $data["world"]
        );

        if($world === null){
            return null;
        }

        return new Position(
            (float)$data["x"],
            (float)$data["y"],
            (float)$data["z"],
            $world
        );
    }

    public function removeHome(string $team) : void{

        $this->homes->remove($team);

        $this->save();
    }
}
