<?php

declare(strict_types=1);

namespace Teaming\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use Teaming\Main;
class TeamCommand extends Command{

    private Main $plugin;

    public function __construct(Main $plugin){

        parent::__construct(
            "team",
            "Team management"
        );

        $this->plugin = $plugin;

        $this->setPermission("teaming.command");
    }
  public function execute(
    CommandSender $sender,
    string $label,
    array $args
) : void{

    if(!$sender instanceof Player){
        return;
    }

    if(count($args) === 0){

        $sender->sendMessage("§6/team help");

        return;
    }

    switch(strtolower($args[0])){
      case "create":

    if(!isset($args[1])){
        return;
    }

    $team = $args[1];

    $tm = $this->plugin->getTeamManager();

    if($tm->inTeam($sender->getName())){

        $sender->sendMessage(
            $this->plugin->msg("already-in-team")
        );

        return;
    }

    if(!$tm->createTeam(
        $sender->getName(),
        $team
    )){

        $sender->sendMessage(
            $this->plugin->msg("team-exists")
        );

        return;
    }

    $sender->sendMessage(
        $this->plugin->msg(
            "team-created",
            ["team" => $team]
        )
    );

break;
      case "invite":

    if(!isset($args[1])){
        return;
    }

    $tm = $this->plugin->getTeamManager();

    if(!$tm->isLeader($sender->getName())){

        $sender->sendMessage(
            $this->plugin->msg("not-leader")
        );

        return;
    }

    $target = $this->findPlayer($args[1]);

    if($target === null){

        $sender->sendMessage(
            $this->plugin->msg("player-not-found")
        );

        return;
    }

    if($tm->inTeam($target->getName())){

        $sender->sendMessage(
            $this->plugin->msg("already-in-team")
        );

        return;
    }

    $team = $tm->getTeam(
        $sender->getName()
    );

    $this->plugin->getInviteManager()->invite(
        $target->getName(),
        $team
    );

    $sender->sendMessage(
        $this->plugin->msg(
            "invite-sent",
            ["player" => $target->getName()]
        )
    );

    $target->sendMessage(
        $this->plugin->msg(
            "invite-received",
            ["team" => $team]
        )
    );

break;
      case "accept":

    $invites = $this->plugin->getInviteManager();

    if(!$invites->hasInvite(
        $sender->getName()
    )){

        $sender->sendMessage(
            $this->plugin->msg("no-invite")
        );

        return;
    }

    $team = $invites->getInvite(
        $sender->getName()
    );

    $this->plugin->getTeamManager()->addMember(
        $team,
        $sender->getName()
    );

    $invites->removeInvite(
        $sender->getName()
    );

    $sender->sendMessage(
        $this->plugin->msg(
            "joined-team",
            ["team" => $team]
        )
    );

break;
      case "leave":

    $tm = $this->plugin->getTeamManager();

    if(!$tm->inTeam($sender->getName())){

        $sender->sendMessage(
            $this->plugin->msg("not-in-team")
        );

        return;
    }

    if($tm->isLeader($sender->getName())){

        $sender->sendMessage(
            $this->plugin->msg("not-leader")
        );

        return;
    }

    $team = $tm->getTeam(
        $sender->getName()
    );

    $tm->removeMember(
        $team,
        $sender->getName()
    );

    $sender->sendMessage(
        $this->plugin->msg("left-team")
    );

break;
      case "sethome":

    $tm = $this->plugin->getTeamManager();

    if(!$tm->isLeader($sender->getName())){

        $sender->sendMessage(
            $this->plugin->msg("not-leader")
        );

        return;
    }

    $team = $tm->getTeam(
        $sender->getName()
    );

    $this->plugin->getHomeManager()->setHome(
        $team,
        $sender->getPosition()
    );

    $sender->sendMessage(
        $this->plugin->msg("home-set")
    );

break;
      case "home":

    $tm = $this->plugin->getTeamManager();

    if(!$tm->inTeam($sender->getName())){

        $sender->sendMessage(
            $this->plugin->msg("not-in-team")
        );

        return;
    }

    $combat = $this->plugin
        ->getServer()
        ->getPluginManager()
        ->getPlugin("CombatLogger");

    if(
        $combat !== null &&
        method_exists($combat, "isInCombat")
    ){
        if($combat->isInCombat($sender)){

            $sender->sendMessage(
                $this->plugin->msg("combat-blocked")
            );

            return;
        }
    }

    $team = $tm->getTeam(
        $sender->getName()
    );

    $home = $this->plugin
        ->getHomeManager()
        ->getHome($team);

    if($home === null){

        $sender->sendMessage(
            $this->plugin->msg("home-not-set")
        );

        return;
    }

    $sender->teleport($home);

    $sender->sendMessage(
        $this->plugin->msg("home-teleported")
    );

break;
      private function findPlayer(
    string $partial
) : ?Player{

    foreach(
        $this->plugin->getServer()->getOnlinePlayers()
        as $player
    ){

        if(
            stripos(
                $player->getName(),
                $partial
            ) === 0
        ){
            return $player;
        }
    }

    return null;
}
