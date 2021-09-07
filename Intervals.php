<?php 
$intervals = array('m2' ,'M2' ,'m3' ,'M3' ,'P4' ,'P5' ,'m6' ,'M6' ,'m7' ,'M7' ,'P8' );
/*deatonic degree is a number in name, and count of semitones is incremented position in array*/
$note_semitones = array('C' ,'C#' ,'Db' ,'D' ,'D#' ,'Eb' ,'E' ,'F' ,'F#' ,'Gb' ,'G' ,'G#' ,'Ab' , 'A' ,'A#' ,'Bb' ,'B' );

/*                 C  D  E  F  G  A  B
                   |  |  |  |  |  |  |    implements count of semitones between the note and her rigth neighbour*/
$semitones = array(2, 2, 1, 2, 2, 2, 1);//for example, A->B is 2 semitones, E->F is 1 semitones, B->C is one semitones
$notes = array('C','D', 'E', 'F', 'G', 'A', 'B');

function mod($x, $y){
	if($x < 0){
		return ($y - (abs($x) % $y)); 
	} else{
		return($x % $y);
	}
}

function linearSearch($temp, $arr){
	//return position of element in array
	foreach ($arr as $key => $item) {
		if($item == $temp){
			return $key;
		}
	}
}


function countDegree($degree, $note_position, $type){
	if($type == 'asc'){
		$find_position = mod($note_position + $degree - 1, 7);
	} else{
		if($type == 'dsc'){
			$find_position = mod($note_position - $degree + 1, 7);
		}
	}

	return $find_position;
}

function semitonesBtwNeighbours($noteA, $noteB){
	global $note_semitones;
	//function count semitones between two neighbour notes
	$positionA = linearSearch($noteA, $note_semitones); 
	$positionB = linearSearch($noteB, $note_semitones);
	
	$current_position = $positionA + 1;
	$result = 0;
	while($current_position < $positionB){
		$result = $result + 1;
		$current_position = $current_position + 1;
	}

	if ($result == 0){
		$result = 1;
	}
	return $result;
}

function linearCountSemitones(string $first_note, string $second_note){
	//direct totalizer for semitones
	global $notes;
	(int) $first_border = linearSearch($first_note, $notes);//E = 2
	(int) $second_border = linearSearch($second_note, $notes);//A = 5
	(int) $current_note = $first_border;
	(int) $result = 0;
	while($current_note < $second_border){ 
		$result = $result + semitonesBtwNeighbours($notes[$current_note], $notes[$current_note+1]);
		
		$current_note = $current_note + 1;
	}
	return $result;
}

function countSemitonesAccidentialA(string $first_note,string  $second_note,string $type){
	/*Note A may be with accidentials, but note B must be whole*/
	global $notes;
	if (strlen($first_note)>1){ // in case when note is with accidentials
		(string)$accidential = substr($first_note, 1, 1);
		(int)$accidential_count = strlen($first_note)-1;
		//echo $accidential_count;
		(string)$first_note = substr($first_note, 0, 1);
	} else {
		(string)$accidential ='';
		(int)$accidential_count = 0;
	}

	(int)$first_border = linearSearch($first_note, $notes);

	(int)$second_border = linearSearch($second_note, $notes); //one symbol anyway
		
	/*иногда,ч тобы посчитать количество полутонов, нам нужно возращаться в начало массива
	реализация этого бы подразумевала введение какого-нибудь флага например. Моя логика в том, что 
	сумма полутонов всегда одинаковая и если первый элемент интервала стоит после второго, 
	то нужно найти количество полутонов считая от второго к первому и отнять его от 12-ти */

	if($first_border >= $second_border){
		if($type == 'asc'){
			if($accidential == 'b'){
				(int) $result = 12 + ($accidential_count - linearCountSemitones($second_note, $first_note)); 
			}
			if($accidential == '#'){
				(int) $result = 12 - $accidential_count - linearCountSemitones($second_note, $first_note);
			}
			if($accidential == ''){
				$result = 12 - linearCountSemitones($second_note, $first_note);
			}
		} else{
			if($accidential == 'b'){
				$result = linearCountSemitones($second_note, $first_note) - $accidential_count; 
			}
			if($accidential == '#'){
				$result = linearCountSemitones($second_note, $first_note) + $accidential_count;
			}
			if($accidential == ''){
				$result = linearCountSemitones($second_note, $first_note);
			}
		}
	} else{
		if($type == 'asc'){
			if($accidential == 'b'){
				$result = $accidential_count + linearCountSemitones($first_note, $second_note); 
			}
			if($accidential == '#'){
				$result = linearCountSemitones($first_note, $second_note) - $accidential_count;
			}
			if($accidential == ''){
				$result =linearCountSemitones($first_note, $second_note);
			}
		} else{
			if($accidential == 'b'){
				$result = 12 - linearCountSemitones($first_note, $second_note) - $accidential_count; 
			}
			if($accidential == '#'){
				$result = 12 - linearCountSemitones($first_note, $second_note) + $accidential_count;
			}
			if($accidential == ''){
				$result = 12 - linearCountSemitones($first_note, $second_note);
			}
		}

	}
	return $result;
}


