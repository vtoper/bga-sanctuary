<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A063_SquirrelMonkey_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A063_SquirrelMonkey_N';
       $this->name = 'SQUIRREL MONKEY';
       $this->appeal = '1 per tile in hand';
       $this->strength = 2;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::FOREST,Icons::PRIMATE]; 
     $this->continents = [Icons::AMERICAS]; 

  }
}
