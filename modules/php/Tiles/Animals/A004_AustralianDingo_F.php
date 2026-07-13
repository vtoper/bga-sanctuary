<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A004_AustralianDingo_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A004_AustralianDingo_F';
       $this->name = 'AUSTRALIAN DINGO';
       $this->appeal = '1 per predator';
       $this->strength = 2;
       $this->gender = 'F';
       //effect = '';
       $this->categories = [Icons::ROCK,Icons::PREDATOR]; 
     $this->continents = [Icons::AUSTRALIA]; 
     $this->pair = 'A003_AustralianDingo_M'; 

  }
}
