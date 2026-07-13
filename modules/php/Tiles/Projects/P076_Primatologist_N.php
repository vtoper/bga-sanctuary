<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P076_Primatologist_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P076_Primatologist_N';
       $this->name = 'PRIMATOLOGIST';
       $this->appeal = '1 per primate';
       $this->strength = 4;
       $this->gender = 'N';
       //effect = '####ongoingwhen you play a primate, relocate 1 tile in your zoo';
       $this->categories = [Icons::PRIMATE]; 

  }
}
