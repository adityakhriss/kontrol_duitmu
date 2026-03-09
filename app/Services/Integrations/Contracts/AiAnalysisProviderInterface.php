<?php

namespace App\Services\Integrations\Contracts;

use App\Models\ApiConfig;

interface AiAnalysisProviderInterface
{
    public function providerKey(): string;

    public function providerLabel(): string;

    public function config(): ApiConfig;

    public function isReady(): bool;

    public function analyzeFinancialSummary(array $summary): array;

    public function testConnection(): array;
}
