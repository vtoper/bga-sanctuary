<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A018_CommonOstrich_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A018_CommonOstrich_N';
       $this->name = 'COMMON OSTRICH';
       $this->appeal = '6';
       $this->strength = 5;
       $this->gender = 'N';
       //effect = 'immediate place 2 open areas from the pile in your zoo';
       $this->categories = [Icons::ROCK,Icons::BIRD]; 
     $this->continents = [Icons::AFRICA]; 

  }
}
