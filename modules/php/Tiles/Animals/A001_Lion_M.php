<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A001_Lion_M extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A001_Lion_M';
       $this->name = 'LION';
       $this->appeal = '2 per predator';
       $this->strength = 4;
       $this->gender = 'M';
       //effect = '';
       $this->categories = [Icons::ROCK,Icons::PREDATOR]; 
     $this->continents = [Icons::AFRICA]; 
     $this->pair = 'A002_Lion_F'; 

  }
}
