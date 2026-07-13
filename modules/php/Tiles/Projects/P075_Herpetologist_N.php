<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P075_Herpetologist_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P075_Herpetologist_N';
       $this->name = 'HERPETOLOGIST';
       $this->appeal = '1 per reptile';
       $this->strength = 4;
       $this->gender = 'N';
       //effect = '####ongoingwhen you play a reptile, take 1 tile from the display';
       $this->categories = [Icons::REPTILE]; 

  }
}
