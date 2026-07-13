<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A002_Lion_F extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A002_Lion_F';
       $this->name = 'LION';
       $this->appeal = '2 per predator';
       $this->strength = 4;
       $this->gender = 'F';
       //effect = '';
       $this->categories = [Icons::ROCK,Icons::PREDATOR]; 
     $this->continents = [Icons::AFRICA]; 
     $this->pair = 'A001_Lion_M'; 

  }
}
