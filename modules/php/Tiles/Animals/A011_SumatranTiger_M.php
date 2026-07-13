<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A011_SumatranTiger_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A011_SumatranTiger_M';
       $this->name = 'SUMATRAN TIGER';
       $this->appeal = '8';
       $this->strength = 5;
       $this->gender = 'M';
       //effect = 'immediate take 1 conservation token';
       $this->categories = [Icons::WATER,Icons::PREDATOR]; 
     $this->continents = [Icons::ASIA]; 
     $this->openAreas = ['SW','SE']; 
     $this->pair = 'A010_SumatranTiger_F'; 

  }
}
