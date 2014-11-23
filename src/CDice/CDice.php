<?php
class CDice {
  /**
   * Properties
   *
   */
  public $rolls = array();
 
 
  /**
   * Roll the dice
   *
   */
  public function Roll($times) {
    $this->rolls = array();
 
    for($i = 0; $i < $times; $i++) {
      $this->rolls[] = rand(1, 6);
    }
  }
  
    /**
   * Get the total from the last roll(s).
   *
   */
  public function GetTotal() {
	if(in_array('1',$this->rolls))
	{
		return -1;
	}
	else
	{
      return array_sum($this->rolls);
	}
  }
}