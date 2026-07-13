<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A014_GreatHornbill_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A014_GreatHornbill_N';
       $this->name = 'GREAT HORNBILL';
       $this->appeal = '2 per connected asia';
       $this->strength = 3;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::FOREST,Icons::BIRD]; 
     $this->continents = [Icons::ASIA]; 

  }
}
