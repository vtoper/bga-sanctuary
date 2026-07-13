<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P079_ExpertInSmallAnimals_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P079_ExpertInSmallAnimals_N';
       $this->name = 'EXPERT IN SMALL ANIMALS';
       $this->appeal = 0;
       $this->strength = 4;
       $this->gender = 'N';
       //effect = 'immediate take 1 small animal from the display####ongoingplay small animal tile with 1 less action strength';
  
  }
}
