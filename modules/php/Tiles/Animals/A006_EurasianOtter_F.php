<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A006_EurasianOtter_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A006_EurasianOtter_F';
       $this->name = 'EURASIAN OTTER';
       $this->appeal = '2 per connected water';
       $this->strength = 3;
       $this->gender = 'F';
       //effect = '####ongoing ';
       $this->categories = [Icons::WATER,Icons::PREDATOR]; 
     $this->continents = [Icons::EUROPE]; 
     $this->pair = 'A005_EurasianOtter_M'; 

  }
}
