<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Voucher;
use App\Repositories\Contracts\VoucherRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class VoucherService
{
    public function __construct(
        protected VoucherRepositoryInterface $voucherRepository
    ) {}

    public function getFilteredVouchers(array $filters = []): Collection
    {
        return $this->voucherRepository->getFilteredVouchers($filters);
    }

    public function findVoucher(int $id): ?Voucher
    {
        return $this->voucherRepository->findById($id);
    }

    public function createVoucher(array $data): Voucher
    {
        $data['code'] = strtoupper(trim($data['code']));
        return $this->voucherRepository->create($data);
    }

    public function updateVoucher(int $id, array $data): Voucher
    {
        if (!empty($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }
        return $this->voucherRepository->update($id, $data);
    }

    public function deleteVoucher(int $id): bool
    {
        return $this->voucherRepository->delete($id);
    }

    public function applyVoucher(string $code, float $seatsTotal, float $snackTotal = 0.0): array
    {
        $voucher = $this->voucherRepository->findByCode($code);

        if (!$voucher || !$voucher->is_active) {
            return ['valid' => false, 'message' => 'Mã giảm giá không tồn tại hoặc đã bị khóa.'];
        }

        if ($voucher->isExpired()) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết hạn sử dụng.'];
        }

        if ($voucher->isExhausted()) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.'];
        }

        $totalOrder = $seatsTotal + $snackTotal;
        if ($voucher->min_order_amount > 0 && $totalOrder < $voucher->min_order_amount) {
            return [
                'valid' => false,
                'message' => 'Đơn hàng chưa đạt giá trị tối thiểu ' . number_format($voucher->min_order_amount) . 'đ để áp dụng voucher này.',
            ];
        }

        if ($voucher->target === 'combo' && $snackTotal <= 0) {
            return [
                'valid' => false,
                'message' => 'Mã ưu đãi này chỉ áp dụng khi mua kèm Combo Bắp Nước.',
            ];
        }

        $discount = $voucher->calculateDiscount($seatsTotal, $snackTotal);

        if ($discount <= 0) {
            return [
                'valid' => false,
                'message' => 'Không thể áp dụng mức giảm giá cho đơn hàng hiện tại.',
            ];
        }

        return [
            'valid' => true,
            'voucher' => $voucher,
            'discount' => $discount,
            'message' => "Áp dụng mã {$voucher->code} thành công! Giảm " . number_format($discount) . 'đ',
        ];
    }
}
