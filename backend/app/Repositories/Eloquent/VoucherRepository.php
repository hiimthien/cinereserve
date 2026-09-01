<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Voucher;
use App\Repositories\Contracts\VoucherRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class VoucherRepository implements VoucherRepositoryInterface
{
    public function getFilteredVouchers(array $filters = []): Collection
    {
        $query = Voucher::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function findById(int $id): ?Voucher
    {
        return Voucher::findOrFail($id);
    }

    public function findByCode(string $code): ?Voucher
    {
        return Voucher::where('code', strtoupper(trim($code)))->first();
    }

    public function create(array $attributes): Voucher
    {
        return Voucher::create($attributes);
    }

    public function update(int $id, array $attributes): Voucher
    {
        $voucher = Voucher::findOrFail($id);
        $voucher->update($attributes);
        return $voucher->fresh();
    }

    public function delete(int $id): bool
    {
        $voucher = Voucher::findOrFail($id);
        return (bool) $voucher->delete();
    }
}
