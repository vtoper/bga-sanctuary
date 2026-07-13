<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A040_ThornyDevil_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A040_ThornyDevil_F';
       $this->name = 'THORNY DEVIL';
       $this->appeal = '4';
       $this->strength = 2;
       $this->gender = 'F';
       //effect = '';
       $this->categories = [Icons::ROCK,Icons::REPTILE]; 
     $this->continents = [Icons::AUSTRALIA]; 
     $this->pair = 'A041_ThornyDevil_M'; 

  }
}
