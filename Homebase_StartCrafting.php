<?php
include("db_connect.php");

$return_array = array();

$id = isset($_POST['id']) ? protect($_POST['id']) : '';
$wep_name = isset($_POST['wep_name']) ? protect($_POST['wep_name']) : '';
$wd_cost = isset($_POST['wood_cost']) ? protect($_POST['wood_cost']) : '';
$mt_cost = isset($_POST['metal_cost']) ? protect($_POST['metal_cost']) : '';
$duration = isset($_POST['duration']) ? protect($_POST['duration']) : '';
$index = isset($_POST['weapon_index']) ? protect($_POST['weapon_index']) : '';

if ($id <> '') {
    if($wep_name <> '') {

    //I would rather see this:
    // $weapon = mysql_query("SELECT * from weapon_list WHERE type=$type") or die(mysql_error());
    // if(mysql_num_rows() > 0) {
    //  $row = mysql_fetch_assoc($weapon);
    //  $type = $row['type'];
    //  $cost = $row['cost'];
    //  $duration = $row['duration'];

    //Subtract the cost from the homebase_sheet
    $update1 = mysql_query("UPDATE homebase_sheet SET wood=wood - $wd_cost, metal=metal-$mt_cost WHERE id='$id' AND wood >= $wd_cost AND metal >= $mt_cost") or die(mysql_error());
    if(mysql_affected_rows() > 0) {
        $start = 'now()'; // simply use the mysql function

        //find the last weapon from this user that haven't been completed
        $query1 = mysql_query("SELECT time_complete FROM weapon_crafting WHERE time_complete > NOW() AND id='$id' ORDER BY time_complete DESC LIMIT 1") or die(mysql_error());

        if (mysql_num_rows($query1) > 0) {
            //take the 1 entry, and add this new weapon 
            $row = mysql_fetch_assoc($query1);
            $start = "'".$row['time_complete']."'"; // we just need to add quotes around it...
        }

        $interval_string = "interval $duration minute";
        $insert1 = mysql_query("INSERT INTO weapon_crafting (id, type, duration, time_complete, weapon_index) VALUES ('$id', '$wep_name', '$duration', date_add($start, $interval_string), '$index')") or die(mysql_error());
        if(mysql_affected_rows() > 0 ){        
            array_push($return_array, "Success");
            array_push($return_array, "weapon added to craft cue");
        } else {
            array_push($return_array, "Failed");
            array_push($return_array, "failed to add the weapon to the crafting DB");
        }
        
    }else{
        array_push ($return_array, "Failed");
        array_push ($return_array, "player does not have enough supply");
    }
    } else {
       array_push ($return_array, "Failed");
        array_push ($return_array, "wepon name not set");
    }
} else {
    array_push ($return_array, "Failed");
    array_push ($return_array, "player ID not set");
}

$jsonReturn = json_encode($return_array);
echo $jsonReturn;

// include("db_connect.php");

// $return_array = array();

// if (isset($_POST['id'])) {
//     if(isset($_POST['type'])) {
//         $id = protect($_POST['id']);
//         $type = protect($_POST['type']);
//         $cost = protect($_POST['cost']);
//         $duration = protect($_POST['duration']);
//         $new_supply = protect($_POST['new_supply']);

//         $start = 'now()'; // simply use the mysql function

//         //find all other weapons from this user that haven't been completed
//         $query1 = mysql_query("SELECT time_complete FROM weapon_crafting WHERE time_complete > NOW() AND id='$id' ORDER BY time_complete DESC LIMIT 1") or die(mysql_error());

//         if (mysql_num_rows($query1) > 0) {
//             //take the 1 entry, and add this new weapon 
//             $row = mysql_fetch_assoc($query1);
//             $start = "'".$row['time_complete']."'"; // we just need to add quotes around it...
//         }
//         $interval_string = "interval $duration minute";
//         $insert1 = mysql_query("INSERT INTO weapon_crafting (id, type, duration, time_complete) VALUES ('$id', '$type', '$duration', date_add($start, $interval_string ))") or die(mysql_error());
        
//         //Subtract the cost from the homebase_sheet
//         if ($new_supply >= 0) {
//             $update1 = mysql_query("UPDATE homebase_sheet SET supply = supply - $cost WHERE id='$id'") or die(mysql_error());
//         }
//         array_push($return_array, "Success");
//         array_push($return_array, "Weapon added to craft cue");
//         $jsonReturn = json_encode($return_array);
//         echo $jsonReturn;
//     }else{
//         array_push($return_array, "Failed");
//         array_push($return_array, "type of weapon not sent");
//         $jsonReturn = json_encode($return_array);
//         echo $jsonReturn;
//     }
// } else {
//     echo "Player ID not set";
// }
?>