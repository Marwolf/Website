<?php
if (!defined('IN_SITE')) {
	die("You do not have permission to access this file.");
}

$skill_array = array('attack', 'strength', 'defense', 'hits', 'ranged', 'prayer', 'magic', 'cooking', 'woodcut', 'fletching', 'fishing', 'firemaking', 'crafting', 'smithing', 'mining', 'herblaw', 'agility', 'thieving');

function buildSQLArray($array)
{
	$SQLarray = '';
	$size = sizeof($array) - 1;
	$i = 0;
	while ($i <= $size) {
		$SQLarray .= ($array[$i] == 'total_lvl') ? '' : (($array[$i] == 'hitpoints') ? 'exp_hits,' : 'exp_' . $array[$i] . '' . (($i == $size) ? '' : ',') . '');
		$i++;
	}
	return $SQLarray;
}

$connector = new Dbc();

$skills = buildSQLArray($skill_array);

$subpage = preg_replace("/[^A-Za-z0-9 ]/", " ", $subpage);
$subpage = preg_replace('~[\x00\x0A\x0D\x1A\x22\x25\x27\x5C\x5F]~u', " ", $subpage);

$character_result = $connector->gamequery("SELECT " . $skills . ", openrsc_players.* FROM openrsc_experience LEFT JOIN openrsc_players ON openrsc_experience.playerID = openrsc_players.id WHERE (openrsc_players.id = '$subpage' OR openrsc_players.username = '$subpage')");
$character = $connector->fetchArray($character_result);

$totalTime = $connector->gamequery("SELECT SUM(`value`) FROM openrsc_player_cache AS B LEFT JOIN openrsc_players AS A ON B.playerID = A.id WHERE (A.id = '$subpage' OR A.username = '$subpage') AND B.key = 'total_played'");

$player_logins = $connector->gamequery("SELECT * FROM openrsc_logins AS B LEFT JOIN openrsc_players AS A ON B.playerID = A.id WHERE (A.id = '$subpage' OR A.username = '$subpage') ORDER BY B.time DESC LIMIT 30");

$player_chatlogs = $connector->gamequery("SELECT * FROM openrsc_chat_logs AS B LEFT JOIN openrsc_players AS A ON B.sender = A.username WHERE (A.id = '$subpage' OR A.username = '$subpage') ORDER BY B.time DESC LIMIT 100");

$player_pmlogs = $connector->gamequery("SELECT * FROM openrsc_private_message_logs AS B LEFT JOIN openrsc_players AS A ON B.sender = A.username OR B.reciever = A.username WHERE (A.id = '$subpage' OR A.username = '$subpage') ORDER BY B.time DESC LIMIT 100");

$player_tradelogs = $connector->gamequery("SELECT B.player1, B.player2, B.player1_items, B.player2_items, B.time FROM openrsc_trade_logs AS B LEFT JOIN openrsc_players AS A ON 'B.player1' = 'A.username' OR 'B.player2' = 'A.username' WHERE (A.id = '$subpage' OR A.username = '$subpage')");

$player_bank = $connector->gamequery("SELECT A.username, B.id, format(B.amount, 0) number, B.slot FROM `openrsc_bank` AS B LEFT JOIN openrsc_players AS A ON B.playerID = A.id WHERE (A.id = '$subpage' OR A.username = '$subpage') ORDER BY slot");

$player_invitems = $connector->gamequery("SELECT A.username, B.id, format(B.amount, 0) number, B.slot FROM `openrsc_invitems` AS B LEFT JOIN openrsc_players AS A ON B.playerID = A.id WHERE (A.id = '$subpage' OR A.username = '$subpage') ORDER BY slot ASC");

$player_feed = $connector->gamequery("SELECT * FROM openrsc_live_feeds AS B LEFT JOIN openrsc_players AS A ON B.username = A.username WHERE (A.id = '$subpage' OR A.username = '$subpage') ORDER BY B.time DESC LIMIT 30");

