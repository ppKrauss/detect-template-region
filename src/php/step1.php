<?php
/**
 * Extracts from XML-path lines of a sample of XML (or HTML) files,
 * the most probable initial and final "boilerplate region" (produced by a template-system).
 * PS: supposing FRONT-BODY-BACK template.
 */

include 'lib.php';

// CONFIGS:
	$originais = '../originalContent/sample01'; 	// original files in UTF-8 XML or HTML
	$randSampling = true;  // true for random sampling or false  for sequential
	$sliceLen_max = 60;  // tamanho máximo do head ou tail
	$minSample = false;      // enforce minimal sampling for 2*$sliceLen_max
	$sliceLen_head = 2;  // tamanho inicial de head
	$sliceLen_tail = 2;  // tamanho inicial de tail
	$NCMP = 25;  // numero de amostras, define itens da matriz diagonal de comparação (permuta).
	$MAXPERC = 30; // percentual máximo de amostras diferentes para continuar buscando

echo "\n----------------------------------------------------------------";
echo "\n--- SAMPLES FROM $originais \t\t---";
echo "\n--- $NCMP samples, head-and-tail with $sliceLen_max elements \t\t---";
echo "\n---";

$allFNames = [];
$allHeads  = [];
$allTails  = [];
$dir = "$basedir/$originais";

foreach (scandir($dir) as $f) if (substr($f,-5,5)=='.html')
	$allFNames[] = $f;
$n_files = count($allFNames);

if ($n_files<=$NCMP) // NO sampling:
	$fname_byRnd=$allFNames;
elseif ($randSampling) { // RANDOM sampling:
	$fname_byRnd=[];
	for($rnd=0, $i=0;  $i<$NCMP;  $rnd=mt_rand(0,$n_files-1), $i++)
		if (!isset($fname_byRnd[$rnd]))
			$fname_byRnd[$rnd] = $allFNames[$rnd];
	$fname_byRnd = array_values($fname_byRnd);
} else  								// SEQUENTIAL (DETERMINISTIC) sampling:
	$fname_byRnd= array_slice($allFNames, 0, $NCMP-1);

// head-tail build:
$n=0;
$sliceLen_max2 = $sliceLen_max*2;
$allFNames = [];

foreach ($fname_byRnd as $idx=>$f) {
	$toc = XMLtoc("$dir/$f",true,true);
	$toc_lines = count($toc);
	if (!$minSample || $sliceLen_max2<=$toc_lines) {
		echo "\n-----\tprocessing sample s$n - $f ($toc_lines lines)\t-----";
		$allFNames[] = $f;
		$cut = ($toc_lines<$sliceLen_max)? $toc_lines: $sliceLen_max;
		$allHeads[] = array_slice($toc, 0, $cut);  // do inicio ao limite head
		$allTails[] = array_slice($toc, -$cut); // daí até o fim
		$n++;
	} // if
} // for

echo "\n RESULTS:";

for( ; $sliceLen_head<$sliceLen_max;  $sliceLen_head++ )
	if ( checkSlices($allHeads,$sliceLen_head,false,"HEADS",$MAXPERC) ) break;

echo "\n";

for( ; $sliceLen_tail<$sliceLen_max;  $sliceLen_tail++ )
	if ( checkSlices($allTails,$sliceLen_tail,true,"TAILS",$MAXPERC) ) break;

echo "\n";
