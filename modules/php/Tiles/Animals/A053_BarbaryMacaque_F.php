<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A053_BarbaryMacaque_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A053_BarbaryMacaque_F';
       $this->name = 'BARBARY MACAQUE';
       $this->appeal = '5';
       $this->strength = 3;
       $this->gender = 'F';
       //effect = 'immediate move 1 action to position 1';
       $this->categories = [Icons::WATER,Icons::PRIMATE]; 
     $this->continents = [Icons::EUROPE]; 
     $this->pair = 'A054_BarbaryMacaque_M'; 

  }
}
