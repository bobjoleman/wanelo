<?php
session_start();
require_once("connect.php");

//This is a basic choice engine with both popular and personal elements.

//function used to see how many times the item has been disliked
function array_count_values_of($value, $array) {
    $counts = array_count_values($array);
    return $counts[$value];
}

//function used to see if the user has been presented with this pair
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

function check_faves($first, $second, $luser) {
	$quick = mysql_query("SELECT faves FROM $luser");
	$fave_array = array();
	while ($fav = mysql_fetch_array($quick)) {
		array_push($fave_array, $fav['faves']);
	}
	if (!empty($fave_array)) {
		if (in_array($first, $fave_array) != FALSE) {
			return TRUE;
			}
		elseif (in_array($second, $fave_array) == FALSE) {
			return FALSE;
			}
		else {return TRUE;}
	}
}

//redefine as session var later
$user = "Tony";

$user_dislikes = array();
//probabilities
$nchoice = 0;

//updates user and master tables with previous pair and choice
//checks for (item 1, item 2) and (item 2, item 1), which is why there's an if/else
if (!empty($_POST[fpair])) {
	
	//default first item is last item picked; used in later loop
	$globalchoice = $_POST[choice];
	
	$q = mysql_query("INSERT INTO $user (first_item,second_item,chosen_item) VALUES ('$_POST[fpair]', '$_POST[spair]', '$_POST[choice]')");
	$qq = mysql_query("SELECT first_item, total_pairings, total_first_items FROM master WHERE (first_item = '$_POST[fpair]' AND second_item = '$_POST[spair]')"); 
	$qqr = mysql_fetch_array($qq);
	if (!empty($qqr)) {
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
		$qq = mysql_query("SELECT second_item, total_pairings, total_first_items FROM master WHERE (first_item = '$_POST[spair]' AND second_item = '$_POST[fpair]')");
		$qqr = mysql_fetch_array($qq);
		if (!empty($qqr)) {
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
	//check for 3 instances of chosen item, add to favorite list
	$qamble = mysql_query("SELECT COUNT(*) FROM $user WHERE chosen_item = '$_POST[choice]'");
	$row1 = mysql_fetch_array($qamble);
	$count1 = $row1[0];
	if ($count1>2) {
		$querulous = mysql_query("INSERT INTO $user (faves) VALUES ('$_POST[choice]')");
		$qerble = mysql_query("SELECT COUNT(*) FROM $user WHERE faves IS NOT NULL");
		$quibble = mysql_query("SELECT faves FROM $user WHERE (faves IS NOT NULL) AND (faves != '$POST_choice]')");
		$row2 = mysql_fetch_array($qerble);
		$count2 = $row2[0];
		if ($count2>1) {
			$fave_item1 = $_POST[choice];
			foreach (range(0, ($count2-1)) as $num) {
				$fave_item2 = mysql_result($quibble, $num);
				$quandary = mysql_query("SELECT fave_marks FROM master WHERE (first_item = '$fave_item1' AND second_item = '$fave_item2')");
				$quandaryrow = mysql_fetch_array($quandary);
				if (!empty($quandaryrow)) {
					$ferv = mysql_result($quandary, 0);
					$new_ferv = $ferv+1;
					$requandary = mysql_query("UPDATE master SET fave_marks = $new_ferv WHERE (first_item = '$fave_item1' AND second_item = '$fave_item2')");
					}
				$quandary2 = mysql_query("SELECT fave_marks FROM master WHERE (second_item = '$fave_item1' AND first_item = '$fave_item2')");
				$quandary2row = mysql_fetch_array($quandary2);
				if (!empty($quandary2row)) {
					$ferv2 = mysql_result($quandary2, 0);
					$new_ferv2 = $ferv2+1;
					$requandary2 = mysql_query("UPDATE master SET fave_marks = $new_ferv2 WHERE (second_item = '$fave_item1' AND first_item = '$fave_item2')");
					}

			}
		}
	//select items that are often favorited together with $_POST[choice]
	$quizzical = mysql_query("SELECT second_item, fave_marks FROM master WHERE first_item = '$_POST[choice]'");
	$quizzical2 = mysql_query("SELECT first_item, fave_marks FROM master WHERE second_item = '$_POST[choice]'");
	$nquiz = array();
	$nquiz2 = array();
	$quiz = array();
	$quiz2 = array();
	
	while ($quizzy = mysql_fetch_array($quizzical)) {
		array_push($nquiz, $quizzy['fave_marks']);
		array_push($quiz, $quizzy['second_item']);
		}
	while ($quizzy2 = mysql_fetch_array($quizzical2)) {
		array_push($nquiz2, $quizzy2['fave_marks']);
		array_push($quiz2, $quizzy2['first_item']);
		}
	if ((!empty($nquiz)) && (!empty($nquiz2))) {
		if (max($nquiz)>=max($nquiz2)) {
			$x = array_search(max($nquiz), $nquiz);
			$globalchoice = $quiz[$x];
			}
		else {
			$x = array_search(max($nquiz2), $nquiz2);
			$globalchoice = $quiz2[$x];
			}
		}
	}

}
//fills user_dislikes and all-user-choices arrays
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

//dislike_tolerance starts at 1 and iterates to 7, or whatever value you want
$dislike_tolerance = 1;	

//main while loop
while ($dislike_tolerance<=7) {	
	//query master randomly
	$query = mysql_query("SELECT first_item, second_item FROM master ORDER BY RAND() LIMIT 1");

	//iterate through master array, dispose of items disliked more than dislike_tolerance
	while ($rowww = mysql_fetch_array($query)) {
		//try to use previous choice
		if (isset($globalchoice)) {
			$first = $globalchoice;
			}
		else {
			if ((array_count_values_of($rowww['first_item'], $user_dislikes))>=$dislike_tolerance) {
				continue;
				}
			elseif ((array_count_values_of($rowww['second_item'], $user_dislikes))>=$dislike_tolerance) {
				continue;
				}
			
			$first = $rowww['first_item'];
			}
			//get array where first item is $first
			$queryy = mysql_query("SELECT first_item, second_item, total_pairings, total_first_items FROM master WHERE first_item = '$first'");
			while ($rowwww = mysql_fetch_array($queryy)) {
				if (($rowwww['total_pairings']==0) && ((array_count_values_of($rowwww['second_item'], $user_dislikes))<$dislike_tolerance)  && (check_pairs($first, $rowwww['second_item'], $user_first_row, $user_second_row)==0) && (check_faves($first, $rowwww['second_item'], $user) == 0)) {
					$choice = $rowwww['second_item'];
					break;
					}
				elseif ((($rowwww['total_first_items']/$rowwww['total_pairings'])>=$nchoice) && ((array_count_values_of($rowwww['second_item'], $user_dislikes))<$dislike_tolerance) && (check_pairs($first, $rowwww['second_item'], $user_first_row, $user_second_row)==0) && (check_faves($first, $rowwww['second_item'], $user) == 0)) {
					$nchoice = ($rowwww['total_first_items']/$rowwww['total_pairings']);
					$choice = $rowwww['second_item'];
					}
				}
			
			$queryyy = mysql_query("SELECT first_item, second_item, total_pairings, total_first_items FROM master WHERE second_item = '$first'");
			while ($rowwwww = mysql_fetch_array($queryyy)) {
				if (($rowwwww['total_pairings']==0) && ((array_count_values_of($rowwwww['first_item'], $user_dislikes))<$dislike_tolerance) && (check_pairs($first, $rowwwww['first_item'], $user_first_row, $user_second_row)==0) && (check_faves($first, $rowwwww['first_item'], $user) == 0)) {
					$choice = $rowwwww['first_item'];
					break;
					}
				elseif (((1-($rowwwww['total_first_items']/$rowwwww['total_pairings']))>=$nchoice) && ((array_count_values_of($rowwwww['first_item'], $user_dislikes))<$dislike_tolerance) && (check_pairs($first, $rowwwww['first_item'], $user_first_row, $user_second_row)==0) && (check_faves($first, $rowwwww['first_item'], $user) == 0)) {
					$nchoice = ($rowwwww['total_first_items']/$rowwwww['total_pairings']);
					$choice = $rowwwww['first_item'];
					}
				}
			if (!empty($choice)) {
				break;
			}
	}
	if (!empty($choice)) {
				break;
			}
	unset($globalchoice);
	$dislike_tolerance++;
}

if (empty($choice)) {
	echo "you've ran out of choices! check back soon!";
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



