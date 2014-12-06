<?php
$db = new SQLite3('HistoryDatabase.s3db');
$summonerId = 20115434;
while (true)
{
	$details = file_get_contents("http://na.api.pvp.net/api/lol/na/v1.3/game/by-summoner/".$summonerId."/recent?api_key=51b355de-b312-4bda-8973-bdef699b7eb7");
	$details = json_decode($details);
	for ($i = 0; $i < count($details->games); $i++)
	{
		$game = $details->games[$i];
		$result = $db->query("SELECT COUNT(*) FROM `Games` WHERE `GameID`='".$game->gameId."'");
		$result = $result->fetchArray();
		$result = $result[0];
		if ($result == 0)
		{
			if ($game->gameType == "MATCHED_GAME" && $game->subType == "RANKED_SOLO_5x5")
			{
				$won = $game->stats->win;
				foreach ($game->fellowPlayers as $key => $player)
				{
					$results = $db->query("SELECT COUNT(*) FROM `Partners` WHERE `summonerId`='".$player->summonerId."'");
					$results = $results->fetchArray();
					$results = $results[0];
					if ($results == 0)
					{
						$db->query("INSERT INTO `Partners` (`summonerId`, `WinsWith`, `LossesWith`, `WinsAgainst`, `LossesAgainst`) VALUES ('".$player->summonerId."', '0', '0', '0', '0')");
					}
					if($player->teamId == $game->teamId)
					{
						if ($won)
						{
							$db->query("UPDATE `Partners` SET `WinsWith`=`WinsWith`+1 WHERE `summonerId`='".$player->summonerId."'");
						} else {
							$db->query("UPDATE `Partners` SET `LossesWith`=`LossesWith`+1 WHERE `summonerId`='".$player->summonerId."'");
						}
					} else {
						if ($won)
						{
							$db->query("UPDATE `Partners` SET `WinsAgainst`=`WinsAgainst`+1 WHERE `summonerId`='".$player->summonerId."'");
						} else {
							$db->query("UPDATE `Partners` SET `LossesAgainst`=`LossesAgainst`+1 WHERE `summonerId`='".$player->summonerId."'");		
						}
					}
				}
				$db->query("INSERT INTO `Games` (`GameID`) VALUES ('".$game->gameId."')");
			}
		}
	}
	echo "rawr";
	sleep(20);
}
?>