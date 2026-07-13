<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P083_PartnerZooEurope_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P083_PartnerZooEurope_N';
       $this->name = 'PARTNER ZOO EUROPE';
       $this->appeal = 0;
       $this->strength = 3;
       $this->gender = 'N';
       //effect = 'immediate take 1 europe tile from the display####ongoingplay europe tile with 2 less action strength';
       $this->continents = [Icons::EUROPE]; 

  }
}
