<?php

	require "PHP-Parser-master/lib/bootstrap.php";

	include "security_constraints.php";

	include "CFG.php";

	$myfile = fopen($argv[1],"r");

	$parser = new PhpParser\Parser(new PhpParser\Lexer);

	$prettyPrinter = new PhpParser\PrettyPrinter\Standard;

	$contents = fread($myfile,filesize($argv[1]));

	$stmts=array();

	try {
		$stmts = $parser->parse($contents);	
	} catch(PhpParser\Error $e) {
	  	echo 'Parse Error: ',$e->getMessage();
	}
	

//	$code = $prettyPrinter->prettyPrint($stmts);


	echo "There are ".count($stmts)." statements.\n"; 	


	$cfg = CFG::construct_cfg($stmts);

	$cfg->print_cfg();

	fclose($myfile);
?>