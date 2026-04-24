<?php

namespace Modules\School\Traits;

use Modules\School\Models\Admission\DocumentVerification;
use Illuminate\Support\Str;

trait HasVerificationHash
{
    public function getOrGenerateVerificationHash(string $type): string
    {
        $verification = DocumentVerification::where('document_type', $type)
            ->where('document_id', $this->id)
            ->first();

        if (!$verification) {
            $verification = DocumentVerification::create([
                'hash' => Str::random(32),
                'document_type' => $type,
                'document_id' => $this->id,
            ]);
        }

        return $verification->hash;
    }

    public function getVerificationUrl(string $type): string
    {
        $hash = $this->getOrGenerateVerificationHash($type);
        return url("/api/v1/public/verify/{$hash}");
    }
}