function intervalConstruction($arr) {
	//diatonic degree
	$temp = substr($arr[0], 1, 1);
	global $notes, $intervals;
	$degree = (int)$temp; 

	if(count($arr) == 2){
		$arr[2] = 'asc';
	}
	
	$whole_note = substr($arr[1], 0, 1); //first symbol in note wit accidentals
	
	$note_position = linearSearch($whole_note, $notes); //$arr[1] - first note in inteval
	//looking for second note, that forms the inteval by diatonic degree

	$second_note = $notes[countDegree($degree, $note_position, $arr[2])]; //$arr[2] - 'asc'||'dsc'

	//real semitones in interval
 	$need_semitones_count = linearSearch($arr[0], $intervals) +1;
 	if ($need_semitones_count > 5){$need_semitones_count +=1;} //because there is no 6 semitones in any interval
 	
	//count semitones between first note and expected second note
 	$real_semitones_count = countSemitonesAccidentialA($arr[1],$second_note, $arr[2]);
 
 	$result = (string)$second_note;
 	if ($need_semitones_count != $real_semitones_count){
 		$j =abs($need_semitones_count - $real_semitones_count);
 		for ($i = 1; $i <= $j; $i++){
 			if ($arr[2] == 'asc'){
 				if(($need_semitones_count < $real_semitones_count)){
 					$result = $result . "b";
 				} else{
 					$result = $result . "#";
 				}
 			} else{
 				if(($need_semitones_count > $real_semitones_count)){
 					$result = $result . "b";
 				} else{
 					$result = $result . "#";
 				}
 			}
 		}
 	}
 	return $result;
}

function countSemitonesAccidentialB($noteA, $noteB, $type)
{
	/*count semitones with accidential first note and accidential second note*/
	$whole_noteA = substr($noteA, 0, 1);
	$whole_noteB = substr($noteB, 0, 1);
	global $notes;
	$first_border = linearSearch($noteA, $notes);
	$second_border = linearSearch($noteB, $notes);
	if (strlen($noteB)>1){ // in case when note is with accidentials
		$accidential = substr($noteB, 1, 1);
		$accidential_count = strlen($noteB)-1;
	} else {
		$accidential ='';
		$accidential_count = 0;
	}
	

	$s = countSemitonesAccidentialA($noteA, $whole_noteB, $type);
	if($first_border >= $second_border){
		if($type == 'asc'){
			if($accidential == 'b'){
				$result = $s - $accidential_count; 
			}
			if($accidential == '#'){
				$result = $s + $accidential_count;
			}
			if($accidential == ''){
				$result = $s;
			}
		} else{
			if($accidential == 'b'){
				$result = $s + $accidential_count; 
			}
			if($accidential == '#'){
				$result = $s - $accidential_count;
			}
			if($accidential == ''){
				$result = $s;
			}
		}
	} else{
		if($type == 'asc'){
			if($accidential == 'b'){
				$result = $s - $accidential_count; 
			}
			if($accidential == '#'){
				$result = $s + $accidential_count;
			}
			if($accidential == ''){
				$result = $s;
			}
		} else{
			if($accidential == 'b'){
				$result = $s + $accidential_count; 
			}
			if($accidential == '#'){
				$result = $s - $accidential_count;
			}
			if($accidential == ''){
				$result = $s;
			}
		}

	}
	return $result;

}

function intervalIdentification($arr)
{
	$first_note = $arr[0];
	$second_note = $arr[1];
	if(count($arr) == 2){
		$arr[2] = 'asc';
	}

	$semitones_count = countSemitonesAccidentialB($first_note, $second_note, $arr[2]);
	global $intervals;

	if($semitones_count <6){
		$result = $intervals[$semitones_count-1];
	} 
	if($semitones_count >6){
		$result = $intervals[$semitones_count -2];
	} 
	return $result;
}


?>