<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P081_PartnerZooAfrica_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P081_PartnerZooAfrica_N';
       $this->name = 'PARTNER ZOO AFRICA';
       $this->appeal = 0;
       $this->strength = 3;
       $this->gender = 'N';
       //effect = 'immediate take 1 africa tile from the display####ongoingplay africa tile with 2 less action strength';
       $this->continents = [Icons::AFRICA]; 

  }
}
