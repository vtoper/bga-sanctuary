<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P099_PredatorBreeding_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P099_PredatorBreeding_N';
       $this->name = 'PREDATOR BREEDING';
       $this->appeal = '2 per connected predator';
       $this->strength = 3;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::PREDATOR]; 

  }
}
