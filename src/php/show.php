<?php
/**
 * (after use step1.php)
 * Show the HEAD and TAIL paths. Option to see HTML.
 */

include 'lib.php';

// CONFIGS:
	$f = '../originalContent/sample01/298679.html'; //sample file
	$useClass = true;  // attribute class of tags	
	$sliceLen_head = 52;  // the last is "body" pointer
	$sliceLen_tail = 18;  // tamanho inicial de tail

echo "\n----------------------------------------------------------------";
echo "\n--- SAMPLE file $f\t---";
echo "\n--- Show $sliceLen_head-lines-HEAD and $sliceLen_tail-lines-TAIL of the sampe \t---";
echo "\n";

$f = "$basedir/$f";
$pathList = XMLtoc($f,true,true,$useClass);
$head = getSlice($pathList,$sliceLen_head,false);
$tail = getSlice($pathList,$sliceLen_tail,true);

echo "\nHEAD:\n\t";
echo join( "\n\t", $head );
echo "\nTAIL:\n\t";
echo join( "\n\t", $tail );
echo "\n";

$headLast = array_pop($head);
$tailFirst = array_shift($tail);

$dom = h2dom($f,true);
$xpath = new DOMXPath($dom);
$domHead = $xpath->query($headLast);

echo "\n----------------------------------------------------------------";
echo "\n---\t HTML OF 'BODY' IN THE FRONT-BODY-BACK TEMPLATE \t-----\n"
	.$dom->saveXML($domHead->item(0))
;

echo "\n";
