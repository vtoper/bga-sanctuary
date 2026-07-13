<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A015_LaughingKookaburra_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A015_LaughingKookaburra_N';
       $this->name = 'LAUGHING KOOKABURRA';
       $this->appeal = '2 per connected australia';
       $this->strength = 3;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::ROCK,Icons::BIRD]; 
     $this->continents = [Icons::AUSTRALIA]; 

  }
}
