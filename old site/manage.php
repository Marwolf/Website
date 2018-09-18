<?php
        $phpbb_root_path = './board/';
        $phpEx = substr(strrchr(__FILE__, '.'), 1);
        require($phpbb_root_path . 'config.' . $phpEx);
        require 'header.php';
        require_once 'inc/db.php';


        //PAGE SELECTORS
        $pages = array('select', 'create');
        $view_page = isset($_GET ['view']) && in_array($_GET ['view'], $pages) ? $_GET ['view'] : 'select';
        $setting = isset($_GET['setting']) ? $_GET['setting'] : 'select';

        //LOAD AND FIND CHARACTERS
        $connector = new Dbc();
	$find_chars = $connector->gamequery("
                                SELECT id,username,combat,forum_active,creation_date
                                FROM rscd_players
                                WHERE owner = '" . $id .  "' 
                                ORDER BY creation_date");
        
        $fetch_char_info = $connector->gamequery("SELECT p.id,p.username,p.combat,p.quest_points,p.skill_total,p.highscoreopt,e.exp_attack,e.exp_defense,e.exp_strength,
        e.exp_hits,e.exp_ranged,e.exp_prayer,e.exp_magic,e.exp_cooking,e.exp_woodcut,e.exp_fletching,e.exp_fishing,
        e.exp_firemaking,e.exp_crafting,e.exp_smithing,e.exp_mining,e.exp_herblaw,e.exp_agility,e.exp_thieving FROM rscd_players AS p LEFT JOIN rscd_experience AS e ON e.user = p.id WHERE p.owner = '" . $id . "' AND p.forum_active = '1' LIMIT 1");

        $apply_char = $connector->fetch_assoc($fetch_char_info);

        //SQL RESULT OF ACTIVE CHARACTER
        $isActive = ($db->num_rows($fetch_char_info));

        // WHEN SETTING PLAYER ID
        $curr_char = isset($_GET['player']) && is_numeric($_GET['player']) ? intval(trim($_GET['player'])) : $apply_char['id'];

        /** TOTAL ACCOUNT SLOTS PER FORUM ACCOUNT ID **/
        $maximum_character_slots = 10;
        $my_character_slots = ($luna_user['character_slots'] > $maximum_character_slots ? 10 : $luna_user['character_slots']);
        $character_slots_remaining = $my_character_slots - $db->num_rows($find_chars);

        // Character renaming price
        //$RENAME_PRICE = 200;

        /** Stat Reduction Holder **/
        $validskills = 	array(
                0 => array('name' => 'attack', 'modify' => true),
                1 => array('name' => 'defense', 'modify' => true),
                2 => array('name' => 'strength', 'modify' => true),
                3 => array('name' => 'hits', 'modify' => false),
                4 => array('name' => 'prayer', 'modify' => true),
                5 => array('name' => 'ranged', 'modify' => true),
                6 => array('name' => 'magic', 'modify' => true)
                );

        //ACHIEVEMENTS DATA
        if($setting == 'achievements') 
        {
                if($curr_char != $apply_char['id'] && $luna_user['g_id'] != LUNA_ADMIN) 
                        redirect('char_manager.php?id='.$id);

                $achievement_data = $db->query("SELECT 1 FROM rscd_achievements");
                $total_achievements = $db->num_rows($achievement_data);

                $achievement_status = $db->query("SELECT 1 FROM rscd_achievement_progress WHERE user = '".$curr_char."'");
                $total_completed = $db->num_rows($achievement_status);

                $achievement_result = $db->query("SELECT a.dbid, a.name, a.description, a.extra, ap.completed, ap.unlocked FROM rscd_achievements AS a LEFT JOIN rscd_achievement_progress AS ap ON ap.id = a.dbid AND ap.user = '".$curr_char."' ORDER BY ap.unlocked DESC");

                // Function to calculate achievement progress percentages
                function get_achievement_percentage($completed, $total, $boolean = false) 
                {
                        $percentage = 0;
                        if($completed != 0) 
                                $percentage = ($completed / $total) * 100;
                        if($boolean == true)
                                return number_format($completed, 0) . " of " . number_format($total, 0) . " (" . number_format($percentage, 0) . "%)";
                        else 
                                return $percentage;
                }
        }

        else if($setting != 'achievements') {
                switch($setting) {
                        case "active":
                                if(isset($_POST['put_active']) && $view_page == 'select' && isset($curr_char)) 
                                {
                                        confirm_referrer('char_manager.php');

                                        $value = isset($_POST['put_active']) ? '1' : '0';
                                        if($value == 1) 
                                        {
                                                $db->query("UPDATE rscd_players SET forum_active='0' WHERE id = '". $apply_char['id'] . "'") or error('Unable to remove current active character', __FILE__, __LINE__, $db->error());
                                                $db->query("UPDATE rscd_players SET forum_active='1' WHERE id = '".$db->escape($curr_char)."'") or error('Unable to set active character', __FILE__, __LINE__, $db->error());
                                        }
                                        redirect('char_manager.php?id='.$id);
                                }
                        break;
                        case "highscore":
                                if(isset($_POST['highscore']) && $view_page == 'select' && isset($curr_char)) 
                                {
                                        confirm_referrer('char_manager.php');

                                        if($curr_char != $apply_char['id'] && $luna_user['g_id'] != LUNA_ADMIN) 
                                                redirect('char_manager.php?id='.$id);

                                        $result = $db->query("SELECT highscoreopt FROM rscd_players WHERE id = '" . $db->escape($curr_char) . "' AND owner = '" . $id . "'") or error('Unable to update highscore option', __FILE__, __LINE__, $db->error());
                                        if($db->num_rows($result) > 0)
                                        {
                                                $option = $db->fetch_assoc($result);
                                                $update = $option['highscoreopt'] == 0 ? 1 : 0;
                                                $db->query("UPDATE rscd_players SET highscoreopt = '" . $update . "' WHERE id = '" . $db->escape($curr_char) . "'");
                                                redirect('char_manager.php?id='.$id.'&setting=highscore');
                                        }
                                }
                        break;
                        case "change_password":
                                if($view_page == 'select' && isset($curr_char) && isset($_POST['change_password']))
                                {
                                        confirm_referrer('char_manager.php');	

                                        if($curr_char != $apply_char['id'] && $luna_user['g_id'] != LUNA_ADMIN) 
                                                redirect('char_manager.php?id='.$id);

                                        $find = $db->query("SELECT username,pass,password_salt FROM rscd_players WHERE rscd_players.id = '" . $db->escape($curr_char) . "' AND owner = '" . $id . "'");
                                        $arrayit = $db->fetch_assoc($find);
                                        if($db->num_rows($find) > 0)
                                        {
                                                $first_pass = isset($_POST['c_pass_1']) ? $_POST['c_pass_1'] : null;
                                                $second_pass = isset($_POST['c_pass_2']) ? $_POST['c_pass_2'] : null;
                                                $errors = array();
                                                if(empty($first_pass) || empty($second_pass))
                                                {
                                                        $errors[] = "Please fill in all the fields.";
                                                }
                                                if($first_pass != $second_pass)
                                                {
                                                        $errors[] = "Your passwords did not match.";
                                                }
                                                if(strlen($first_pass) < 4 || strlen($first_pass) > 16)
                                                {
                                                        $errors[] = "Your password must be at least 4 to 16 characters in length.";
                                                }
                                                if(count($errors) == 0)
                                                {
                                                        $new_salt = random_pass(16); // 8 default?

                                                        $salt = random_pass(16); // 8 default?                                                                          
                                                        $new_password_hash = game_hmac($new_salt.$first_pass, $HMAC_PRIVATE_KEY);

                                                        $db->query("UPDATE rscd_players SET pass= '" . $new_password_hash . "', password_salt='".$new_salt."' WHERE id = '" . $db->escape($curr_char) . "'") or die('Failed to update game character password');
                                                        redirect('char_manager.php?id='.$id.'&setting=change_password&saved=true');
                                                }
                                        } 
                                        else
                                        {
                                                $errors[] = "This character does not belong to you.";
                                        }
                                }	
                        break;
                        case "character_renaming":
                                if($view_page == 'select' && isset($_POST['character_rename']))
                                {
                                        confirm_referrer('char_manager.php');

                                        $current_name = isset($_POST['character_name']) && strlen($_POST['character_name']) <= 12 && preg_match("/^[a-zA-Z0-9\s]+?$/i", $_POST['character_name']) ? trim($_POST['character_name']) : null;
                                        $new_name = isset($_POST['new_name']) && preg_match("/^[a-zA-Z0-9\s]+?$/i", $_POST['new_name']) ? trim($_POST['new_name']) : null;
                                        $usernameHash = usernameToHash($new_name);
                                        $errors = array();
                                        if(empty($new_name))
                                        {
                                                $errors[] = "Please enter your new name.";
                                        }
                                        if(!preg_match("/^[a-zA-Z0-9\s]+?$/i", $new_name)) 
                                        {
                                                $errors[] = "Your character name can only contain letters, numbers, and spaces.";
                                        }
                                        if(strlen($new_name) < 2 || strlen($new_name) > 12) 
                                        {
                                                $errors[] = "Your new character name must be minimum 2 charcaters and maximum 12 characters in length.";
                                        }
                                        if (preg_match('/^Mod\s+/i', $new_name) || preg_match('/^Admin\s+/i', $new_name))
                                        {
                                                 $errors[] = "Sorry, your new name cannot contain \"Mod\" or \"Admin\" in it's username";
                                        }
                                        if(count($errors) == 0)
                                        {

                                                $character = $db->query("SELECT id, online, owner, banned FROM rscd_players WHERE username = '" . $db->escape($current_name) . "' AND owner = '". $id ."'");
                                                $check = $db->fetch_assoc($character);	
                                                if($db->num_rows($character) > 0) 
                                                {
                                                        if($check['banned'] != 0) {
                                                                $errors[] = "Banned characters cannot use this feature.";
                                                        } else {
                                                                $check_availability = $db->query("SELECT id FROM rscd_players WHERE username = '" . $db->escape($new_name) . "'");
                                                                if($db->num_rows($check_availability) > 0)
                                                                {
                                                                        $errors[] = "The name you are attempting to rename this character to already exists.";
                                                                } 
                                                                else 
                                                                {
                                                                        if($check['online'] == 0) 
                                                                        {
                                                                                        $db->query("UPDATE rscd_players SET username='" . $db->escape($new_name) . "' WHERE id ='" . $check['id'] . "'") or error('Failed to rename player username', __FILE__, __LINE__, $db->error());
                                                                                        $db->query("UPDATE rscd_players SET user = '" . $db->escape($usernameHash) . "' WHERE id='" . $check['id'] . "'");
                                                                                        $db->query("UPDATE rscd_experience SET user = '" . $db->escape($usernameHash) . "' WHERE id='" . $check['id'] . "'");
                                                                                        $db->query("UPDATE rscd_curstats SET user = '" . $db->escape($usernameHash) . "' WHERE id='" . $check['id'] . "'");
                                                                                        $db->query("UPDATE rscd_invitems SET user = '" . $db->escape($usernameHash) . "' WHERE user='" . $check['user'] . "'");
                                                                                        $db->query("UPDATE rscd_quests SET user = '" . $db->escape($usernameHash) . "' WHERE id='" . $check['id'] . "'");
                                                                                        $db->query("UPDATE rscd_auctions SET player = '" . $db->escape($new_name) . "' WHERE player='" . $check['id'] . "'");
                                                                                        $db->query("UPDATE rscd_friends SET user = '" . $db->escape($usernameHash) . "' WHERE user='" . $check['user'] . "'");
                                                                                        $db->query("UPDATE rscd_ignores SET user = '" . $db->escape($usernameHash) . "' WHERE user='" . $check['user'] . "'");

                                                                                        // Insert into name change table			
                                                                                        $db->query('INSERT INTO ' . GAME_BASE . 'name_changes (user, owner, old_name, new_name, date) VALUES('.intval($check['id']).', '.intval($check['owner']).', \''.$db->escape($current_name).'\',  \''.$db->escape($new_name).'\', '.time().')') or error('Unable to save character name change!', __FILE__, __LINE__, $db->error());
                                                                                        redirect('char_manager.php?id='.$id.'&setting=character_renaming&saved=true');                                                                                
                                                                        } 
                                                                        else 
                                                                        {
                                                                                $errors[] = "You need to stay logged out from the game during the renaming process.";
                                                                        }	
                                                                }
                                                        }
                                                } 
                                                else 
                                                {
                                                        $errors[] = "Character to be renamed doesn't exist or doesn't belong to you.";
                                                }
                                        }
                                }
                        break;
                        case "add":
                                if(isset($_POST['addcharacter']) && $view_page == 'create') 
                                {
                                        confirm_referrer('char_manager.php');

                                        $username = isset($_POST['char_name']) ? trim($_POST['char_name']) : null;
                                        $password_1 = isset($_POST['char_pass_1']) ? $_POST['char_pass_1'] : null;
                                        $password_2 = isset($_POST['char_pass_2']) ? $_POST['char_pass_2'] : null;
                                        $errors = array();
                                        if(empty($username) || empty($password_1) || empty($password_2))
                                        {
                                                $errors[] = "Please fill in every field in the registration form.";
                                        }
                                        if(!preg_match("/^[a-zA-Z0-9\s]+?$/i", $username))
                                        {
                                                $errors[] = "Your username can only contain regular letters, numbers and spaces.";
                                        }
                                        if (preg_match('/^Mod\s+/i', $username) || preg_match('/^Admin\s+/i', $username)){
                                        $errors[] = "Sorry, but you can not create a character that begins with \"Mod\" or \"Admin\"";
                                        }
                                        if(strlen($username) < 2 || strlen($username) > 12)
                                        {
                                                $errors[] = "Your username must be from 2 to 12 characters in length.";
                                        }
                                        if($password_1 != $password_2)
                                        {
                                                $errors[] = "Your passwords did not match.";
                                        }
                                        if(strlen($password_1) < 4 || strlen($password_1) > 16)
                                        {
                                                $errors[] = "Your password must be from 4 to 16 characters in length.";
                                        }
                                        if(!preg_match("/^[a-zA-Z0-9\s]+?$/i", $password_1))
                                        {
                                                $errors[] = "Your password can only contain regular letters, numbers and spaces.";
                                        }
                                        if(count($errors) == 0)
                                        {
                                                $check_name_in_use = $db->query("SELECT id FROM rscd_players WHERE username = '" . $db->escape($username) . "'");
                                                $check_user_amount = $db->num_rows($db->query("SELECT id FROM rscd_players WHERE owner = '" . $id . "'"));
                                                if($check_user_amount >= $my_character_slots)
                                                {
                                                        $errors[] = "Sorry you have reached your maximum limit of in-game characters (".$my_character_slots.").";
                                                }
                                                else
                                                {
                                                        if($db->num_rows($check_name_in_use) > 0)
                                                        {
                                                                $errors[] = "The username '" . luna_htmlspecialchars($username) . "' is already in use.";
                                                        }
                                                        else
                                                        {
                                                                $salt = random_pass(16); // 8 default?
                                                                $password_hash = game_hmac($salt.$password_1, $HMAC_PRIVATE_KEY);
                                                                $usernameHash = usernameToHash($username);

                                                                $db->query("INSERT INTO rscd_players (user,username,owner,pass,password_salt,creation_date,creation_ip) VALUES ('" . $db->escape($usernameHash) . "', '" . $db->escape($username) . "', '" . $id . "', '" . $password_hash . "', '" . $salt . "', '".(time())."', '". $_SERVER['REMOTE_ADDR'] ."');") or error('Unable to insert game character', __FILE__, __LINE__, $db->error());
                                                                $new_uid = $db->insert_id();
                                                                $db->query("INSERT INTO rscd_curstats (user) VALUES ('" . $usernameHash . "');") or error('Unable to insert current stats on game character', __FILE__, __LINE__, $db->error());
                                                                $db->query("INSERT INTO rscd_experience (user) VALUES ('" . $usernameHash . "');") or error('Unable to insert experience on game character', __FILE__, __LINE__, $db->error());
                                                                redirect('char_manager.php?id='.$id.'&view=create&saved=true');
                                                        }
                                                }
                                        }
                                }
                        break;
                        case "delete_character":
                                if ($luna_user['is_guest']) {
                                header('Location: index.php');
                                exit;
                                }
                                if (isset($_GET['key'])) {
                                        $key = $_GET['key'];
                                        $result = $db->query('SELECT * FROM '.$db->prefix.'users WHERE id='.$id) or error('Unable to fetch deletion', __FILE__, __LINE__, $db->error());
                                        $cur_user = $db->fetch_assoc($result);

                                        if ($key == '' || $key != $cur_user['activate_key'])
                                                message(__('The specified activation key was incorrect or has expired. Please re-request a new deletion. If that fails, contact the forum administrator at', 'luna').' <a href="mailto:'.luna_htmlspecialchars($luna_config['o_admin_email']).'">'.luna_htmlspecialchars($luna_config['o_admin_email']).'</a>.');

                                                $character_to_delete = $cur_user['activate_string'];
                                                $character_to_delete_query = $db->query('SELECT id,user,username FROM ' . GAME_BASE . 'players WHERE id='.$character_to_delete) or error('Unable to fetch deletion extra', __FILE__, __LINE__, $db->error());
                                                if (!$db->num_rows($character_to_delete_query)) 
                                                        message(__('User could not be found on your account', 'luna').'.');

                                                $cur_del_char = $db->fetch_assoc($character_to_delete_query);

                                                // DELETE CHARACTER
                                                $db->query("DELETE FROM rscd_players WHERE id = '" . $db->escape($character_to_delete) . "'");
                                                $db->query("DELETE FROM rscd_curstats WHERE id = '" . $db->escape($character_to_delete) . "'");
                                                $db->query("DELETE FROM rscd_experience WHERE id = '" .  $db->escape($character_to_delete) . "'");
                                                $db->query("DELETE FROM rscd_friends WHERE user = '" .  $db->escape($cur_del_char['user']) . "'");
                                                $db->query("DELETE FROM rscd_ignores WHERE user = '" .  $db->escape($cur_del_char['user']) . "'");
                                                $db->query("DELETE FROM rscd_invitems WHERE user = '" .  $db->escape($cur_del_char['user']). "'");
                                                $db->query("DELETE FROM rscd_quests WHERE id = '" .  $db->escape($character_to_delete) . "'");

                                                // UPDATE THE ACTIVATION KEYS TO NULL.
                                                $db->query('UPDATE '.$db->prefix.'users SET activate_string=NULL, activate_key=NULL WHERE id='.$id) or error('Unable to update user defaults', __FILE__, __LINE__, $db->error());
                                                message(__('Your character has been successfully deleted.', 'luna'), true);
                                } else {
                                        if (isset($_POST['delete_verify'])) {
                                                require LUNA_ROOT.'include/email.php';
                                                $result = $db->query('SELECT id, username, last_email_sent FROM '.$db->prefix.'users WHERE email=\''.$db->escape($luna_user['email']).'\'') or error('Unable to fetch user info', __FILE__, __LINE__, $db->error());
                                                $char_delete_result = $db->query('SELECT id, user, username, banned FROM ' . GAME_BASE . 'players WHERE id='.$db->escape($curr_char).' AND owner='.$db->escape($luna_user['id']).'') or error('Unable to find player character info', __FILE__, __LINE__, $db->error());
                                                if (!$db->num_rows($char_delete_result)) 
                                                        message(__('User could not be found on your account', 'luna').'.');

                                                $char_delete = $db->fetch_assoc($char_delete_result);
                                                if($char_delete['banned'] != 0)
                                                        message(__('Banned characters cannot use this feature', 'luna').'.');

                                                if ($db->num_rows($result)) {
                                                        // Load the "delete character" template
                                                        $mail_tpl = trim(__('Subject: Delete character requested

        Hello <username>,

        You have requested to have a game character deleted from your forum account at <base_url>. If you did not request this or if you do not want to delete this character you should just ignore this message. Only if you visit the activation page below will confirm the deletion.

        Character to be deleted: <char_delete>

        To confirm the deletion of your character, please click the activation url below:
        <activation_url>

        --
        <board_mailer> Service
        (Do not reply to this message)', 'luna'));

                                                        // The first row contains the subject
                                                        $first_crlf = strpos($mail_tpl, "\n");
                                                        $mail_subject = trim(substr($mail_tpl, 8, $first_crlf-8));
                                                        $mail_message = trim(substr($mail_tpl, $first_crlf));

                                                        // Do the generic replacements first (they apply to all emails sent out here)
                                                        $mail_message = str_replace('<base_url>', get_base_url().'/', $mail_message);
                                                        $mail_message = str_replace('<board_mailer>', $luna_config['o_board_title'], $mail_message);

                                                        // Loop through users we found
                                                        while ($cur_hit = $db->fetch_assoc($result)) {
                                                                $activation_key = random_pass(8);

                                                                $db->query('UPDATE '.$db->prefix.'users SET activate_string=\''.$db->escape($char_delete['id']).'\', activate_key=\''.$db->escape($activation_key).'\', last_email_sent = '.time().' WHERE id='.$cur_hit['id'])
                                                                or error('Unable to update activation data', __FILE__, __LINE__, $db->error());

                                                                // Do the user specific replacements to the template
                                                                $cur_mail_message = str_replace('<username>', $cur_hit['username'], $mail_message);
                                                                $cur_mail_message = str_replace('<activation_url>', get_base_url().'/char_manager.php?id='.$cur_hit['id'].'&view=select&setting=delete_character&key='.$activation_key, $cur_mail_message);
                                                                $cur_mail_message = str_replace('<char_delete>', $char_delete['username'], $cur_mail_message);

                                                                luna_mail($luna_user['email'], $mail_subject, $cur_mail_message);
                                                        }
                                                }
                                        }
                                }
                        break;
                }
        }
?>

<div class="main">
        <div class="content">
                <article>
                <?php 
                        if($view_page == 'select') 
                        { 
                                if($isActive) 
                                {
                                        require load_page('inc/character/active-character.php');
                                        switch($setting) 
                                        {
                                                case "change_password":
                                                        require load_page('character/change-password.php');
                                                break;
                                                case "character_renaming":
                                                        require load_page('character/rename-character.php');
                                                break;
                                                case "highscore":
                                                        require load_page('character/highscore.php'); 
                                                break;
                                                case "achievements":
                                                        require load_page('character/achievement.php'); 
                                                break;
                                                /*case "reduction":
                                                        require load_page('character/stat-reduction.php');
                                                break;*/
                                                default:
                                                        require load_page('character/select-character.php'); 
                                                break;
                                        }
                                } 
                                else 
                                {
                                        require load_page('inc/character/select-character.php'); 
                                }
                        } 
                        else if($view_page == 'create') 
                        { 
                                require load_page('inc/character/create-character.php');
                        }
                        else 
                        { 
                                echo 'Error entering character manager.';
                        }
                ?>
                </article>
        </div>
</div>
    
<?php
    include 'footer.php';
?>