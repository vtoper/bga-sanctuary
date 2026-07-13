<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P098_PrimateBreeding_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P098_PrimateBreeding_N';
       $this->name = 'PRIMATE BREEDING';
       $this->appeal = '2 per connected primate';
       $this->strength = 3;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::PRIMATE]; 

  }
}
