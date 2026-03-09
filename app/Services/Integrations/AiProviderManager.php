<?php

namespace App\Services\Integrations;

use App\Models\Setting;
use App\Services\Integrations\Contracts\AiAnalysisProviderInterface;
use InvalidArgumentException;

class AiProviderManager
{
    /** @var array<string, AiAnalysisProviderInterface> */
    private array $providers;

    public function __construct(OpenRouterService $openRouterService)
    {
        $this->providers = [
            $openRouterService->providerKey() => $openRouterService,
        ];
    }

    public function currentProviderKey(): string
    {
        return (string) data_get(
            Setting::query()->where('key', 'ai.provider')->first()?->value,
            'provider',
            'openrouter',
        );
    }

    public function currentProvider(): AiAnalysisProviderInterface
    {
        return $this->provider($this->currentProviderKey());
    }

    public function provider(string $key): AiAnalysisProviderInterface
    {
        if (! isset($this->providers[$key])) {
            throw new InvalidArgumentException('AI provider tidak dikenali: '.$key);
        }

        return $this->providers[$key];
    }

    /** @return array<string, string> */
    public function options(): array
    {
        return collect($this->providers)
            ->mapWithKeys(fn (AiAnalysisProviderInterface $provider) => [$provider->providerKey() => $provider->providerLabel()])
            ->all();
    }

    public function isReady(): bool
    {
        return $this->currentProvider()->isReady();
    }

    public function analyzeFinancialSummary(array $summary): array
    {
        return $this->currentProvider()->analyzeFinancialSummary($summary);
    }

    public function testCurrentProvider(): array
    {
        return $this->currentProvider()->testConnection();
    }
}
