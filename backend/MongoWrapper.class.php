<?php

namespace MongoWrapper;

class MongoWrapper
{

	public $connection;
	public $collection;

	public function __construct($host = 'localhost:27017')
	{
		if(getenv("APP_STAGE") == "production"){
			$connection_url = getenv("MONGOHQ_URL");
 
		    // create the mongo connection object
		    $this->connection = new \Mongo($connection_url);
		 
		    // extract the DB name from the connection path
		    $url = parse_url($connection_url);
		    $db_name = preg_replace('/\/(.*)/', '$1', $url['path']);
		 	if($db_name){
		 		// use the database we connected to
		    	$this->db = $this->connection->selectDB($db_name);	
		 	}
		    
		    return;
		}
		$this->connection = new \Mongo();
	}

	public function setDatabase($c)
	{
		$this->db = $this->connection->selectDB($c);
	}

	public function setCollection($c)
	{
		$this->collection = $this->db->selectCollection($c);
	}

	public function insert($f)
	{
		$this->collection->insert($f);
	}

	public function get($f,$limit=null)
	{
		$cursor = $this->collection->find($f);

		if($limit)
			$cursor->limit($limit);

		$k = array();
		$i = 0;
		while( $cursor->hasNext())
		{
		    $k[$i] = $cursor->getNext();
		    $k[$i]["id"] = $k[$i]["_id"]->__toString();
			$i++;
		}

		return $k;
	}

	public function update($f1, $f2)
	{
		$this->collection->update($f1, $f2);
	}

	public function getAll()
	{
		$cursor = $this->collection->find();
		foreach ($cursor as $id => $value)
		{
			echo "$id: ";
			var_dump( $value );
		}
	}

	public function delete($f, $one = FALSE)
	{
		$c = $this->collection->remove($f, $one);
		return $c;
	}

	public function ensureIndex($args)
	{
		return $this->collection->ensureIndex($args);
	}

}