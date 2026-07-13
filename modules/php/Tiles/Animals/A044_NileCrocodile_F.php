<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A044_NileCrocodile_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A044_NileCrocodile_F';
       $this->name = 'NILE CROCODILE';
       $this->appeal = '6';
       $this->strength = 4;
       $this->gender = 'F';
       //effect = 'immediate take 1 tile from the display';
       $this->categories = [Icons::WATER,Icons::REPTILE]; 
     $this->continents = [Icons::AFRICA]; 
     $this->openAreas = ['NW']; 
     $this->pair = 'A045_NileCrocodile_M'; 

  }
}
