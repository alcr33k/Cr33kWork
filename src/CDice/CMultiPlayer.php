<?php
class CMultiPlayer {
	public function switchPlayer() {
		if($_SESSION['player'] == "player1")
		{
			unset($_SESSION['player']);
			$_SESSION['player'] = "player2";
		}
		else
		{
			unset($_SESSION['player']);
			$_SESSION['player'] = "player1";
		}
	}
	public function getCurrentPlayer()
	{
		if($_SESSION['player'] == "player1")
		{
			return "Spelare 1";
		}
		else
		{
			return "Spelare 2";
		}
	}
	public function playerRoll()
	{
		$storage = new CStoreHand();
		$dice = new CDice();
		$dice->Roll(1); 
		$sum = $dice->GetTotal();
		if($sum == -1)
		{	
			unset($_SESSION['currentHand']);
			///  switch player
			$this->switchPlayer();
			$currentPlayer = $this->getCurrentPlayer();
			$player = "Status";
			return $player. ": Otur, du slog en etta och din nuvarande hand kommer inte att räknas. {$currentPlayer}, din tur!";
		}
		else
		{
			$player = $this->getCurrentPlayer();
			/// Store current hand in session
			return $player.": ".$storage->storeCurrentHand($sum);
		}
	}
	public function playerStay()
	{
		$storage = new CStoreHand();
		$dice = new CDice();
		/// store hand
		$currentPlayer = $this->getCurrentPlayer();
		$storage->storeCurrentPlayerHand($_SESSION['currentHand'], $currentPlayer);
		/// switch player
		$this->switchPlayer();
		/// check for win
		if((isset($_SESSION['player1Hand'])) && ($_SESSION['player1Hand'] >= 100))
		{
			return "Grattis! Spelare 1 vann. Klicka på starta om för att spela igen.";
		}
		else if((isset($_SESSION['player2Hand'])) && ($_SESSION['player2Hand'] >= 100))
		{
			return "Grattis! Spelare 2 vann. Klicka på starta om för att spela igen.";
		}
		else
		{
			$player = $this->getCurrentPlayer();;
			return $player.", din tur!";
		}
	}
}