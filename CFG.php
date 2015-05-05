<?php

require "PHP-Parser-master/lib/bootstrap.php";
require "CFGNode.php";
require "StmtProcessing.php";

// Class representing an entire CFG.
// It contains an entry CFG node, and an exit CFG node.
// The CFG can be traversed by going through the successors of 
// the CFG nodes until the exit node.

class CFG {

      // Entry node.
      public $entry = NULL;

      // Exit node.
      public $exit = NULL;

      function __construct() {

      	       $this->entry = new CFGNode();
	       $this->exit = new CFGNode();

      }

	
	// Construct the Control Flow Graph (CFG) from a 
	// sequence of statements.

	static function construct_cfg($stmts) {

	       $prettyPrinter = new PhpParser\PrettyPrinter\Standard;

	       // Creating an empty entry node.	
	       $cfg = new CFG();

	       $entry = new CFGNode();

	       $cfg->entry = $entry;
	       
	       $current_node = $entry;

	       foreach($stmts as $stmt)  {

	         print "Processing statement:\n";
	       	 printStmts(array($stmt));

		 // Assignment statement.
	       	 if($stmt instanceof PhpParser\Node\Expr\Assign) {
		 	  print "Found assignment statement\n";
			  $assign_node = CFG::processExprAssign($stmt);
			  $current_node->successors[] = $assign_node;
			  $current_node = $assign_node;
			  print "Constructed assignment node\n";



		  }
		  // If statement.
		  else if($stmt instanceof PhpParser\Node\Stmt\If_) {
		          print "Found conditional statement\n";
			  $if_nodes = CFG::processStmtIf($stmt);

			  // Connect the current node with the 
			  // conditional node of the if.
			  $current_node->successors[]=$if_nodes[0];

			  // Make the current node, the node that 
			  // joins the branches of the if.
			  $current_node = $if_nodes[1];
			  
		       	  print "Constructed conditional node\n";

		  // Method call statement.
		  } else if($stmt instanceof PhpParser\Node\Expr\MethodCall) {

		 	  print "Found method call statement\n";
			  $method_call_node = CFG::processExprMethodCall($stmt);
			  $current_node->successors[] = $method_call_node;
			  $current_node = $method_call_node;
			  print "Constructed method call node\n";


		  

   }	       		      

		  else {	       		      
		       	  print "WARNING: Couldn't construct CFG node.\n";
		  	  print "The statement has type ".($stmt->getType())."\n";

	          	  print "Has keys\n";

		  	  foreach($stmt as $key => $value) {
			  		print "Key=".($key)."\n";
		   	   }

		  }




	        }
	 
	// Create a dummy exit node, and make a pointer
	// from the last processed node to the exit node.
	$cfg->exit = new CFGNode();
	$current_node->successors[] = $cfg->exit;

	return $cfg;
					
}	

// Constructs a node for an assignment expression.
static function processExprAssign($exprAssign) {

	// exprAssign has keys 'var' and 'expr'.

	$cfg_node = new CFGNode();
	$cfg_node->stmt = $exprAssign;

	return $cfg_node;
}

// Constructs a node for a method call expression.
static function processExprMethodCall($exprMethodCall) {

	// exprMethodCall has keys 'var', 'name' and 'args'.

	$cfg_node = new CFGNode();
	$cfg_node->stmt = $exprMethodCall;

	return $cfg_node;
}

// Constructs a node for an if statement.
// It creates a CFG node with the condition, two CFGs for the 
// true and false branches (if they exist), and a dummy CFG node
// that represents the "exit" of the conditional.
// It links the conditional to the CFGs, and the exit of the CFGs
// to the dummy exit node.
// It returns the condition node and dummy exit nodes.

static function processStmtIf($stmtIf) {

	// stmtIf has keys 'cond', 'stmts', 'elseifs', and 'else'.


	// Get the If condition.
	$cond = $stmtIf->cond;

	// Create a CFG node with the condition.
	$cond_node = new CFGNode();
	$cond_node->stmt = $cond;

	// Create a dummy exit node from which the branch CFGs point to.
	$dummy_exit = new CFGNode();

	
	// Get the statements for the first branch.
	$if_stmts = $stmtIf->stmts;

	// Construct the CFG for the statements in the true branch.
	$true_cfg = CFG::construct_cfg($if_stmts);

	// Create a pointer from the condition node to the entry of 
	// the true CFG.

	$cond_node->successors[] = $true_cfg->entry;

	
	// Create a pointer from the exit node of the true CFG
	// to the exit dummy node.
	$true_cfg->exit->successors[] = $dummy_exit;

	if($stmtIf->elseifs) {
			     print "WARNING: Not soundly processing conditionals with else if clauses!\n";
	}


	if($stmtIf->else) {
		
		// If there are statements in an else branch, create
		// the false CFG from them, create a link from the 
		// condition node to the entry of the false CFG, and
		// create a link from the exit of the false CFG to
		// the dummy exit node.

		$else_branch = $stmtIf->else;
		$false_cfg = CFG::construct_cfg($else_branch->stmts);

		// Create a pointer from the false CFG exit to the 
		// dummy node.
		
		$false_cfg->exit->successors[] = $dummy_exit;

		// Create a pointer from the condition node to the 
		// entry of the false branch.
		$cond_node->successors[] = $false_cfg->entry;		

		
	 } else {
	   // The else clause doesn't exist, so add a pointer directly
	   // from the condition to the dummy exit node.
	   $cond_node->succesors[] = $dummy_exit;
	 }

	 
	 
	 // Return the conditional node and the dummy node.
	 return array($cond_node,$dummy_exit);
}

// Constructs a node for an include expression.
// WARNING: Not implemented;
static function processExprInclude($exprInclude) {

	// exprInclude has keys 'expr' and 'type'.
	print("WARNING:Expr Include not handled properly.\n");
	$cfg_node = new CFGNode();

	return $cfg_node;
}


// Prints a CFG starting from the root node.
// WARNING: Only printing the true branches of the conditionals.
function print_cfg() {
	 
	 print "Starting to print CFG\n";
	 print "WARNING: Only printing the true branches of the conditionals\n";

	 // Skip the first node, because it's a dummy entry node.
	 $current_node=$this->entry;
	 $successor_list = $current_node->successors;
	 $current_node=$successor_list[0];
	 

	 do {

	 	if($current_node->stmt!=NULL) {
			print "Statement in node.\n";
			printStmts(array($current_node->stmt));
			print "It has ".(count($current_node->successors)) ." successors.\n";
		 }			      
		 else {
		       print "Skipping null node\n";
		 }
		 
		$successor_list = $current_node->successors;
		$current_node=$successor_list[0];
		

	 } while(count($current_node->successors));
	

}

}

?>