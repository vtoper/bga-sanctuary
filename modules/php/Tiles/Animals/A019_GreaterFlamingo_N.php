<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A019_GreaterFlamingo_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A019_GreaterFlamingo_N';
       $this->name = 'GREATER FLAMINGO';
       $this->appeal = '2 per connected africa';
       $this->strength = 3;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::WATER,Icons::BIRD]; 
     $this->continents = [Icons::AFRICA]; 

  }
}
