<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A031_AlpineIbex_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A031_AlpineIbex_M';
       $this->name = 'ALPINE IBEX';
       $this->appeal = '2 per connected rock';
       $this->strength = 3;
       $this->gender = 'M';
       //effect = '';
       $this->categories = [Icons::ROCK,Icons::HERBIVORE]; 
     $this->continents = [Icons::EUROPE]; 
     $this->pair = 'A032_AlpineIbex_F'; 

  }
}
