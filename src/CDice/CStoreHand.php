<?php
class CStoreHand {
	public function storeCPUHand($sum) {
		if($sum == -1)
		{
			if(isset($_SESSION['CPUhand'])) { /// CPU got a hand already
				$hand = $_SESSION['CPUhand'];
				$finalsum  = $hand + 0;
				$_SESSION['CPUhand'] = $finalsum;
			}
			else { /// not stored yet
				$_SESSION['CPUhand'] = 0;
			}
			return "Datorn råkade slå en etta och fick 0 poäng denna omgång. Din tur!";
		}
		else
		{
			if(isset($_SESSION['CPUhand'])) { /// CPU got a hand already
				$hand = $_SESSION['CPUhand'];
				$finalsum  = $hand + $sum;
				$_SESSION['CPUhand'] = $finalsum;
			}
			else { /// not stored yet
				$_SESSION['CPUhand'] = $sum;
			}
			return "Datorn slog en summa på ".$sum." och stannade. Din tur!";
		}
		unset($_SESSION['currentHand']);
	}
	public function storePlayerHand($sum) {
		if(isset($_SESSION['playerHand'])) { /// CPU got a hand already
			$_SESSION['playerHand']  = $_SESSION['playerHand'] +  $sum;
		}
		else { /// not stored yet
			$_SESSION['playerHand'] = $_SESSION['currentHand'];
		}
		unset($_SESSION['currentHand']);
	}
	public function storeCurrentHand($sum) {
		if(isset($_SESSION['currentHand'])) { /// player got a hand already
			$hand = $_SESSION['currentHand'];
			$finalsum  = $hand + $sum;
			$_SESSION['currentHand'] = $finalsum;
		}
		else { /// not stored yet
			$_SESSION['currentHand'] = $sum;
			$finalsum = $sum;
		}
		return "Du slog ".$sum.". Du har nu en summa på ".$_SESSION['currentHand'].". Vill du stanna eller slå igen?";
	}
	public function storeCurrentPlayerHand($sum, $player) {
		if($player == "Spelare 1")
		{
			if(isset($_SESSION['player1Hand'])) { /// player got a hand already
				$hand = $_SESSION['player1Hand'];
				$finalsum  = $hand + $sum;
				$_SESSION['player1Hand'] = $finalsum;
			}
			else { /// not stored yet
				$_SESSION['player1Hand'] = $sum;
				$finalsum = $sum;
			}
		}
		else
		{
			if(isset($_SESSION['player2Hand'])) { /// player got a hand already
				$hand = $_SESSION['player2Hand'];
				$finalsum  = $hand + $sum;
				$_SESSION['player2Hand'] = $finalsum;
			}
			else { /// not stored yet
				$_SESSION['player2Hand'] = $sum;
				$finalsum = $sum;
			}
		}
		unset($_SESSION['currentHand']);
	}
	public function getPlayersum() {
		return $_SESSION['playerHand'];
	}
	public function getCPUsum() {
		return $_SESSION['CPUhand'];
	}
	public function getPlayer1sum() {
		return $_SESSION['player1Hand'];
	}
	public function getPlayer2sum() {
		return $_SESSION['player2Hand'];
	}
}