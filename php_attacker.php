<?php

	require "PHP-Parser-master/lib/bootstrap.php";

	include "security_constraints.php";

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

//	echo $code."\n";

/*
	foreach($stmts as $stmt) {
		       echo "A statement ".$stmt->getType()."\n";
		       }
*/

	echo "There are ".count($stmts)." statements.\n"; 	

//	$traverser = new PhpParser\NodeTraverser;

//	$traverser->addVisitor(new ASTVisitor);

//	$traverser->traverse($stmts);

	$constraints = generate_security_constraints($stmts);

	fclose($myfile);
?>