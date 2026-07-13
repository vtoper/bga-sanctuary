<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A060_DuskyLeafMonkey_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A060_DuskyLeafMonkey_F';
       $this->name = 'DUSKY LEAF MONKEY';
       $this->appeal = '2 per connected forest';
       $this->strength = 3;
       $this->gender = 'F';
       //effect = '';
       $this->categories = [Icons::FOREST,Icons::PRIMATE]; 
     $this->continents = [Icons::ASIA]; 
     $this->pair = 'A061_DuskyLeafMonkey_M'; 

  }
}
