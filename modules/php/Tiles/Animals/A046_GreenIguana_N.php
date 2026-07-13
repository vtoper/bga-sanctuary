<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A046_GreenIguana_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A046_GreenIguana_N';
       $this->name = 'GREEN IGUANA';
       $this->appeal = '4';
       $this->strength = 3;
       $this->gender = 'N';
       //effect = 'immediate take 1 tile from the display';
       $this->categories = [Icons::FOREST,Icons::REPTILE]; 
     $this->continents = [Icons::AMERICAS]; 

  }
}
