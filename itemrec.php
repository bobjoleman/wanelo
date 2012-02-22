<?php
session_start();
require_once("connect.php");

function array_count_values_of($value, $array) {
    $counts = array_count_values($array);
    return $counts[$value];
}

function check_pairs($first, $second, $array1, $array2) {
	if (!empty($array1)){
		$keys = array_keys($array1, $first);
		$keys2 = array_keys($array1, $second);
	}
	else {return False;}
	$yes = 0;
	if (!empty($keys)) {
		foreach ($keys as $key) {
			if ($array2[$key]==$second) {
				$yes = 1;
				}
			}
		}
	if (!empty($keys2)) {
		foreach ($keys2 as $key2) {
			if ($array2[$key2]==$first) {
				$yes = 1;
				}
			}
		}
	if ($yes==0) {return False;}
	else {return True;}
}

//redefine as session var later
$user = "Tony";
$user_dislikes = array();
$nchoice = 0;

if (!empty($_POST[fpair])) {
	$q = mysql_query("INSERT INTO $user (first_item,second_item,chosen_item) VALUES ('$_POST[fpair]', '$_POST[spair]', '$_POST[choice]')");
	$qq = mysql_query("SELECT first_item, total_pairings, total_first_items FROM master WHERE (first_item = '$_POST[fpair]' AND second_item = '$_POST[spair]')"); 
	if (mysql_num_rows($qq) != 0) {
		$r = mysql_result($qq, 0, 'total_pairings');
		$new_r = $r+1;
		$qqq = mysql_query("UPDATE master SET total_pairings = $new_r WHERE (first_item = '$_POST[fpair]' AND second_item = '$_POST[spair]')");
		$f = mysql_result($qq, 0, 'first_item');
		if ($_POST['choice'] == $f) {
			$s = mysql_result($qq, 0, 'total_first_items');
			$new_s = $s+1;
			$qqqq = mysql_query("UPDATE master SET total_first_items = $new_s WHERE (first_item = '$_POST[fpair]' AND second_item = '$_POST[spair]')");
		}
	
	}
	else { 
		if (mysql_num_rows($qq) != 0) {
		$qq = mysql_query("SELECT second_item, total_pairings, total_first_items FROM master WHERE (first_item = '$_POST[spair]' AND second_item = '$_POST[fpair]')");
		$r = mysql_result($qq, 0, 'total_pairings');
		$new_r = $r+1;
		$qqq = mysql_query("UPDATE master SET total_pairings = $new_r WHERE (first_item = '$_POST[spair]' AND second_item = '$_POST[fpair]')");
		$f = mysql_result($qq, 0, 'second_item');
		if ($_POST['choice'] == $f) {
			$s = mysql_result($qq, 0, 'total_first_items');
			$new_s = $s+1;
			$qqqq = mysql_query("UPDATE master SET total_first_items = $new_s WHERE (first_item = '$_POST[spair]' AND second_item = '$_POST[fpair]')");
		}
		}
	}
}

$user_dislikes1 = mysql_query("SELECT second_item FROM $user WHERE first_item = chosen_item");
$user_dislikes2 = mysql_query("SELECT first_item FROM $user WHERE second_item = chosen_item");

$user_first = mysql_query("SELECT first_item FROM $user");
$user_second = mysql_query("SELECT second_item FROM $user");

$user_first_row = array();
$user_second_row = array();

while ($user_first_row1 = mysql_fetch_array($user_first)) {
	array_push($user_first_row, $user_first_row1['first_item']);
}
while ($user_second_row1 = mysql_fetch_array($user_second)) {
	array_push($user_second_row, $user_second_row1['second_item']);
}

while ($row = mysql_fetch_array($user_dislikes1)) {
	array_push($user_dislikes, $row['second_item']);
}

while ($roww = mysql_fetch_array($user_dislikes2)) {
	array_push($user_dislikes, $roww['first_item']);
}

$dislike_tolerance = 1;	
while ($dislike_tolerance<=7) {	
$query = mysql_query("SELECT first_item, second_item FROM master");

	while ($rowww = mysql_fetch_array($query)) {
		if ((array_count_values_of($rowww['first_item'], $user_dislikes))>=$dislike_tolerance) {
			continue;
			}
		elseif ((array_count_values_of($rowww['second_item'], $user_dislikes))>=$dislike_tolerance) {
			continue;
			}
		else {
			$first = $rowww['first_item'];
			$queryy = mysql_query("SELECT first_item, second_item, total_pairings, total_first_items FROM master WHERE first_item = '$first'");
			while ($rowwww = mysql_fetch_array($queryy)) {
				if (($rowwww['total_pairings']==0) && ((array_count_values_of($rowwww['second_item'], $user_dislikes))<$dislike_tolerance)  && (check_pairs($first, $rowwww['second_item'], $user_first_row, $user_second_row)==0)) {
					$choice = $rowwww['second_item'];
					}
				elseif ((($rowwww['total_first_items']/$rowwww['total_pairings'])>$nchoice) && ((array_count_values_of($rowwww['second_item'], $user_dislikes))<$dislike_tolerance) && (check_pairs($first, $rowwww['second_item'], $user_first_row, $user_second_row)==0)) {
					$nchoice = ($rowwww['total_first_items']/$rowwww['total_pairings']);
					$choice = $rowwww['second_item'];
					}
				}
			
			$queryyy = mysql_query("SELECT first_item, second_item, total_pairings, total_first_items FROM master WHERE second_item = '$first'");
			while ($rowwwww = mysql_fetch_array($queryyy)) {
				if (($rowwwww['total_pairings']==0) && ((array_count_values_of($rowwwww['first_item'], $user_dislikes))<$dislike_tolerance) && (check_pairs($first, $rowwwww['first_item'], $user_first_row, $user_second_row)==0)) {
					$choice = $rowwwww['first_item'];
					}
				elseif (((1-($rowwwww['total_first_items']/$rowwwww['total_pairings']))>$nchoice) && ((array_count_values_of($rowwwww['first_item'], $user_dislikes))<$dislike_tolerance) && (check_pairs($first, $rowwwww['first_item'], $user_first_row, $user_second_row)==0)) {
					$nchoice = ($rowwwww['total_first_items']/$rowwwww['total_pairings']);
					$choice = $rowwwww['first_item'];
					}
				}	
			if (!empty($choice)) {
				break;
			}
			}
	}
	if (!empty($choice)) {
				break;
			}
	$dislike_tolerance++;
}
?>

<html>
<head>
<title>Itemrec Testing</title>
</head>
<body>
<center>
<h1>Click the item you like better!</h1>
<form name="choose" id="choose" method="post" action="itemrec.php">
<table><tr><td>
 
<input type="hidden" name="choice" id="choice" />
<input type="hidden" name="fpair" id="fpair" value="<? echo $first ?>" />
<input type="hidden" name="spair" id="spair" value="<? echo $choice ?>" />

<a href="javascript:document.getElementById('choice').value='<? echo $first ?>'; document.forms['choose'].submit()">
<? echo $first ?></a>
</td><td>
<a href="javascript:document.getElementById('choice').value='<? echo $choice ?>'; document.forms['choose'].submit()">
<? echo $choice ?></a>
</td></tr></table>


</form>
</center>
</body>
</html>



