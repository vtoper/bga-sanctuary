<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P085_PartnerZooAustralia_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P085_PartnerZooAustralia_N';
       $this->name = 'PARTNER ZOO AUSTRALIA';
       $this->appeal = 0;
       $this->strength = 3;
       $this->gender = 'N';
       //effect = 'immediate take 1 australia tile from the display####ongoingplay australia tile with 2 less action strength';
       $this->continents = [Icons::AUSTRALIA]; 

  }
}
