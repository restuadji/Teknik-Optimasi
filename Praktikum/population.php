<?php 

class Catalogue{
	function createProductColumn($columns, $listofRawProduct){
		foreach (array_keys($listofRawProduct) as $listofRawProductKey){
			$listofRawProduct[$columns[$listofRawProductKey]] = $listofRawProduct[$listofRawProductKey];
			unset($listofRawProduct[$listofRawProductKey]);
		}
		return $listofRawProduct;
	}

	function product($paramaters){
		$collectionOfListProduct = [];

		$raw_data = file($paramaters['file_name']);
		foreach ($raw_data as $listofRawProduct){
			$collectionOfListProduct[] = $this->createProductColumn($paramaters['columns'], explode(",", $listofRawProduct));
		} //Membaca Seluruh List Data dan Memanggil Data 

		// foreach ($collectionOfListProduct as $listofRawProduct){
		// 	print_r($listofRawProduct);
		// 	echo '<br>';
		// }
		return [
			'product' => $collectionOfListProduct,
			'gen_length' => count($collectionOfListProduct)
		];
	}
}

class PopulationGenerator{
	function createIndividu($paramaters){
		$catalogue = new Catalogue;
		$lengthOfGen = $catalogue->product($paramaters)['gen_length'];
		for ($i = 0; $i <= $lengthOfGen-1; $i++){
			$ret[] = rand(0,1);
		}
		return $ret;
	}

	function createPopulation($paramaters){
		for ($i = 0; $i <= $paramaters['population_size']; $i++){
			$ret[] = $this->createIndividu($paramaters);
		}
		foreach ($ret as $key => $val){
			print_r($val);
			echo '<br>';
		}
	}
}

$paramaters = [
	'file_name' => 'Products.txt',
	'columns' => ['item', 'price'],
	'population_size' => 10
];

$katalog = new Catalogue;
$katalog->product($paramaters);

$initialPopulation = new PopulationGenerator;
$initialPopulation->createPopulation($paramaters);
 ?>