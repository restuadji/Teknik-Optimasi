<?php 

class Parameters{
	const FILE_NAME = 'Datasets.txt';
	const COLUMNS = ['item', 'price'];
	const POPULATION_SIZE = 5;
	const BUDGET = 5000;
	const STOPPING_VALUE = 0;
	const CROSSOVER_RATE = 0.8;
}

class Catalogue{
	function createProductColumn($listofRawProduct){
		foreach (array_keys($listofRawProduct) as $listofRawProductKey){
			$listofRawProduct[Parameters::COLUMNS[$listofRawProductKey]] = $listofRawProduct[$listofRawProductKey];
			unset($listofRawProduct[$listofRawProductKey]);
		}
		return $listofRawProduct;
	}

	function product(){
		$collectionOfListProduct = [];
		$raw_data = file(Parameters::FILE_NAME);
		foreach ($raw_data as $listofRawProduct){
			$collectionOfListProduct[] = $this->createProductColumn(explode(",", $listofRawProduct));
		}

		return $collectionOfListProduct;
	}
}

class Individu
{
	function countNumberOfGen(){
		$catalogue = new Catalogue;
		return count($catalogue->product());
	}

	function createRandomIndividu(){
		for($i = 0; $i <= $this->countNumberOfGen()-1; $i++){
			$ret[] = rand(0,1);
		}
		return $ret;
	}
}

class Population{
	function createRandomPopulation(){
		$individu = new Individu;
		for ($i = 0; $i <= Parameters::POPULATION_SIZE-1; $i++){
			$ret[] = $individu->createRandomIndividu();
		}
		return $ret;
	}
}

class Fitness{
	function selectingItem($individu){
		$catalogue = new Catalogue;
		foreach ($individu as $individuKey => $binaryGen){
			if ($binaryGen === 1){
				$ret[] = [
					'selectedKey' => $individuKey,
					'selectedPrice' => $catalogue->product()[$individuKey]['price']
				];
			}
		}
		return $ret;
	}

	function calculateFitnessValue($individu){ //digunakan untuk menghitung total harga
		return array_sum(array_column($this->selectingItem($individu), 'selectedPrice'));
	}

	function countSelectedItem($individu){ // digunakan untuk menghitung total item
		return count($this->selectingItem($individu));
	}

	function searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem){ // fungsi ini digunakan untuk mencari Hasil Array yang terbaik
		if ($numberOfIndividuHasMaxItem === 1){
			$index = array_search($maxItem, array_column($fits, 'numberOfSelectedItem'));
			return $fits[$index];
		}else{
			foreach ($fits as $key => $val){
				if($val['numberOfSelectedItem'] === $maxItem){
					echo $key.' '.$val['fitnessValue'].'<br>';
					$ret[] = [
						'individuKey' => $key,
						'fitnessValue' => $val['fitnessValue']
					];
				}
			}
			if (count(array_unique(array_column($ret, 'fitnessValue'))) === 1){
				$index = rand(0, count($ret) - 1);
			}else{
				$max = max(array_column($ret, 'fitnessValue'));
				$index = array_search($max, array_column($ret, 'fitnessValue'));
			}
			echo 'Hasil : ';
			return $ret[$index];
		}
	}

	function isFound($fits){ 
		$countedMaxItems = array_count_values(array_column($fits, 'numberOfSelectedItem'));
		print_r($countedMaxItems);
		echo '<br>';
		$maxItem = max(array_keys($countedMaxItems)); 
		echo $maxItem;
		echo '<br>';
		echo $countedMaxItems[$maxItem]; // untuk menghitung jumlah max item
		$numberOfIndividuHasMaxItem = $countedMaxItems[$maxItem];

		$bestFitnessValue = $this->searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem)['fitnessValue'];
		echo '<br>';
		echo '<br>Best Fitness Value : '.$bestFitnessValue; // untuk menentukan harga terbaik dari seluruh array

		$residual = Parameters::BUDGET - $bestFitnessValue; // untuk menentukan residual atau sisa dari budget yang digunakan
		echo ', Residual : '.$residual;
		if ($residual <= Parameters::STOPPING_VALUE && $residual > 0){
			return TRUE;
		}
	}

	function isFit($fitnessValue){ // digunakan agat nilai fitness value tidak lebih dari budget yang diinginkan
		if ($fitnessValue <= Parameters::BUDGET){
			return TRUE;
		}
	}

	function fitnessEvaluation($population){
		$catalogue = new Catalogue; // memanggil populasi awal
		foreach ($population as $listOfIndividuKey => $listOfIndividu){
			echo 'Individu-'. $listOfIndividuKey.'<br>';
			foreach ($listOfIndividu as $individuKey => $binaryGen){
				echo $binaryGen.'&nbsp;&nbsp;';
				print_r($catalogue->product()[$individuKey]); // untuk memberikan nama item dan harga item
				echo '<br>';
			}
			$fitnessValue = $this->calculateFitnessValue($listOfIndividu); // memanggil individu yang terbaik
			$numberOfSelectedItem = $this->countSelectedItem($listOfIndividu); // 
			echo 'Max Item : '.$numberOfSelectedItem;
			echo ', Fitness Value : '.$fitnessValue;
			if ($this->isFit($fitnessValue)){ // mendeskripsikan bahwa fit atau not fitnya item tersebut berdasarkan budget dan residual
				echo ' (FIT) ';
				$fits[] = [
					'selectedIndividuKey' => $listOfIndividuKey,
					'numberOfSelectedItem' => $numberOfSelectedItem,
					'fitnessValue' => $fitnessValue
				];
				print_r($fits);
			}else{
				echo ' (NOT FIT) ';
			}
			echo '<br>';
		}
		if($this->isFound($fits)){
			echo ' Found';
		}else{
			echo ' >> Next Generation';
		}
	}
}

