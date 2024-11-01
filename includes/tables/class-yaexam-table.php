<?php

namespace YaExam\Tables;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class YAEXAM_Table
{
	
	protected	$_prefix = 'yaexam_';
		
	public		$_data		=	array();
	
	protected	$_formats	=	array();
		
	public function __construct( $data = array(), $key = 'id' )
	{
		global $wpdb;
		
		$this->_key		=	$key;
		$this->_table	=	$wpdb->prefix . $this->_prefix . $this->_table;
		
	}
	
	public function save($src, $orderingFilter = '', $ignore = '')
	{
		
		// Attempt to bind the source to the instance.
		if (!$this->bind($src, $ignore))
		{
			return false;
		}

		// Run any sanity checks on the instance and verify that it is ready for storage.
		if (!$this->check())
		{
			return false;
		}

		// Attempt to store the properties to the database table.
		if (!$this->store())
		{
			return false;
		}

		// Attempt to check the row in, just in case it was checked out.
		if (!$this->checkin())
		{
			return false;
		}

		return $this->_data[$this->_key];
	}
	
	public function bind($src, $ignore = array())
	{
		// If the source value is not an array or object return false.
		if (!is_object($src) && !is_array($src))
		{
			throw new InvalidArgumentException(sprintf('%s::bind(*%s*)', get_class($this), gettype($src)));
		}

		// If the source value is an object, get its accessible properties.
		if (is_object($src))
		{
			$src = get_object_vars($src);
		}

		// If the ignore value is a string, explode it over spaces.
		if (!is_array($ignore))
		{
			$ignore = explode(' ', $ignore);
		}
		
		// Bind the source value, excluding the ignored fields.
		foreach ($this->fields as $k => $v)
		{
			// Only process fields not in the ignore array.
			if (!in_array($k, $ignore))
			{
				
				if (isset($src[$k]))
				{
					$this->_data[$k] 	=	$src[$k];
					$this->_formats[] 	=	'%'.$v;
				}
				
			}
		}
		
		return true;
	}
	
	protected function _has_primary_key()
	{
		
		if(isset($this->_data[$this->_key]))
			return true;
		
		return false;
	}
	
	public function load( $id = 0 )
	{
		global $wpdb;
		
		$fields			=	implode(', ', array_keys($this->fields));
		
		$this->_data	=	$wpdb->get_row($wpdb->prepare('SELECT '. $fields .' FROM ' . $this->_table . ' WHERE ' . $this->_key . ' = %d', $id), ARRAY_A);
		
		return $this->_data;
	}
	
	public function store()
	{
		
		global $wpdb;
		
		if( $this->_has_primary_key() ){
			
			$wpdb->update( 
				$this->_table, 
				$this->_data, 
				array( $this->_key => $this->_data[$this->_key] ),
				$this->_formats,
				array( '%d' )
			 );
			 
			 return true;
			
		}else{
			
			$wpdb->insert( $this->_table, $this->_data, $this->_formats );
			
			if( $wpdb->insert_id )
				$this->_data[$this->_key]	=	$wpdb->insert_id;
				 
			return true;
		}
		
		$wpdb->flush();
		
		return false;
	}

	public function updateOrNew( $fields = array() ) {
		
		global $wpdb;
		
		if( !$fields ) {
			
			$fields = array( $this->_key => $this->_data[$this->_key]);
		}
		
		$where = implode(' AND ', $fields );
		
		$key_value = $wpdb->get_var('SELECT '. $this->_key .' FROM ' . $this->_table . ' WHERE id = ' . $where);
		
		if( $key_value ) {
			
			$this->store();
			
		}else{
			
			$wpdb->insert( $this->_table, $this->_data, $this->_formats );
			
			if( $wpdb->insert_id )
				$this->_data[$this->_key]	=	$wpdb->insert_id;
		}
	}
	
	public function getId()
	{
		return $this->_data[$this->_key];
	}
	
	public function remove( $key = 0 )
	{
		global $wpdb;
		
		if(isset($this->_data[$this->_key]))
			$_key	=	$this->_data[$this->_key];
		
		if($key)
			$_key	=	$key;
				
		if( $_key ){
			
			return $wpdb->delete(
				 $this->_table, 
				 array( $this->_key => $_key ), 
				 array( '%d' ) 
			 );
		}
		
		return false;
	}
	
	public function check()
	{
		return true;
	}
	
	public function checkin()
	{
		return true;
	}
	
	
}