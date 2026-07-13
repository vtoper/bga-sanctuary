<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A058_HamadryasBaboon_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A058_HamadryasBaboon_F';
       $this->name = 'HAMADRYAS BABOON';
       $this->appeal = '8';
       $this->strength = 4;
       $this->gender = 'F';
       //effect = 'immediate draw 1 tile from the pile';
       $this->categories = [Icons::ROCK,Icons::PRIMATE]; 
     $this->continents = [Icons::AFRICA]; 
     $this->openAreas = ['NE','SW']; 
     $this->pair = 'A057_HamadryasBaboon_M'; 

  }
}