class Crossover{
	public $population;
	function __construct($population){
		$this->population = $population;
	}

	function randomZeroToOne(){
		return (float) rand() / (float) getrandmax();
	}

	function generateCrossover(){
		for ($i = 0; $i <= Parameters::POPULATION_SIZE-1; $i++){
			$randomZeroToOne = $this->randomZeroToOne();
			if($randomZeroToOne < Parameters::CROSSOVER_RATE){
				$parents[$i] = $randomZeroToOne;
			}
		}
		foreach(array_keys($parents) as $key){
			foreach(array_keys($parents) as $subkey){
				if ($key !== $subkey){
					$ret[] = [$key, $subkey];
				}
			}
			array_shift($parents);
		}
		echo '<br>';
		return $ret;
	}

	function offspring($parent1, $parent2, $cutPointIndex, $offspring){
		$lengthOfGen = new Individu;
		if($offspring === 1){
			for($i = 0; $i <= $lengthOfGen->countNumberOfGen()-1; $i++){
				if($i <= $cutPointIndex){
					$ret[] = $parent1[$i];
				}
				if($i > $cutPointIndex){
				$ret[] = $parent2[$i];
				}
			}
		}
		if($offspring === 2){
			for($i = 0; $i <= $lengthOfGen->countNumberOfGen()-1; $i++){
				if($i <= $cutPointIndex){
					$ret[] = $parent2[$i];
				}
				if($i > $cutPointIndex){
				$ret[] = $parent1[$i];
				}
			}
		}
		return $ret;
	}

	function cutPointRandom(){
		$lengthOfGen = new Individu;
		return rand(0, $lengthOfGen->countNumberOfGen()-1);
	}

	function crossover(){
		$cutPointIndex = $this->cutPointRandom();
		echo '<br>';
		echo $cutPointIndex;
		foreach ($this->generateCrossover() as $listOfCrossover) {
			$parent1 = $this->population[$listOfCrossover[0]];
			$parent2 = $this->population[$listOfCrossover[1]];
			echo 'Parents : <br>';
			foreach ($parent1 as $gen){
				echo $gen;
			}
			echo ' >< ';
			foreach ($parent2 as $gen){
				echo $gen;
			}
			echo '<br>';

			echo 'Offspring : <br>';
			$offspring1 = $this->offspring($parent1, $parent2, $cutPointIndex, 1);
			$offspring2 = $this->offspring($parent1, $parent2, $cutPointIndex, 2);
			foreach ($offspring1 as $gen){
				echo $gen;
			}
			echo ' >< ';
			foreach ($offspring2 as $gen){
				echo $gen;
			}
			echo '<br>';

			$offsprings[] = $offspring1;
			$offsprings[] = $offspring2;
		}
		return $offsprings;
	}
}

class Randomizer{
	static function getRandomIndexOfGen(){
		return rand(0, (new Individu())->countNumberOfGen() - 1);
	}

