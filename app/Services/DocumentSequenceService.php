<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\DocumentSequence;
use Illuminate\Support\Facades\DB;

/**
 * Issues strict consecutive document numbers per store and document type.
 *
 * The sequence row is locked with `lockForUpdate` before it is consumed,
 * so two concurrent sales can never share a number.
 */
class DocumentSequenceService
{
    /**
     * Return the next formatted number and advance the sequence.
     */
    public function nextNumber(int $storeId, DocumentType $type): string
    {
        return DB::transaction(function () use ($storeId, $type): string {
            $sequence = $this->lockSequence($storeId, $type);

            if ($sequence === null) {
                DocumentSequence::create([
                    'store_id' => $storeId,
                    'type' => $type->value,
                    'prefix' => $type->prefix(),
                    'next_number' => 1,
                ]);

                $sequence = $this->lockSequence($storeId, $type);
            }

            $number = $sequence->next_number;
            $sequence->next_number = $number + 1;
            $sequence->save();

            return $sequence->prefix.str_pad((string) $number, 6, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Fetch the sequence row for update, bypassing the store scope.
     */
    protected function lockSequence(int $storeId, DocumentType $type): ?DocumentSequence
    {
        return DocumentSequence::withoutGlobalScopes()
            ->where('store_id', $storeId)
            ->where('type', $type->value)
            ->lockForUpdate()
            ->first();
    }
}
