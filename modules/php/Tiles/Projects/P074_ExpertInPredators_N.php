<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P074_ExpertInPredators_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P074_ExpertInPredators_N';
       $this->name = 'EXPERT IN PREDATORS';
       $this->appeal = '1 per predator';
       $this->strength = 4;
       $this->gender = 'N';
       //effect = '####ongoingwhen you play a predator, draw 3 tiles from the pile, keep 1 animal';
       $this->categories = [Icons::PREDATOR]; 

  }
}
