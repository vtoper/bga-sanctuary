<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A050_IndianPython_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A050_IndianPython_M';
       $this->name = 'INDIAN PYTHON';
       $this->appeal = '4';
       $this->strength = 3;
       $this->gender = 'M';
       //effect = 'immediate relocate 1 tile in your zoo';
       $this->categories = [Icons::ROCK,Icons::REPTILE]; 
     $this->continents = [Icons::ASIA]; 
     $this->pair = 'A051_IndianPython_F'; 

  }
}
