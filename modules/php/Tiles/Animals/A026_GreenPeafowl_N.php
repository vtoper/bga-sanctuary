<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A026_GreenPeafowl_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A026_GreenPeafowl_N';
       $this->name = 'GREEN PEAFOWL';
       $this->appeal = '3 per project';
       $this->strength = 5;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::UNDEFINED,Icons::BIRD]; 
     $this->continents = [Icons::ASIA]; 

  }
}
