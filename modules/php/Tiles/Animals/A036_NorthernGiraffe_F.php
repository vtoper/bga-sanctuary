<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A036_NorthernGiraffe_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A036_NorthernGiraffe_F';
       $this->name = 'NORTHERN GIRAFFE';
       $this->appeal = '7';
       $this->strength = 4;
       $this->gender = 'F';
       //effect = 'immediate take 1 building from the display';
       $this->categories = [Icons::FOREST,Icons::HERBIVORE]; 
     $this->continents = [Icons::AFRICA]; 
     $this->openAreas = ['NE']; 
     $this->pair = 'A037_NorthernGiraffe_M'; 

  }
}
