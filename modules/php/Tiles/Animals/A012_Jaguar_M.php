<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A012_Jaguar_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A012_Jaguar_M';
       $this->name = 'JAGUAR';
       $this->appeal = '7';
       $this->strength = 4;
       $this->gender = 'M';
       //effect = 'immediate draw 3 tiles from the pile, keep 1 animal';
       $this->categories = [Icons::FOREST,Icons::PREDATOR]; 
     $this->continents = [Icons::AMERICAS]; 
     $this->openAreas = ['SW']; 
     $this->pair = 'A013_Jaguar_F'; 

  }
}
