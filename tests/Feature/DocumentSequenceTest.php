<?php

namespace Tests\Feature;

use App\Enums\DocumentType;
use App\Models\Store;
use App\Services\DocumentSequenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentSequenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_it_generates_consecutive_numbers_per_store_and_type(): void
    {
        $store = Store::first();
        $service = app(DocumentSequenceService::class);

        $this->assertSame('VTA-000001', $service->nextNumber($store->id, DocumentType::Sale));
        $this->assertSame('VTA-000002', $service->nextNumber($store->id, DocumentType::Sale));
        $this->assertSame('COM-000001', $service->nextNumber($store->id, DocumentType::Purchase));
        $this->assertSame('APT-000001', $service->nextNumber($store->id, DocumentType::Layaway));
    }

    public function test_it_persists_the_next_number_in_the_sequence_row(): void
    {
        $store = Store::first();
        $service = app(DocumentSequenceService::class);

        $service->nextNumber($store->id, DocumentType::Sale);

        $this->assertDatabaseHas('document_sequences', [
            'store_id' => $store->id,
            'type' => 'sale',
            'prefix' => 'VTA-',
            'next_number' => 2,
        ]);
    }
}
