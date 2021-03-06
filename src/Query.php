<?php

namespace FNVi\Mongo;

use \Iterator;
/**
 * The Query class is created to provide backwards compatibility with the old Mongo driver in the FNVi application.
 * It will be potentially phased out in time. Only use if absolutely necessary!
 *
 * @author Joe Wheatley <joew@fnvi.co.uk>
 */
class Query extends Collection implements Iterator{
    
    /**
     *
     * @var \MongoDB\Driver\Cursor 
     */
    private $cursor;
    protected $query = [];
    private $options = [];
    
    public function __construct($collection = "", $query = []) {
        $this->query = $query;
        parent::__construct($collection);
        
    }
    
    public function limit($number){
        $this->options += ["limit"=>$number];
    }
    
    public function skip($number){
        $this->options += ["skip"=>$number];
    }
    
    public function sort($options){
        $this->options += $options;
    }
    
    public function subset($query = []){
        return new Query($this->collectionName, $query += $this->query);
    }
    
    public function current() {
        return $this->cursor->current();
    }

    public function key() {
        $this->cursor->key();
    }

    public function next() {
        return $this->cursor->next();
    }

    public function rewind() {
        $this->cursor = new \IteratorIterator($this->find($this->query,$this->options));
        return $this->cursor->rewind();
    }

    public function valid() {
        return $this->cursor->valid();
    }

    public function getQuery(){
        return $this->query;
    }
}
