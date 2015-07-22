<?php

namespace Tests\Data {
    
    /**
     * Test the currently configured DataConcierge.
     */
    class DataConciergeTest extends \Tests\KnownTestCase {
        
        public static $object;
        public static $uuid;
        public static $id;
        public static $url;
        
        public static function setUpBeforeClass()
        {
            $obj = new \Idno\Entities\GenericDataItem();
            $obj->setDatatype('UnitTestObject');
            $obj->setTitle("Unit Test Search Object");
            $obj->variable1 = 'test';
            $obj->variable2 = 'test again';
            $id = $obj->save();
            
            // Save for later retrieval
            self::$id = $id;
            self::$uuid = $obj->getUUID();
            self::$url = $obj->getUrl();
            self::$object = $obj;
        }
        
        /**
         * Versions test (if applicable)
         */
        public function testVersions() {
            if (is_callable([\Idno\Core\site()->db(), 'getVersions'])) {
                $versions = \Idno\Core\site()->db()->getVersions();
                
                $this->assertTrue(is_array($versions));
            }
        }
        
        /**
         * Create an object.
         */
        public function testCreateObject() {
            // Verify
            $this->assertFalse(empty(self::$id));
            $this->assertTrue(is_string(self::$uuid));
            $this->assertTrue(is_string(self::$url));
            
            $this->validateObject(self::$object);
        }
        
        /**
         * Attempt to retrieve record by UUID.
         */
        public function testGetRecordByUUID() {
            $this->validateObject(
                    \Idno\Core\site()->db()->rowToEntity(
                            \Idno\Core\site()->db()->getRecordByUUID(self::$uuid)
                    )
            );
        }
        
        /**
         * Attempt to retrieve record by ID.
         */
        public function testGetRecord() {
            $this->validateObject(
                    \Idno\Core\site()->db()->rowToEntity(
                            \Idno\Core\site()->db()->getRecord(self::$id)
                    )
            );
        }
        
        /**
         * Attempt to get any object
         */
        public function testGetAnyRecord() {
            $obj = \Idno\Core\site()->db()->getAnyRecord();
           
            $this->assertFalse(empty($obj));
            if (is_array($obj))
            {
                print "WARNING: getAnyRecord for this DataConcierge returned an Array. This is inconsistent, but we're converting.";
                $obj = \Idno\Core\site()->db()->rowToEntity($obj);
            }
            
            $this->assertTrue(is_object($obj));
        }
        
        /**
         * Test getByID
         */
        public function testGetById() {
            $obj = \Idno\Entities\GenericDataItem::getByID(self::$id);
            
            $this->validateObject($obj);
        }
        
        /**
         * Test getByID
         */
        public function testGetByUUID() {
            $obj = \Idno\Entities\GenericDataItem::getByUUID(self::$uuid);
            
            $this->validateObject($obj);
        }
        
        public function testGetByMetadata() {
            
            $null = \Idno\Entities\GenericDataItem::get(['variable1' => 'not']);
            $this->assertTrue(empty($null));
            
            $objs = \Idno\Entities\GenericDataItem::get(['variable1' => 'test']);
            $this->assertTrue(is_array($objs));
            $this->validateObject($objs[0]);
        }
        
        public function testGetByMetadataMulti() {
            
            $null = \Idno\Entities\GenericDataItem::get(['variable1' => 'test', 'variable2' => 'not']);
            $this->assertTrue(empty($null));
            
            $objs = \Idno\Entities\GenericDataItem::get(['variable1' => 'test', 'variable2' => 'test again']);
            $this->assertTrue(is_array($objs));
            $this->validateObject($objs[0]);
        }
        
        public function testSearchShort() {
            $search = array();

            $search = \Idno\Core\site()->db()->createSearchArray("sear");

            $count = \Idno\Entities\GenericDataItem::countFromX('Idno\Entities\GenericDataItem', $search);
            $this->assertTrue($count > 0);
            
            $feed  = \Idno\Entities\GenericDataItem::getFromX('Idno\Entities\GenericDataItem', $search);
            $this->assertTrue(is_array($feed));
            $this->assertTrue(($feed[0] instanceof \Idno\Entities\GenericDataItem));
        }
        
        public function testSearchLong() {
            $search = array();

            $search = \Idno\Core\site()->db()->createSearchArray("search obj");

            $count = \Idno\Entities\GenericDataItem::countFromX('Idno\Entities\GenericDataItem', $search);
            $this->assertTrue($count > 0);
            
            $feed  = \Idno\Entities\GenericDataItem::getFromX('Idno\Entities\GenericDataItem', $search);
            $this->assertTrue(is_array($feed));
            $this->assertTrue(($feed[0] instanceof \Idno\Entities\GenericDataItem));
        }
        
        public function testCountObjects() {
            $cnt = \Idno\Entities\GenericDataItem::count(['variable1' => 'test']);
            
            $this->assertTrue(is_int($cnt));
            $this->assertTrue($cnt > 0);
        }
        
        /**
         * Helper function to validate object.
         */
        protected function validateObject(&$obj) {
            
            $this->assertTrue($obj instanceof \Idno\Entities\GenericDataItem);
            $this->assertEquals("".self::$object->getID(), "".$obj->getID());
            $this->assertEquals("".self::$id, "".$obj->getID());
            $this->assertEquals(self::$uuid, $obj->getUUID());
            $this->assertEquals(self::$url, $obj->getUrl());
        }
        
        public static function tearDownAfterClass() {
            if (static::$object) static::$object->delete();
        }
    }
}

//  get by metadata, search