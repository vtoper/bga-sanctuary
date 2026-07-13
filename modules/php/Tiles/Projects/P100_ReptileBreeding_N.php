<?php
namespace Bga\Games\Sanctuary\Tiles\Projects;
use Bga\Games\Sanctuary\Constants\Icons;

class P100_ReptileBreeding_N extends \Bga\Games\Sanctuary\Models\Project
{
  public function __construct($row){
		parent::__construct($row);
       $this->id = 'P100_ReptileBreeding_N';
       $this->name = 'REPTILE BREEDING';
       $this->appeal = '2 per connected reptile';
       $this->strength = 3;
       $this->gender = 'N';
       //effect = '';
       $this->categories = [Icons::REPTILE]; 

  }
}
