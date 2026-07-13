<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A049_CommonWallLizard_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A049_CommonWallLizard_N';
       $this->name = 'COMMON WALL LIZARD';
       $this->appeal = '1 per connected rock';
       $this->strength = 2;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::ROCK,Icons::REPTILE]; 
     $this->continents = [Icons::EUROPE]; 

  }
}
