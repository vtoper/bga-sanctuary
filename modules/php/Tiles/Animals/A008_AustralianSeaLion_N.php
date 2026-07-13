<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A008_AustralianSeaLion_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A008_AustralianSeaLion_N';
       $this->name = 'AUSTRALIAN SEA LION';
       $this->appeal = '2 per predator';
       $this->strength = 4;
       $this->gender = 'N';
       //effect = 'immediate draw 3 tiles from the pile, keep 1 animal';
       $this->categories = [Icons::WATER,Icons::PREDATOR]; 
     $this->continents = [Icons::AUSTRALIA]; 
     $this->openAreas = ['N']; 

  }
}
