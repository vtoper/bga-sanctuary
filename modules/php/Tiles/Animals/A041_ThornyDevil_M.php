<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A041_ThornyDevil_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A041_ThornyDevil_M';
       $this->name = 'THORNY DEVIL';
       $this->appeal = '4';
       $this->strength = 2;
       $this->gender = 'M';
       //effect = '';
       $this->categories = [Icons::ROCK,Icons::REPTILE]; 
     $this->continents = [Icons::AUSTRALIA]; 
     $this->pair = 'A040_ThornyDevil_F'; 

  }
}
