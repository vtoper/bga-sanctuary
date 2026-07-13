<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A016_TocoToucan_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A016_TocoToucan_N';
       $this->name = 'TOCO TOUCAN';
       $this->appeal = '2 per connected americas';
       $this->strength = 3;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::ROCK,Icons::BIRD]; 
     $this->continents = [Icons::AMERICAS]; 

  }
}
