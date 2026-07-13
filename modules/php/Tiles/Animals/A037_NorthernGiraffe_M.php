<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A037_NorthernGiraffe_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A037_NorthernGiraffe_M';
       $this->name = 'NORTHERN GIRAFFE';
       $this->appeal = '7';
       $this->strength = 4;
       $this->gender = 'M';
       //effect = 'immediate take 1 building from the display';
       $this->categories = [Icons::FOREST,Icons::HERBIVORE]; 
     $this->continents = [Icons::AFRICA]; 
     $this->openAreas = ['SW']; 
     $this->pair = 'A036_NorthernGiraffe_F'; 

  }
}
