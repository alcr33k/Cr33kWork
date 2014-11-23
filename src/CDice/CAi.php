<?php
class CAi {
	public function aiRoll() {
		$storage = new CStoreHand();
		$dice = new CDice();
		$dice->Roll(1); 
		$sum = $dice->GetTotal();
		if($sum == -1)
		{	
			unset($_SESSION['currentHand']);
			/// now let the computer play
			$dice->Roll(rand(1,4)); 
			$sum = $dice->GetTotal();
			$storage->storeCPUHand($sum);
			if($sum == -1)
			{
				$sum = "en etta och fick 0 poäng";
			}
			return "Otur, du slog en etta och din nuvarande summa kommer inte att räknas. Datorn slog ".$sum." och stannade. Din tur!";
		}
		else
		{
			/// Store current hand in session
			return $storage->storeCurrentHand($sum);
		}
	}
	public function aiStay() {
		$storage = new CStoreHand();
		$dice = new CDice();
		$storage->storePlayerHand($_SESSION['currentHand']);
		/// Let the computer play
		$dice->Roll(rand(1,4)); 
		$sum = $dice->GetTotal();
		if((isset($_SESSION['playerHand'])) && ($_SESSION['playerHand'] >= 100))
		{
			return "Grattis! Du vann. Du har vunnit en gratis film, säg 100VINST och du får en gratis film! Klicka på starta om för att spela igen.";
		}
		else if((isset($_SESSION['CPUhand'])) && ($_SESSION['CPUhand'] >= 100))
		{
			return "Datorn vann. Klicka på starta om för att spela igen.";
		}
		else
		{
			return $storage->storeCPUHand($sum);
		}
	}
}