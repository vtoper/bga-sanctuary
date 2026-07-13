<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A047_AmericanAnole_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A047_AmericanAnole_N';
       $this->name = 'AMERICAN ANOLE';
       $this->appeal = '1 per connected forest';
       $this->strength = 2;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::FOREST,Icons::REPTILE]; 
     $this->continents = [Icons::AMERICAS]; 

  }
}
