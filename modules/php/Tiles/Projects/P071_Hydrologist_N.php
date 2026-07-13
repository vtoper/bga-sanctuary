<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P071_Hydrologist_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P071_Hydrologist_N';
       $this->name = 'HYDROLOGIST';
       $this->appeal = '1 per connected water';
       $this->strength = 4;
       $this->gender = 'N';
       //effect = '####ongoingplay water tile with 1 less action strength';
       $this->categories = [Icons::WATER]; 

  }
}
