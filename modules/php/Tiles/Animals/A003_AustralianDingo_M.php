<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A003_AustralianDingo_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A003_AustralianDingo_M';
       $this->name = 'AUSTRALIAN DINGO';
       $this->appeal = '1 per predator';
       $this->strength = 2;
       $this->gender = 'M';
       //effect = '';
       $this->categories = [Icons::ROCK,Icons::PREDATOR]; 
     $this->continents = [Icons::AUSTRALIA]; 
     $this->pair = 'A004_AustralianDingo_F'; 

  }
}
