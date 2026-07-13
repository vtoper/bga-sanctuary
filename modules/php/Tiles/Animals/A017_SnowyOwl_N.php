<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A017_SnowyOwl_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A017_SnowyOwl_N';
       $this->name = 'SNOWY OWL';
       $this->appeal = '2 per connected europe';
       $this->strength = 3;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::ROCK,Icons::BIRD]; 
     $this->continents = [Icons::EUROPE]; 

  }
}
