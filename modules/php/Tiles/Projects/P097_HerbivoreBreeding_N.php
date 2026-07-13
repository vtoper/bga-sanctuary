<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P097_HerbivoreBreeding_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P097_HerbivoreBreeding_N';
       $this->name = 'HERBIVORE BREEDING';
       $this->appeal = '2 per connected herbivore';
       $this->strength = 3;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::HERBIVORE]; 

  }
}
