<?php

declare(strict_types=1);

namespace Teaming;

class InviteManager{

    private array $invites = [];

    public function invite(
        string $player,
        string $team
    ) : void{

        $this->invites[strtolower($player)] = [
            "team" => $team,
            "expires" => time() + 60
        ];
    }

    public function hasInvite(string $player) : bool{

        $key = strtolower($player);

        if(!isset($this->invites[$key])){
            return false;
        }

        if(time() > $this->invites[$key]["expires"]){

            unset($this->invites[$key]);

            return false;
        }

        return true;
    }

    public function getInvite(string $player) : ?string{

        if(!$this->hasInvite($player)){
            return null;
        }

        return $this->invites[
            strtolower($player)
        ]["team"];
    }

    public function removeInvite(string $player) : void{

        unset(
            $this->invites[
                strtolower($player)
            ]
        );
    }
}
