<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A027_GiantPanda_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A027_GiantPanda_N';
       $this->name = 'GIANT PANDA';
       $this->appeal = '9';
       $this->strength = 5;
       $this->gender = 'N';
       //effect = 'immediate take all buildings from the display';
       $this->categories = [Icons::ROCK,Icons::HERBIVORE]; 
     $this->continents = [Icons::ASIA]; 
     $this->openAreas = ['N','NE','SE']; 

  }
}
