<?php
use PHPUnit\Framework\TestCase;
use FNVi\Mongo\Collection;
use FNVi\Mongo\Database;
use MongoDB\Model\BSONDocument;

/**
 * Description of CollectionTest
 *
 * @author Joe Wheatley <joew@fnvi.co.uk>
 */
class CollectionTest extends TestCase{
    
    /**
     * An FNVi collection class
     * @var Collection
     */
    protected $collection;
    
    protected $collectionName = "testcollections";
    protected $className = "TestCollection";
        
    public static function tearDownAfterClass() {
        Database::dropDatabase();
    }
    
    protected function setUp(){
        $this->collection = $this->getMockBuilder(Collection::class)->setMockClassName($this->className)->getMockForAbstractClass();
    }
        
    protected function tearDown() {
        $this->collection->deleteMany([]);
    }
    
    public function testConstructor(){
        $this->assertEquals($this->collectionName, $this->collection->getCollectionName(), "Check collection name is set correctly");
        $this->assertEquals($this->className, get_class($this->collection), "Check collection class name is correctly");
    }
    
    public function testAggregate(){
        $actual = $this->collection->aggregationPipeline();
        $this->assertEquals(\FNVi\Mongo\Tools\AggregationPipeline::class, get_class($actual), "Check aggregation pipeline object returned");
    }
    
    public function testUpdate(){
        $actual = $this->collection->update();
        $this->assertEquals(\FNVi\Mongo\Tools\Update::class, get_class($actual), "Check update object returned");
    }
    
    public function testQuery(){
        $actual = $this->collection->query();
        $this->assertEquals(\FNVi\Mongo\Tools\Query::class, get_class($actual), "Check query object returned");
    }
    
    public function testCRUDOne(){
        $document = new BSONDocument(["test"=>"insert one"]);
        
        $insertResult = $this->collection->insertOne($document);
        $this->assertEquals(1, $insertResult->getInsertedCount(), "insert one");
        
        $countResult = $this->collection->count();
        $this->assertEquals(1, $countResult, "Count result after inserting document");
        
        $query = ["_id"=>$insertResult->getInsertedId()];
        
        $document->offsetSet("_id", $insertResult->getInsertedId());
        
        $findResult = $this->collection->findOne($query);
        $this->assertEquals($document, $findResult, "find one");
        
        $update = ["test"=>"update one"];
        $document->offsetSet("test", "update one");
        
        $updateResult = $this->collection->updateOne($query, ['$set'=>$update]);
        $count = $updateResult->getModifiedCount();
        $matched = $updateResult->getMatchedCount();
        $this->assertEquals(1, $count, "update one result ".  json_encode(["count"=>$count, "matched"=>$matched],128));
        
        $findUpdatedResult = $this->collection->findOne($query);
        $this->assertEquals($document, $findUpdatedResult, "find updated one");
        
        
        $deleteResult = $this->collection->deleteOne($query);
        $this->assertEquals(1, $deleteResult->getDeletedCount(), "remove one");
        
        $findRemovedResult = $this->collection->findOne($query);
        $this->assertNull($findRemovedResult, "find removed one");
        
        $countRecoveredResult = $this->collection->count();
        $this->assertEquals(0, $countRecoveredResult, "Count result after recovering document");
        
    }
    
    public function testCRUDMany(){
        $documents = array_fill(0, 5, new BSONDocument(["test"=>"insert many"]));
        
        $insertResult = $this->collection->insertMany($documents);
        $this->assertEquals(5, $insertResult->getInsertedCount(), "insert many");
        $countResult = $this->collection->count();
        $this->assertEquals(5, $countResult, "Count result after inserting documents");
        
        foreach($insertResult->getInsertedIds() as $i=>$id)
        {
            $documents[$i] = new BSONDocument(["_id"=>$id,"test"=>"insert many"]);
        }
        
        $findResult = $this->collection->find();
                
        $this->assertEquals($documents, iterator_to_array($findResult), "find all");
        
        $update = ["test"=>"update many"];
        
        $updateResult = $this->collection->updateMany([], ['$set'=>$update]);
        $count = $updateResult->getModifiedCount();
        $matched = $updateResult->getMatchedCount();
        $this->assertEquals(5, $count, "update many result ".  json_encode(["count"=>$count, "matched"=>$matched],128));
        
        foreach ($documents as $d)
        {
            $d->offsetSet("test", "update many");
        }
        $findUpdatedResult = $this->collection->find();
        $this->assertEquals($documents, iterator_to_array($findUpdatedResult), "find updated many");
        
        
        $deleteResult = $this->collection->deleteMany([]);
        $this->assertEquals(5, $deleteResult->getDeletedCount(), "remove many");
        
        $findRemovedResult = $this->collection->findOne();
        $this->assertNull($findRemovedResult, "find removed one");
               
        $countRecoveredResult = $this->collection->count();
        $this->assertEquals(0, $countRecoveredResult, "Count result after recovering documents");
        
    }
}
