<?php
namespace Bga\Games\Sanctuary\Tiles\Animals;
use Bga\Games\Sanctuary\Constants\Icons;

class A064_Minipig_N extends \Bga\Games\Sanctuary\Models\Animal
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'A064_Minipig_N';
       $this->name = 'MINIPIG';
       $this->appeal = '3/9/18 for connected group';
       $this->strength = 2;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::UNDEFINED,Icons::PETTING_ZOO]; 

  }
}
