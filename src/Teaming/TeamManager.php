<?php

declare(strict_types=1);

namespace Teaming;

use pocketmine\utils\Config;

class TeamManager{

    private Main $plugin;

    private Config $teams;

    public function __construct(Main $plugin){

        $this->plugin = $plugin;

        $this->teams = new Config(
            $plugin->getDataFolder() . "teams.yml",
            Config::YAML
        );
    }

    public function save() : void{
        $this->teams->save();
    }

    public function exists(string $team) : bool{
        return $this->teams->exists($team);
    }

    public function createTeam(string $owner, string $team) : bool{

        if($this->exists($team)){
            return false;
        }

        $this->teams->set($team, [
            "owner" => $owner,
            "members" => [$owner]
        ]);

        $this->save();

        return true;
    }

    public function deleteTeam(string $team) : void{

        $this->teams->remove($team);

        $this->save();
    }

    public function getTeam(string $player) : ?string{

        foreach($this->teams->getAll() as $team => $data){

            if(in_array(
                strtolower($player),
                array_map("strtolower", $data["members"])
            )){
                return $team;
            }
        }

        return null;
    }

    public function inTeam(string $player) : bool{
        return $this->getTeam($player) !== null;
    }

    public function getOwner(string $team) : ?string{

        $data = $this->teams->get($team);

        return $data["owner"] ?? null;
    }

    public function isLeader(string $player) : bool{

        $team = $this->getTeam($player);

        if($team === null){
            return false;
        }

        return strtolower(
            $this->getOwner($team)
        ) === strtolower($player);
    }

    public function getMembers(string $team) : array{

        $data = $this->teams->get($team);

        return $data["members"] ?? [];
    }

    public function addMember(string $team, string $player) : void{

        $data = $this->teams->get($team);

        $data["members"][] = $player;

        $this->teams->set($team, $data);

        $this->save();
    }

    public function removeMember(string $team, string $player) : void{

        $data = $this->teams->get($team);

        $data["members"] = array_values(
            array_filter(
                $data["members"],
                fn(string $name) =>
                    strtolower($name) !== strtolower($player)
            )
        );

        $this->teams->set($team, $data);

        $this->save();
    }

    public function sameTeam(
        string $player1,
        string $player2
    ) : bool{

        $team1 = $this->getTeam($player1);
        $team2 = $this->getTeam($player2);

        return $team1 !== null &&
               $team2 !== null &&
               strtolower($team1) === strtolower($team2);
    }
}
