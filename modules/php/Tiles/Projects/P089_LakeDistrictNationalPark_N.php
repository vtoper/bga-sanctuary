<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P089_LakeDistrictNationalPark_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P089_LakeDistrictNationalPark_N';
       $this->name = 'LAKE DISTRICT NATIONAL PARK';
       $this->appeal = '4';
       $this->strength = 4;
       $this->gender = 'N';
       //effect = 'immediate release 1 europe animal, get 2/3 conservation tokens';
       $this->categories = [Icons::WATER]; 
     $this->continents = [Icons::EUROPE]; 

  }
}
