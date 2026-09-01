<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Collection;

interface VoucherRepositoryInterface
{
    public function getFilteredVouchers(array $filters = []): Collection;

    public function findById(int $id): ?Voucher;

    public function findByCode(string $code): ?Voucher;

    public function create(array $attributes): Voucher;

    public function update(int $id, array $attributes): Voucher;

    public function delete(int $id): bool;
}
