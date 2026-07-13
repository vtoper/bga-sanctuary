<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A022_ScarletMacaw_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A022_ScarletMacaw_M';
       $this->name = 'SCARLET MACAW';
       $this->appeal = '4';
       $this->strength = 4;
       $this->gender = 'M';
       //effect = '';
       $this->categories = [Icons::FOREST,Icons::BIRD]; 
     $this->continents = [Icons::AMERICAS]; 
     $this->pair = 'A023_ScarletMacaw_F'; 

  }
}
