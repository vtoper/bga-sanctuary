<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A057_HamadryasBaboon_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A057_HamadryasBaboon_M';
       $this->name = 'HAMADRYAS BABOON';
       $this->appeal = '8';
       $this->strength = 4;
       $this->gender = 'M';
       //effect = 'immediate draw 1 tile from the pile';
       $this->categories = [Icons::ROCK,Icons::PRIMATE]; 
     $this->continents = [Icons::AFRICA]; 
     $this->openAreas = ['NE','SW']; 
     $this->pair = 'A058_HamadryasBaboon_F'; 

  }
}
