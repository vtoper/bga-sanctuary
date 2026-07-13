<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A054_BarbaryMacaque_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A054_BarbaryMacaque_M';
       $this->name = 'BARBARY MACAQUE';
       $this->appeal = '5';
       $this->strength = 3;
       $this->gender = 'M';
       //effect = 'immediate move 1 action to position 1';
       $this->categories = [Icons::WATER,Icons::PRIMATE]; 
     $this->continents = [Icons::EUROPE]; 
     $this->pair = 'A053_BarbaryMacaque_F'; 

  }
}
