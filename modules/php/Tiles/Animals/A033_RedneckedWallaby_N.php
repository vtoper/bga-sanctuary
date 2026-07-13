<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A033_RedneckedWallaby_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A033_RedneckedWallaby_N';
       $this->name = 'RED-NECKED WALLABY';
       $this->appeal = '3';
       $this->strength = 2;
       $this->gender = 'N';
       //effect = 'immediate discard up to 2 tiles, gain 1 pouch marker for each';
       $this->categories = [Icons::WATER,Icons::HERBIVORE]; 
     $this->continents = [Icons::AUSTRALIA]; 

  }
}
