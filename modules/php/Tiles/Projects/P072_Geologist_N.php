<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P072_Geologist_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P072_Geologist_N';
       $this->name = 'GEOLOGIST';
       $this->appeal = '1 per connected rock';
       $this->strength = 4;
       $this->gender = 'N';
       //effect = '####ongoingplay rock tile with 1 less action strength';
       $this->categories = [Icons::ROCK]; 

  }
}