	static function getRandomIndexOfIndividu(){
		return rand(0, Parameters::POPULATION_SIZE - 1);
	}
}

class Mutation{
	function __construct($population){
		$this->population = $population;
	}

	function calculateMutationRate(){
		return 1 / (new Individu())->countNumberOfGen();
	}

	function calculateNumOfMutation(){
		return round($this->calculateMutationRate() * Parameters::POPULATION_SIZE);
	}

	function isMutation(){
		if($this->calculateNumOfMutation() > 0){
			return TRUE;
		}
	}

	function generateMutation($valueOfGen){
		if($valueOfGen === 0){
			return 1;
		} else{
			return 0;
		}
	}

	function mutation(){
		if($this->isMutation()){
			for ($i = 0; $i <= $this->calculateNumOfMutation()-1; $i++){
				$indexOfIndividu = Randomizer::getRandomIndexOfIndividu();
				$indexOfGen = Randomizer::getRandomIndexOfGen();
				$selectedIndividu = $this->population[$indexOfIndividu];

				echo 'Before Mutation: ';
				print_r($selectedIndividu);
				echo '<br>';

				$valueOfGen = $selectedIndividu[$indexOfGen];
				$mutatedGen = $this->generateMutation($valueOfGen);
				$selectedIndividu[$indexOfGen] = $mutatedGen;
				echo 'After Mutation: ';
				print_r($selectedIndividu);
				$ret[] = $selectedIndividu;
			}
			return $ret;
		}
	}
}

class Selection{
	function __construct($population, $combinedOffsprings){
		$this->population = $population;
		$this->combinedOffsprings = $combinedOffsprings;
	}

	function createTemporaryPopulation(){
		//echo 'Base population : '. count($this->population).'&nbsp;';
		foreach($this->combinedOffsprings as $offspring){
			$this->population[] = $offspring;
		}
		//echo ' offspring : '. count($this->combinedOffsprings).' Temporary : '.count($this->population);
		return $this->population;
	}

	function getVariableValue($basePopulation, $fitTemporaryPopulation){
		foreach($fitTemporaryPopulation as $val){
			$ret[] = $basePopulation[$val[1]];
		}
		return $ret;
	}

	function sortFitTemporaryPopulation(){
		$tempPopulation = $this->createTemporaryPopulation();
		$fitness = new Fitness;
		foreach ($tempPopulation as $key => $individu){
			$fitnessValue = $fitness->calculateFitnessValue($individu);
			if ($fitness->isFit($fitnessValue)){
				//echo $fitnessValue.' '. $key.'<br>';
				$fitTemporaryPopulation[] = [
					$fitnessValue,
					$key
				];
			}
		}
		rsort($fitTemporaryPopulation);
		$fitTemporaryPopulation = array_slice($fitTemporaryPopulation, 0, Parameters::POPULATION_SIZE);
		return $this->getVariableValue($tempPopulation, $fitTemporaryPopulation);
		// echo '<p></p> '.print_r($fitTemporaryPopulation);
		// foreach($fitTemporaryPopulation as $val){
		// 	print_r($val).'<br>';
		// }
	}

	function selectingIndividus(){
		$selected = $this->sortFitTemporaryPopulation();
		echo '<p></p>';
		print_r($selected);
		// echo '<p></p> Temporary Population <br>';
		// print_r($this->createTemporaryPopulation());
	}
}



$initialPopulation = new Population;
$population = $initialPopulation->createRandomPopulation();

$fitness = new Fitness;
$fitness->fitnessEvaluation($population);
print_r($population);

$crossover = new Crossover($population);
$crossoverOffsprings = $crossover->crossover();

echo 'Crossover Offsprings:<br>';
print_r($crossoverOffsprings);

echo '<p></p>';
// (new Mutation($population))->mutation();
$mutation = new mutation($population);
if($mutation->mutation()){
	$mutationOffsprings = $mutation->mutation();
	echo 'Mutation offsprings<br>';
	print_r($mutationOffsprings);
	echo '<p></p>';
	foreach($mutationOffsprings as $mutationOffsprings){
		$crossoverOffsprings[] = $mutationOffsprings;
	}
}
echo 'Mutation offsprings <br>';
print_r($crossoverOffsprings);
$fitness->fitnessEvaluation($crossoverOffsprings);

$selection = new Selection($population, $crossoverOffsprings);
$selection->selectingIndividus();
// $individu = new Individu;
// print_r($individu->createRandomIndividu());


 ?>