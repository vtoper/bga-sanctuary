<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A035_Capybara_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A035_Capybara_F';
       $this->name = 'CAPYBARA';
       $this->appeal = '4';
       $this->strength = 2;
       $this->gender = 'F';
       //effect = '';
       $this->categories = [Icons::WATER,Icons::HERBIVORE]; 
     $this->continents = [Icons::AMERICAS]; 
     $this->pair = 'A034_Capybara_M'; 

  }
}
