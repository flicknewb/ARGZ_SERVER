<?php
include("db_connect.php");
$return_array = array();

$id = isset($_POST['id']) ? protect($_POST['id']) : '';
$survivor_id = isset($_POST['survivor_id']) ? protect($_POST['survivor_id']) : '';

if ($id <> '') {
    if ($survivor_id <> '') {
        //find the team member's position
        $surv_query = mysql_query("SELECT * FROM survivor_roster WHERE entry_id='$survivor_id' AND owner_id='$id'") or die(mysql_error());
        $row = mysql_fetch_assoc($surv_query);
        $team_pos = $row['team_position'];
        //promote a survivor into his team position
        $update_replacement = mysql_query("UPDATE survivor_roster SET team_position='$team_pos' WHERE owner_id='$id' AND onMission=0 AND injured=0 AND team_position=0") or die(mysql_error());
        
        //create the injured record and update the survivor record
        $now = 'now()';
        $duration = rand(4,8);
        $interval_string = "interval $duration days";
        $stam_loss = rand(10,20);
        $attk_loss = rand(3,7);
        $injured_update = mysql_query("INSERT INTO injury_table (owner_id, survivor_id, expire_time, stam_loss, attk_loss) VALUES ('$id', '$survivor_id', date_add($now, $interval_string), '$stam_loss', '$attk_loss')") or die(mysql_error());
        $row1 = mysql_fetch_assoc($injured_update);
        $injury_id = $row1['entry_id'];
        $survivor_update = mysql_query("UPDATE survivor_roster SET injured='$injury_id', isActive=0, team_position=0 WHERE owner_id='$id' AND entry_id='$survivor_id'") or die(mysql_error());
        
        if (mysql_affected_rows() > 0 ) {
            array_push($return_array, "Success");
            array_push($return_array, "survivor records modified and injury added to DB");
        }
    }else{
        array_push($return_array, "Failed");
        array_push($return_array, "Survivor ID not set");
    }
}else{
    array_push($return_array, "Failed");
    array_push($return_array, "Player ID not set");
}
$jsonreturn = json_encode($return_array);
echo $jsonreturn;
?>