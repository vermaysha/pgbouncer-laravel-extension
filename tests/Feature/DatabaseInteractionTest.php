<?php

namespace Vermaysha\PgbouncerLaravelExtension\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Vermaysha\PgbouncerLaravelExtension\Tests\App\Models\TestItem;
use Vermaysha\PgbouncerLaravelExtension\Tests\TestCase;

class DatabaseInteractionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_save_data_without_a_model()
    {
        $now = now();
        DB::table('test_items')->insert([
            'name' => 'Raw Item',
            'description' => 'A description for raw item.',
            'quantity' => 100,
            'price' => 19.99,
            'is_active' => true,
            'activated_at' => $now,
            'options' => json_encode(['color' => 'red', 'size' => 'M']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->assertDatabaseHas('test_items', [
            'name' => 'Raw Item',
            'quantity' => 100,
            'is_active' => true, // In postgres, boolean is stored as 't' or 'f' but Laravel handles assertion
        ]);

        $item = DB::table('test_items')->where('name', 'Raw Item')->first();
        $this->assertEquals('19.99', $item->price);
    }

    #[Test]
    public function it_can_save_data_using_an_eloquent_model()
    {
        $item = TestItem::create([
            'name' => 'Eloquent Model Item',
            'description' => 'A description for eloquent item.',
            'quantity' => 50,
            'price' => 120.50,
            'is_active' => false,
            'activated_at' => null,
            'options' => ['weight' => '10kg', 'priority' => 1],
        ]);

        $this->assertInstanceOf(TestItem::class, $item);
        $this->assertDatabaseHas('test_items', [
            'name' => 'Eloquent Model Item',
            'quantity' => 50,
            'is_active' => false,
        ]);

        $freshItem = TestItem::find($item->id);
        $this->assertEquals(120.50, $freshItem->price);
        $this->assertIsArray($freshItem->options);
        $this->assertEquals(['weight' => '10kg', 'priority' => 1], $freshItem->options);
    }

    #[Test]
    public function it_can_save_data_within_a_successful_transaction()
    {
        DB::transaction(function () {
            TestItem::create([
                'name' => 'Transaction Item 1',
                'quantity' => 10,
                'price' => 10.00,
                'is_active' => true,
            ]);
            DB::table('test_items')->insert([
                'name' => 'Transaction Item 2',
                'quantity' => 20,
                'price' => 20.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assert data exists inside the transaction
            $this->assertDatabaseCount('test_items', 2);
        });

        // Assert data is persisted after transaction is committed
        $this->assertDatabaseCount('test_items', 2);
        $this->assertDatabaseHas('test_items', ['name' => 'Transaction Item 1']);
        $this->assertDatabaseHas('test_items', ['name' => 'Transaction Item 2']);
    }

    #[Test]
    public function it_rolls_back_data_on_a_failed_transaction()
    {
        try {
            DB::transaction(function () {
                TestItem::create([
                    'name' => 'Should be rolled back',
                    'quantity' => 5,
                    'price' => 5.00,
                    'is_active' => true,
                ]);

                $this->assertDatabaseHas('test_items', ['name' => 'Should be rolled back']);

                // Force an exception to trigger a rollback
                throw new \Exception('Something went wrong');
            });
        } catch (\Exception $e) {
            // Catch the exception to prevent test failure
        }

        // Assert the data does not exist outside the transaction
        $this->assertDatabaseMissing('test_items', ['name' => 'Should be rolled back']);
        $this->assertDatabaseCount('test_items', 0);
    }
}
