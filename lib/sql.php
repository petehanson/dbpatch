<?php

// General utilities for manipulating SQL.
// 
// David Stygstra
// 2011-05-24
// 
// Note:
//   SQL::tokenize uses a regular expression from the CPAN module SQL::Tokenize. Use is
//   permitted under the Artistic License. See the comment near the top of SQL::tokenize
//   for more information.

class SQL {
	protected $sql;
	protected $tokens;

	// Create a new SQL statement given either a SQL string or a SQL token array. If a SQL
	// token array is provided, make sure to set $sqlIsTokenized to true.
	public function __construct($sql, $sqlIsTokenized=false) {
		if ($sqlIsTokenized) {
			$this->sql = null;
			$this->tokens = $sql;
		} else {
			$this->sql = $sql;
			$this->tokens = null;
		}
	}

	// Take a SQL string and split it into tokens. For example:
	// "insert into A values (1, 'some stuff'); select * from A;"
	// becomes
	// array (
	//   'insert',' ','into',' ','A',' ','values',' ','(','1',',',' ','\'some stuff\'',')',';',
	//   ' ','select',' ','*',' ','from',' ','A',';'
	// )
	public function toTokens() {
		if ($this->tokens !== null) {
			return $this->tokens;
		}

		// Regular expression from SQL::Tokenizer <http://dev.perl.org/licenses/artistic.html>
		// Used under the Artistic License. Minor modifications made for use with PHP.
		// Copyright (c) 2007, 2008, 2009, 2010, 2011 Igor Sutton Lopes "<IZUT@cpan.org>". All rights reserved.
		// This module [ed: the regular expression below] is free software; you can redistribute
		// it and/or modify it under the same terms as Perl itself.
		$re = '/(';
		$re .= '(?:--|\#)[\ \t\S]*'; # single line comments
		$re .= '|(?:<>|<=>|>=|<=|==|=|!=|!|<<|>>|<|>|\|\||\||&&|&|-|\+|\*(?!\/)|\/(?!\*)|\%|~|\^|\?)'; # operators and tests
		$re .= '|[\[\]\(\),;.]'; # punctuation (parenthesis, comma)
		$re .= '|\\\'\\\'(?!\\\')'; # empty single quoted string
		$re .= '|\\"\\"(?!\\"")'; # empty double quoted string
		$re .= '|".*?(?:(?:""){1,}"|(?<!["\\\\])"(?!")|\\\\"{2})'; # anything inside double quotes, ungreedy
		$re .= '|`.*?(?:(?:``){1,}`|(?<![`\\\\])`(?!`)|\\\\`{2})'; # anything inside backticks quotes, ungreedy
		$re .= '|\'.*?(?:(?:\'\'){1,}\'|(?<![\'\\\\])\'(?!\')|\\\\\'{2})'; # anything inside single quotes, ungreedy.
		$re .= '|\/\*[\ \t\r\n\S]*?\*\/'; # C style comments
		$re .= '|(?:[\w:@]+(?:\.(?:\w+|\*)?)*)'; # words, standard named placeholders, db.table.*, db.*
		$re .= '|(?: \$_\$ | \$\d+ | \${1,2} )'; # dollar expressions - eg $_$ $3 $$
		$re .= '|\\n'; # newline
		$re .= '|[\\t\ ]+'; # any kind of white spaces
		$re .= ')/smx';

		// Split into tokens and return the result
		$tokens = array();
		preg_match_all($re, $this->sql, $tokens);
		$tokens = $tokens[0];

		// Strip out empty strings and replace lengthy whitespace sequences with a single space
		foreach ($tokens as $k=>$v) {
			if ($v === '') {
				unset($tokens[$k]);
			} else {
				if (preg_match('/^\s+$/', $v)) {
					$tokens[$k] = ' ';
				}
			}
		}

		$this->tokens = array_values($tokens);
		return $this->toTokens();
	}

	// Put a list of SQL tokens back together
	public function toString() {
		if ($this->sql !== null) {
			return $this->sql;
		}
		$this->sql = implode('', $this->tokens);
		return $this->toString();
	}

	// Check if a SQL string contains a 'create database' clause
	public function createsDatabase() {
		$tokens = $this->toTokens();
		$find = array('create', ' ', 'database');
		$found = 0;
		foreach ($tokens as $tok) {
			if (strtolower($tok) === $find[$found]) {
				$found += 1;
				if ($found >= 3) {
					return true;
				}
			} else {
				$found = 0;
			}
		}
		return false;
	}
	
	// Return this SQL statement as a list of SQL strings, each containing exactly one query
	public function splitQueries() {
		$tokens = $this->toTokens();
		$queries = array();
		$query = '';
		foreach($tokens as $tok) {
			if($tok === ';' && trim($query) !== '') {
				$queries[] = $query;
				$query = '';
			} else {
				$query .= $tok;
			}
		}
		// Add the last query if it didn't end in a ';'
		if(trim($query) !== '') {
			$queries[] = $query;
		}
		return $queries;
	}
}

