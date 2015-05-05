<?php

class CFGNode {

// The statement contained in the node.
public $stmt = NULL;

// The set of pointers to successor CFG nodes.
public $successors = array();
	
public function __construct() {
	      	     
	$this->stmt = NULL;
	$this->successors = array();
 }
	
 }



?>