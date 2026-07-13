<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A029_IndianRhinoceros_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A029_IndianRhinoceros_M';
       $this->name = 'INDIAN RHINOCEROS';
       $this->appeal = '2 per building';
       $this->strength = 4;
       $this->gender = 'M';
       //effect = '';
       $this->categories = [Icons::WATER,Icons::HERBIVORE]; 
     $this->continents = [Icons::ASIA]; 
     $this->openAreas = ['NE']; 
     $this->pair = 'A028_IndianRhinoceros_F'; 

  }
}
