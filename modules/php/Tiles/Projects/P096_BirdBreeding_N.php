<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P096_BirdBreeding_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P096_BirdBreeding_N';
       $this->name = 'BIRD BREEDING';
       $this->appeal = '2 per connected bird';
       $this->strength = 3;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::BIRD]; 

  }
}
