<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A030_AfricanBushElephant_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A030_AfricanBushElephant_N';
       $this->name = 'AFRICAN BUSH ELEPHANT';
       $this->appeal = '10';
       $this->strength = 5;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::UNDEFINED,Icons::HERBIVORE]; 
     $this->continents = [Icons::AFRICA]; 
     $this->openAreas = ['NW','SW','SE']; 

  }
}
