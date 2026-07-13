<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A013_Jaguar_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A013_Jaguar_F';
       $this->name = 'JAGUAR';
       $this->appeal = '7';
       $this->strength = 4;
       $this->gender = 'F';
       //effect = 'immediate draw 3 tiles from the pile, keep 1 animal';
       $this->categories = [Icons::FOREST,Icons::PREDATOR]; 
     $this->continents = [Icons::AMERICAS]; 
     $this->openAreas = ['NE']; 
     $this->pair = 'A012_Jaguar_M'; 

  }
}
