<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A025_AustralianPelican_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A025_AustralianPelican_F';
       $this->name = 'AUSTRALIAN PELICAN';
       $this->appeal = '6';
       $this->strength = 4;
       $this->gender = 'F';
       //effect = 'immediate place 1 open area from the pile in your zoo';
       $this->categories = [Icons::WATER,Icons::BIRD]; 
     $this->continents = [Icons::AUSTRALIA]; 
     $this->pair = 'A024_AustralianPelican_M'; 

  }
}
