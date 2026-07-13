<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A009_EurasianBrownBear_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A009_EurasianBrownBear_N';
       $this->name = 'EURASIAN BROWN BEAR';
       $this->appeal = '8';
       $this->strength = 5;
       $this->gender = 'N';
       //effect = 'immediate take all projects from the display';
       $this->categories = [Icons::FOREST,Icons::PREDATOR]; 
     $this->continents = [Icons::EUROPE]; 
     $this->openAreas = ['N']; 

  }
}
