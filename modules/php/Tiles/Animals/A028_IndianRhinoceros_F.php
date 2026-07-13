<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A028_IndianRhinoceros_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A028_IndianRhinoceros_F';
       $this->name = 'INDIAN RHINOCEROS';
       $this->appeal = '2 per building';
       $this->strength = 4;
       $this->gender = 'F';
       //effect = '';
       $this->categories = [Icons::WATER,Icons::HERBIVORE]; 
     $this->continents = [Icons::ASIA]; 
     $this->openAreas = ['NW']; 
     $this->pair = 'A029_IndianRhinoceros_M'; 

  }
}
