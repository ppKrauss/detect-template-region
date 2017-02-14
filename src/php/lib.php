<?php
ini_set("default_charset", 'utf-8');
$basedir = dirname(__DIR__);  //  clone root

	/**
	 * XML's Table of Contents (a getNodePath() list)
	 * @param $dom the DOMDocument object.
	 * @param $asarray boolean indicating to return an array.
	 * @param $sep string item separator.
	 * @param $reduceWhen string or array, when used is a list of non-root "stop-tags", that reduce the XPath.
	 */
	function XMLtoc($dom,$isfile=true,$asarray=false, $sep="\n", $rmIdx=false, $reduceWhen=NULL) {
		if (!is_object($dom))
			$dom = h2dom($dom,$isfile);
		$toc=array();
		$wasPath = array();
		if ($reduceWhen){
			if (is_array($reduceWhen))
				$reduceWhen = join('|',$reduceWhen);
			$reduceWhen = "#/(?:$reduceWhen)(?:[\[/].+|)\$#s"; // regex
			$c=0;
		}
		$nels =0;
		foreach ($dom->getElementsByTagName('*') as $node) {
			// debug ok print "{$node->nodeType}.{$node->nodeName}, ";
			$path = $node->getNodePath();
			if ($rmIdx)
				$path = preg_replace('/\[\d+\]/s', '', $path);
			if ($reduceWhen) {
				$path = preg_replace($reduceWhen, '', $path, -1, $c);
				if (!$path)
					die("\nERROR in this DTD with XPaths in the (regex) form $reduceWhen\n");
				if (!$c || !isset($wasPath[$path]))
					$toc[] = $path;
				$wasPath[$path] = 1;
			} else
				$toc[] = $path;
			$nels++;
		}
		return $asarray? $toc: join($sep,$toc);
	}


	/**
	 * Converts HTML to DOMDocument.
	 */
	function h2dom($xml,$isFile=true, $debug=false) {
		$META = '<meta charset="UTF-8"/>';
		if ($isFile) {
				$xml = file_get_contents($xml);
				$xml = preg_replace('|<nobr\s*/?\s*>|s', "", $xml); // commom Microsoft bug
				$xml = preg_replace('/^\s*<html>/s', "<html>$META", $xml);
		}
		$dom = new DOMDocument('1.0', 'UTF-8'); // check libXML2 v2.9.4: May 23 2016
		$dom->formatOutput = true;
		$dom->preserveWhiteSpace = false;
		$dom->resolveExternals = false; // external entities from a (HTML) doctype declaration
		$dom->recover = true; // Libxml2 proprietary behaviour. Enables recovery mode, i.e. trying to parse non-well formed documents
		if ($debug)
			$ok = $dom->loadHTML($xml, LIBXML_NOENT | LIBXML_NOCDATA | LIBXML_COMPACT);
		else {
			libxml_use_internal_errors(true); // or use @ to silent
			$ok = $dom->loadHTML($xml, LIBXML_NOENT | LIBXML_NOCDATA | LIBXML_COMPACT);
			libxml_clear_errors();
		}
		if ($ok) {
			$dom->normalizeDocument();
			return $dom;
		} else
			return NULL;
	}

	function getSlice($toc,$cut,$inverse=false) {
		return  $inverse
				? array_slice($toc, -$cut)
				: array_slice($toc, 0, $cut)
		;
	}

	/**
	 * Check (by reduceToDiffs function) if there are some repeated sample.
	 * @return boolean true when repeat (more than $maxPerc percentual, ex.30).
	 */
	function checkSlices(&$allItems,$sliceLen,$inverse=false,$MSG="??",$maxPerc=30,$print=true) {
		$check = [];
		foreach($allItems as $idx=>$toc) {
			$len = count($toc);
			$cut = $len<$sliceLen? $len: $sliceLen;
			$check[] = $inverse
				? array_slice($toc, -$cut)
				: array_slice($toc, 0, $cut)
			;
		}
		$check_n0=count($check);
		reduceToDiffs($check);
		$check_n = count($check)-1;
		if ($check_n>0) {
			$perc = round(1000*$check_n/$check_n0)/10;
			if ($print) print("\n\t!$MSG $check_n diffs (in $check_n0=$perc%) when len=$sliceLen");
			return ($perc>$maxPerc)? true: false;
		} elseif ($check_n<0)
			die("\nBUG232, plase check\n");
		else
			return false;
	}

	/**
	 * Use SHA1 as content representation and unique key.
	 * Group same content into same sha1.
	 * Changes input array $a when is possible to group someting.
	 */
	function reduceToDiffs(&$a) {
		$a_n = count($a);
		$test=[];
		for($i=0; $i<$a_n; $i++) {
			$ss = join("\n",$a[$i]);
			$sha1 = sha1($ss);
			if (!isset($test[$sha1]))
				$test[$sha1]=$i;
		}
		if (count($test)<$a_n) {
			$tmp = [];
			foreach(array_values($test) as $i)
				$tmp[] = $a[$i];
			$a = $tmp;
		}
	}
