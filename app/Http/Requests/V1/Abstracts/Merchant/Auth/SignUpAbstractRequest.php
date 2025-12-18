<?php

namespace App\Http\Requests\V1\Abstracts\Merchant\Auth;

use App\Http\Requests\PlatformRequest;
use App\Repository\Contracts\DomainRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

abstract class SignUpAbstractRequest extends PlatformRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $subdomain = (string)$this->input('merchant_subdomain', '');
        $subdomain = $this->normalizeSubdomain($subdomain);

        if ($subdomain === '' || str_contains($subdomain, '.')) {
            throw ValidationException::withMessages([
                'merchant_subdomain' => __('validation.regex', ['attribute' => 'merchant_subdomain']),
            ]);
        }

        $this->ensureUniqueOrFail($subdomain);

        $base = (string)config('app.domain');
        $base = parse_url('//' . $base, PHP_URL_HOST) ?: $base;

        $this->merge([
            'merchant_domain' => "{$subdomain}.{$base}",
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email:rfc,dns'],
            'password' => ['required', Password::min(8)->letters()->numbers()->symbols()],
            'merchant_name' => ['required', 'string'],
            'merchant_description' => ['required', 'string', 'min:100'],
            'merchant_subdomain' => ['required', 'string', 'max:50', 'regex:/^(?!-)[A-Za-z0-9-]+(?<!-)$/'],
            'merchant_domain' => ['present', 'string'],
        ];
    }

    private function normalizeSubdomain(string $value): string
    {
        $value = Str::of($value)->trim()->lower()->replaceMatches('/\s+/', '-')->value();

        if (str_contains($value, '.')) {
            $value = Str::of($value)->before('.')->value();
        }

        return $value;
    }

    private function ensureUniqueOrFail(string $subdomain): void
    {
        $base = (string)config('app.domain');
        $base = parse_url('//' . $base, PHP_URL_HOST) ?: $base;
        $full = "{$subdomain}.{$base}";

        /** @var \App\Repository\Contracts\DomainRepositoryInterface $domainRepository */
        $domainRepository = resolve(DomainRepositoryInterface::class);

        $isUnique = $domainRepository->isUnique($full);

        if (!$isUnique) {
            throw ValidationException::withMessages([
                'merchant_subdomain' => __('validation.unique', ['attribute' => 'merchant_subdomain']),
            ]);
        }
    }
}
