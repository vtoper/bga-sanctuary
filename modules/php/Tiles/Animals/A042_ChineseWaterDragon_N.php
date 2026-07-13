<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A042_ChineseWaterDragon_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A042_ChineseWaterDragon_N';
       $this->name = 'CHINESE WATER DRAGON';
       $this->appeal = '1 per connected water';
       $this->strength = 2;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::WATER,Icons::REPTILE]; 
     $this->continents = [Icons::ASIA]; 

  }
}