$player_gang = $connector->gamequery("SELECT value FROM openrsc_player_cache AS B LEFT JOIN openrsc_players AS A ON B.playerID = A.id WHERE B.key = 'arrav_gang' AND (A.id = '$subpage' OR A.username = '$subpage')");

function bd_nice_number($n)
{
	if ($n > 1000000000000) return round(($n / 1000000000000), 1) . ' trillion';
	else if ($n > 1000000000) return round(($n / 1000000000), 1) . ' billion';
	else if ($n > 1000000) return round(($n / 1000000), 1) . ' million';
	else if ($n > 1000) return round(($n / 1000), 1) . ' thousand';

	return number_format($n);
}

?>

<?php if ($character) { ?>
<div class="text-info table-dark" style="height: 100vh; width: 100vw;">
	<div class="border-left border-info border-right table-wrapper-scroll-y container">
		<div class="h2 text-center text-capitalize display-3" style="font-size: 38px;"><?php
			if ($character['group_id'] != 10): echo "<img class=\"pr-3 pb-2\" src=\"/img/$character[group_id].svg\" height=\"42\">";
			else: NULL; endif;
			echo $character['username']; ?></div>
		<div class="row sm-stats justify-content-center" style="text-transform: unset;">
			<div class="text-primary">
				<div class="row justify-content-center">

					<!-- Begin hide if highscore opt out unless admin or moderator -->
					<?php if ($character['highscoreopt'] == 1 && ($user->data['group_id'] == '3' || $user->data['group_id'] == '2' || $user->data['group_id'] == '7' || $user->data['group_id'] == '1') || $user->data['group_id'] == '6') { ?>
						<br/><h4 align="center">The player has decided to opt out of highscores</h4><br/>
					<?php } else {
						?>

						<div class="pl-3 pr-3 container">
							<div class="flex-row stats">
								<div class="display-glow">
									<?php
									$file = 'https://game.openrsc.com/avatars/' . $character['id'] . '.png';
									echo "<img src=\"$file\"/>";
									?>
								</div>

								<div id="sm-skill">
									<?php foreach ($skill_array as $skill) {
										if ($skill == 'hitpoints') {
											$skillc = 'hits';
										} else {
											$skillc = $skill;
										}
										?><span class="sm-skill"><a
										href="/highscores/<?php echo $skill; ?>"><img
											src="/img/skill_icons/<?php echo $skill; ?>.svg"
											height="20px" alt="<?php echo $skill; ?>"/>
										</a><?php echo experienceToLevel($character['exp_' . $skillc] / 4.0); ?>
										</span>
									<?php } ?>
								</div>

								<div id="sm-stats">
									<span class="sm-stats">Combat Level: <?php echo $character['combat']; ?></span>
									<span
										class="sm-stats">Skill Total: <?php echo $character['skill_total']; ?></span>
									<span class="sm-stats">Gang: <?php
										if(mysqli_num_rows($player_gang)===0)
										{
											echo 'None';
											}
										else {
											while ($row = $connector->fetchArray($player_gang)) {
												$gang = $row["value"];
												if ($gang == 0) {
													$pick = 'Black Arm';
												} else {
													$pick = 'Phoenix';
												}
												echo $pick;
											}
										}?>
										</span>
									<span class="sm-stats">Time Played: <?php
										while ($row = $connector->fetchArray($totalTime)) {
											$time = $row["SUM(`value`)"] / 1000;
											$days = floor($time / (24 * 60 * 60));
											$hours = floor(($time - ($days * 24 * 60 * 60)) / (60 * 60));
											$minutes = floor(($time - ($days * 24 * 60 * 60) - ($hours * 60 * 60)) / 60);
											$seconds = ($time - ($days * 24 * 60 * 60) - ($hours * 60 * 60) - ($minutes * 60)) % 60;
											echo $days . 'd ' . $hours . 'h ' . $minutes . 'm ';
										} ?>
										</span>
									<span class="sm-stats">Status:
											<?php if ($character['online'] == 1) {
												echo '<span class="green"><strong>Online</strong></span>';
											} else {
												echo '<span class="red"><strong>Offline</strong></span>';
											} ?>
										</span>
									<span
										class="sm-stats">Last Online: <?php date_default_timezone_set('America/New_York');
										echo strftime("%b %d, %I:%M %p", $character["login_date"]) ?>
										</span>
								</div>
							</div>
						</div>

						<br/>

						<div>
							<div class="stats pl-5 pr-5">
								<div class="h4 text-info">Recent Accomplishments</div>
								<?php while ($row = $connector->fetchArray($player_feed)) {
									echo '[<small>' . strftime("%d %b / %H:%M %Z", $row["time"]) . '</small>] <strong>' . $row["username"] . '</strong> ' . $row["message"];
									echo '<br/>';
								} ?>
							</div>
						</div>

						<br/>

						<!-- Begin admin and moderator view only -->
						<?php //if ($user->data['group_id'] == '5' || $user->data['group_id'] == '4') { ?>
						<div class="pt-3">
							<div class="stats pl-5 pr-5">
								<div class="h4 text-info">Inventory</div>
								<table style="background: rgba(255,255,255,0.2); border-collapse: collapse;">
									<?php $invitems = $connector->num_rows($player_invitems); ?>
									<tr>
										<?php
										if ($invitems == 0) {
											echo "No inventory items found.";
										} else {
											for ($i = 1; $list = $connector->fetchArray($player_invitems); $i++) {
												?>
												<td style="border: 1px solid black;">
													<div class="clickable-row item<?php echo $list['id'] ?>" data-href="/itemdef/<?php echo $list['id'] ?>"
														 style="-webkit-text-fill-color: limegreen; -webkit-text-stroke-width: 1px; -webkit-text-stroke-color: black; margin-top: 0px; position: relative; color: white; font-size: 13px; font-weight: 900;">
														<?php echo $list["number"]; ?>
													</div>
												</td>
												<?php
												if (($i % 10 == 0) && ($i < $invitems)) {
													echo '</tr><tr>';
												}
											}
										} ?>
									</tr>
								</table>
							</div>

							<br/>

							<div class="stats pl-5 pr-5">
								<div class="h4 text-info">Bank:</div>
								<table style="background: rgba(255,255,255,0.2); border-collapse: collapse;">
									<?php $bank = $connector->num_rows($player_bank); ?>
									<tr>
										<?php
										if ($bank == 0) {
											echo "No bank items found.";
										} else {
											for ($i = 1; $list = $connector->fetchArray($player_bank); $i++) {
												?>
												<td style="border: 1px solid black;">
													<div class="clickable-row item<?php echo $list['id'] ?>" data-href="/itemdef/<?php echo $list['id'] ?>"
														 style="-webkit-text-fill-color: limegreen; -webkit-text-stroke-width: 1px; -webkit-text-stroke-color: black; margin-top: 0px; position: relative; color: white; font-size: 13px; font-weight: 900;">
														<?php echo $list["number"]; ?>
													</div>
												</td>
												<?php
												if (($i % 10 == 0) && ($i < $bank)) {
													echo '</tr><tr>';
												}
											}
										} ?>
									</tr>
								</table>
							</div>

							<br/>

							<div class="stats pl-5 pr-5">
								<div class="h4 text-info">Logins and IPs:</div>
								<table style="background: rgba(255,255,255,0.3); border-collapse: collapse;">
									<?php $logins = $connector->num_rows($player_logins); ?>
									<tr>
										<?php
										if ($logins == 0) {
											echo "No login logs found.";
										} else {
											for ($i = 1; $list = $connector->fetchArray($player_logins); $i++) {
												echo '[<small>' . strftime("%d %b / %H:%M %Z", $list["time"]) . '</small>] <b>' . $list["ip"] . '</b>';
												echo '<br/>';
												if (($i % 14 == 0) && ($i < $logins)) {
													echo '</tr><tr>';
												}
											}
										} ?>
									</tr>
								</table>
							</div>

							<br/>

							<div class="stats pl-5 pr-5">
								<div class="h4 text-info">Chat Logs:</div>
								<table class=style="background: rgba(255,255,255,0.3); border-collapse: collapse;
								">
								<?php $chat = $connector->num_rows($player_chatlogs); ?>
								<tr>
									<?php
									if ($chat == 0) {
										echo "No chat logs found.";
									} else {
										for ($i = 1; $list = $connector->fetchArray($player_chatlogs); $i++) {
											echo '[<small>' . strftime("%d %b / %H:%M %Z", $list["time"]) . '</small>] <span style="color: #F5FA3C; text-shadow: 1px 1px black;">' . $list["message"] . '</span>';
											echo '<br/>';
											if (($i % 14 == 0) && ($i < $chat)) {
												echo '</tr><tr>';
											}
										}
									} ?>
								</tr>
								</table>
							</div>

							<br/>

							<div class="stats pl-5 pr-5">
								<div class="h4 text-info">PM Logs:</div>
								<table style="background: rgba(255,255,255,0.3); border-collapse: collapse;">
									<?php $pm = $connector->num_rows($player_pmlogs); ?>
									<tr>
										<?php
										if ($pm == 0) {
											echo "No private message logs found.";
										} else {
											for ($i = 1; $list = $connector->fetchArray($player_pmlogs); $i++) {
												$idLinkSender = preg_replace("/[^A-Za-z0-9]/", "-", $list['sender']);
												$idLinkReciever = preg_replace("/[^A-Za-z0-9]/", "-", $list['reciever']);
												echo '[<small>' . strftime("%d %b / %H:%M %Z", $list["time"]) . '</small>] from <b><a href="/player/' . $idLinkSender . '" target="_blank">' . $list["sender"] . '</a></b> to <b><a href="/player/' . $idLinkReciever . '" target="_blank">' . $list["reciever"] . '</a></b>: <span class="text-info" style="text-shadow: 1px 1px black;">' . $list["message"] . '</span>';
												echo '<br/>';
												if (($i % 14 == 0) && ($i < $pm)) {
													echo '</tr><tr>';
												}

											}
										} ?>
									</tr>
								</table>
							</div>

							<br/>

							<div class="stats pl-5 pr-5">
								<div class="h4 text-info">Trade Logs:</div>
								<table style="background: rgba(255,255,255,0.3); border-collapse: collapse;">
									<?php $trade = $connector->num_rows($player_tradelogs); ?>
									<tr>
										<?php
										if ($trade == 0) {
											echo "No trade logs found. This is currently not functioning and under development.";
										} else {
											for ($i = 1; $list = $connector->fetchArray($player_tradelogs); $i++) {
												echo '[<small>' . strftime("%d %b / %H:%M %Z", $list["time"]) . '</small>] from <b>' . $list["player1"] . '</b> to <b>' . $list["player2"] . '</b>';
												?>
												<td style="border: 1px solid black;">
													<?php echo $list["player1_items"]; ?>
												</td>
												<td style="border: 1px solid black;">
													<?php echo $list["player2_items"]; ?>
												</td>
												<?php
												if (($i % 14 == 0) && ($i < $trade)) {
													echo '</tr><tr>';
												}
											}
										} ?>
									</tr>
								</table>
							</div>
						</div>

						<?php //} else {
						//} ?>
						<!-- End admin and moderator view only -->

					<?php } ?>
					<!-- End player opt out view else -->
				</div>
				<br><br><br><br>
				<?php } else {
					echo "<div class='h4' align='center'>Player not found</div>";
				} ?>
