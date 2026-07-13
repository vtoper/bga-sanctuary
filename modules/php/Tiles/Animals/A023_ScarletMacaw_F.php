<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A023_ScarletMacaw_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A023_ScarletMacaw_F';
       $this->name = 'SCARLET MACAW';
       $this->appeal = '4';
       $this->strength = 4;
       $this->gender = 'F';
       //effect = '';
       $this->categories = [Icons::FOREST,Icons::BIRD]; 
     $this->continents = [Icons::AMERICAS]; 
     $this->pair = 'A022_ScarletMacaw_M'; 

  }
}
