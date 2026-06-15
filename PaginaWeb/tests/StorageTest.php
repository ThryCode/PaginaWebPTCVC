<?php
require_once __DIR__ . '/../public/api/storage.php';

use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{
    protected function setUp(): void
    {
        if (!is_dir(DATA_DIR)) {
            mkdir(DATA_DIR, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        $file = Storage::getFilePath('_test');
        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function testInsertReturnsItemWithId()
    {
        $result = Storage::insert('_test', ['nombre' => 'Foo']);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals(1, $result['id']);
    }

    public function testFindByIdReturnsData()
    {
        $created = Storage::insert('_test', ['nombre' => 'Bar']);
        $fetched = Storage::findById('_test', $created['id']);
        $this->assertEquals('Bar', $fetched['nombre']);
    }

    public function testFindByIdReturnsNullForMissing()
    {
        $this->assertNull(Storage::findById('_test', 999));
    }

    public function testReadReturnsArray()
    {
        Storage::insert('_test', ['nombre' => 'A']);
        Storage::insert('_test', ['nombre' => 'B']);
        $all = Storage::read('_test');
        $this->assertCount(2, $all);
    }

    public function testFindWhereFiltersCorrectly()
    {
        Storage::insert('_test', ['nombre' => 'X', 'tipo' => 'a']);
        Storage::insert('_test', ['nombre' => 'Y', 'tipo' => 'b']);
        Storage::insert('_test', ['nombre' => 'Z', 'tipo' => 'a']);
        $results = Storage::findWhere('_test', ['tipo' => 'a']);
        $this->assertCount(2, $results);
    }

    public function testUpdateModifiesData()
    {
        $created = Storage::insert('_test', ['nombre' => 'Old']);
        $updated = Storage::update('_test', $created['id'], ['nombre' => 'New']);
        $this->assertEquals('New', $updated['nombre']);
    }

    public function testDeleteRemovesData()
    {
        $created = Storage::insert('_test', ['nombre' => 'ToDelete']);
        $result = Storage::delete('_test', $created['id']);
        $this->assertTrue($result);
        $this->assertNull(Storage::findById('_test', $created['id']));
    }

    public function testDeleteReturnsFalseForMissing()
    {
        $this->assertFalse(Storage::delete('_test', 999));
    }

    public function testCountWithoutConditions()
    {
        Storage::insert('_test', ['nombre' => 'A']);
        Storage::insert('_test', ['nombre' => 'B']);
        $this->assertEquals(2, Storage::count('_test'));
    }

    public function testCountWithConditions()
    {
        Storage::insert('_test', ['nombre' => 'A', 'tipo' => 'x']);
        Storage::insert('_test', ['nombre' => 'B', 'tipo' => 'y']);
        Storage::insert('_test', ['nombre' => 'C', 'tipo' => 'x']);
        $this->assertEquals(2, Storage::count('_test', ['tipo' => 'x']));
    }
}
