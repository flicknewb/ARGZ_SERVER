<?php
include("db_connect.php");
$return_array = array();

if(isset($_POST["id"])) {
    if(isset($_POST["survivor_id"])) {
        if(isset($_POST["weapon_id"])) {
            $owner_id = protect($_POST["id"]);
            $survivor_id = protect($_POST["survivor_id"]);
            $weapon_id = protect($_POST["weapon_id"]);

            //find the weapon to be equipped
            $weapon_query = mysql_query("SELECT * FROM active_weapons WHERE weapon_id='$weapon_id' AND owner_id='$owner_id'") or die(mysql_error());
            //find the survivor equipping the weapon
            $survivor_query = mysql_query("SELECT * FROM survivor_roster WHERE entry_id='$survivor_id' AND owner_id='$owner_id'") or die(mysql_error());

            //verify there is only one of these weapons
            if (mysql_num_rows($weapon_query) > 0 && mysql_num_rows($weapon_query) < 2) {
                $weapon_data = mysql_fetch_assoc($weapon_query);
                $survivor_data = mysql_fetch_assoc($survivor_query);
                //find out if someone is already holding this weapon, or the survivor already has a different weapon.
                $equipped_survivor_id = $weapon_data['equipped_id'];
                $previous_equipped_weapon_id = $survivor_data['weapon_equipped'];

                //this checks if the weapon already belongs to another survivor.
                $rec_unequips=0;
                if ($equipped_survivor_id > 0) {
                    //Set the survivor to unequipped.
                    $unequip_old_survivor_update = mysql_query("UPDATE survivor_roster SET weapon_equipped=0 WHERE owner_id='$owner_id' AND entry_id='$equipped_survivor_id'") or die(mysql_error());
                    if (mysql_affected_rows()>0) {
                        $rec_unequips++;
                    }
                }
                //check for a previously equipped weapon on the survivor
                if ($previous_equipped_weapon_id > 0) {
                    //set the weapon to unequipped.
                    $unequip_old_weapon_update = mysql_query("UPDATE active_weapons SET equipped_id=0 WHERE owner_id='$owner_id' AND weapon_id='$previous_equipped_weapon_id'") or die(mysql_query());
                    if (mysql_affected_rows()>0) {
                        $rec_unequips++;
                    }
                }

                //update the weapon to it's new survivor
                $new_weapon_update = mysql_query("UPDATE active_weapons SET equipped_id='$survivor_id' WHERE owner_id='$owner_id' AND weapon_id='$weapon_id'") or die(mysql_error());
                
                if (mysql_affected_rows() > 0) {
                    
                    //update the survivor record.
                    $survivor_update = mysql_query("UPDATE survivor_roster SET weapon_equipped='$weapon_id' WHERE owner_id='$owner_id' AND entry_id='$survivor_id'") or die(mysql_error());
                    if (mysql_affected_rows() > 0) {
                        array_push($return_array, "Success");
                        array_push($return_array, "Weapon successfully equipped");
                    }else {
                        array_push($return_array, "Failed");
                        array_push($return_array, "unable to update the surivor record");
                    }
                } else {
                    array_push($return_array, "Failed");
                    array_push($return_array, "unable to update the weapon record");
                }
                

            } else {
                array_push($return_array, "Failed");
                array_push($return_array, "server returning more than one of this exact weapon");

            }
        }else{
            array_push($return_array, "Failed");
            array_push($return_array, "Weapon ID not set");
        }
    } else {
        array_push($return_array, "Failed");
        array_push($return_array, "survivor id not set");
    }

} else {
    
    array_push($return_array, "Failed");
    array_push($return_array, "user id not set");
}
array_push($return_array, $rec_unequips);
$json_return = json_encode($return_array);
echo $json_return;

// EquipWeapon.php
?>