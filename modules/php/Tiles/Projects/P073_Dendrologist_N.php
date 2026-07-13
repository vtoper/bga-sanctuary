<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P073_Dendrologist_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P073_Dendrologist_N';
       $this->name = 'DENDROLOGIST';
       $this->appeal = '1 per connected forest';
       $this->strength = 4;
       $this->gender = 'N';
       //effect = '####ongoingplay forest tile with 1 less action strength';
       $this->categories = [Icons::FOREST]; 

  }
}
