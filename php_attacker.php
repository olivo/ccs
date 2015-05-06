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

	// Construct the CFGs for all the functions defined in 
	// the file.

	echo "Constructing the CFG map of functions.\n";
	$function_definitions = CFG::process_function_definitions($stmts);
	$function_cfgs = $function_definitions[0];
	$function_signatures = $function_definitions[1];


	echo "Finished construction of the CFG map of functions.\n";
	echo "Found ".(count($function_signatures))." inner functions.\n";
	echo "The function names are:\n";
	foreach($function_signatures as $name => $signature)
				     print $name."\n";

	// Construct the CFG of the main procedure of the file.
	echo "Constructing the main CFG.\n";
	$main_cfg = CFG::construct_cfg($stmts);
	echo "Finished construction of the main CFG.\n";

	echo "The main CFG is:\n";
	$main_cfg->print_cfg();

	echo "The CFGs of the inner functions are:\n";
	foreach($function_cfgs as $name => $inner_cfg) {
		print "The CFG of ".$name." is :\n";
		$inner_cfg->print_cfg();
		}

	fclose($myfile);
?>