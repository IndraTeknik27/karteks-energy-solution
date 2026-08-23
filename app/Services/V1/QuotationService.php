<?php

namespace App\Services\V1;

use App\Models\CustomBatteryRequest;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use App\Notifications\QuotationAcceptedNotification;
use App\Notifications\QuotationRejectedNotification;
use App\Notifications\QuotationSentNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class QuotationService
{
    protected const ALLOWED_STATUS_TRANSITIONS = [
        'draft' => ['sent', 'expired'],
        'sent' => ['viewed', 'accepted', 'rejected', 'expired'],
        'viewed' => ['accepted', 'rejected', 'expired'],
        'accepted' => [],
        'rejected' => [],
        'expired' => [],
    ];

    public function createDraft(User $admin, array $data): Quotation
    {
        return DB::transaction(function () use ($admin, $data) {
            $quotation = Quotation::create([
                'quotation_number' => $this->generateQuotationNumber(),
                'customer_id' => $data['customer_id'],
                'created_by' => $admin->id,
                'quotable_type' => $data['quotable_type'] ?? null,
                'quotable_id' => $data['quotable_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'discount' => (float) ($data['discount'] ?? 0),
                'tax' => (float) ($data['tax'] ?? 0),
                'terms_conditions' => $data['terms_conditions'] ?? $this->defaultTerms(),
                'notes' => $data['notes'] ?? null,
                'valid_until' => $data['valid_until'] ?? now()->addDays(30),
                'status' => 'draft',
            ]);

            $this->syncItems($quotation, $data['items'] ?? []);

            $this->recalculateTotals($quotation);

            Log::info('quotation_draft_created', [
                'quotation_id' => $quotation->id,
                'quotation_number' => $quotation->quotation_number,
                'customer_id' => $quotation->customer_id,
                'created_by' => $admin->id,
            ]);

            return $quotation->fresh(['items', 'customer', 'quotable']);
        });
    }

    public function send(Quotation $quotation, User $admin): Quotation
    {
        if (! in_array('sent', self::ALLOWED_STATUS_TRANSITIONS[$quotation->status] ?? [], true)) {
            throw new InvalidArgumentException(
                "Quotation dengan status '{$quotation->status}' tidak dapat dikirim."
            );
        }

        return DB::transaction(function () use ($quotation, $admin) {
            $previousStatus = $quotation->status;
            $quotation->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            if ($customer = $quotation->customer) {
                $customer->notify(new QuotationSentNotification(
                    quotationNumber: $quotation->quotation_number,
                    title: $quotation->title,
                    total: (float) $quotation->total,
                    validUntil: $quotation->valid_until,
                ));
            }

            Log::info('quotation_sent', [
                'quotation_id' => $quotation->id,
                'quotation_number' => $quotation->quotation_number,
                'previous_status' => $previousStatus,
                'sent_by' => $admin->id,
            ]);

            return $quotation->fresh();
        });
    }

    public function markAsViewed(Quotation $quotation, User $customer): Quotation
    {
        if ((int) $quotation->customer_id !== $customer->id) {
            throw new InvalidArgumentException('Quotation ini bukan milik Anda.');
        }

        if ($quotation->status === 'sent') {
            return DB::transaction(function () use ($quotation) {
                $quotation->update([
                    'status' => 'viewed',
                    'viewed_at' => now(),
                ]);

                return $quotation->fresh();
            });
        }

        return $quotation;
    }

    public function accept(Quotation $quotation, User $customer, ?string $customerNotes = null): Quotation
    {
        if ((int) $quotation->customer_id !== $customer->id) {
            throw new InvalidArgumentException('Quotation ini bukan milik Anda.');
        }

        if (! in_array($quotation->status, ['sent', 'viewed'], true)) {
            throw new InvalidArgumentException(
                "Quotation dengan status '{$quotation->status}' tidak dapat diterima."
            );
        }

        if ($quotation->is_expired) {
            throw new InvalidArgumentException('Quotation sudah kadaluarsa.');
        }

        return DB::transaction(function () use ($quotation, $customer, $customerNotes) {
            $previousStatus = $quotation->status;
            $quotation->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'notes' => $customerNotes
                    ? trim(($quotation->notes ?? '')."\n\n[Customer] {$customerNotes}")
                    : $quotation->notes,
            ]);

            if ($quotation->quotable_type === CustomBatteryRequest::class) {
                $customBattery = $quotation->quotable;
                if ($customBattery && $customBattery->status === 'quoted') {
                    $customBattery->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'final_price' => $quotation->total,
                    ]);
                }
            }

            if ($admin = $quotation->creator) {
                $admin->notify(new QuotationAcceptedNotification(
                    quotationNumber: $quotation->quotation_number,
                    customerName: $customer->name,
                    total: (float) $quotation->total,
                ));
            }

            Log::info('quotation_accepted', [
                'quotation_id' => $quotation->id,
                'quotation_number' => $quotation->quotation_number,
                'previous_status' => $previousStatus,
                'customer_id' => $customer->id,
            ]);

            return $quotation->fresh();
        });
    }

    public function reject(Quotation $quotation, User $customer, string $reason): Quotation
    {
        if ((int) $quotation->customer_id !== $customer->id) {
            throw new InvalidArgumentException('Quotation ini bukan milik Anda.');
        }

        if (! in_array($quotation->status, ['sent', 'viewed'], true)) {
            throw new InvalidArgumentException(
                "Quotation dengan status '{$quotation->status}' tidak dapat ditolak."
            );
        }

        return DB::transaction(function () use ($quotation, $customer, $reason) {
            $previousStatus = $quotation->status;
            $quotation->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => $reason,
            ]);

            if ($admin = $quotation->creator) {
                $admin->notify(new QuotationRejectedNotification(
                    quotationNumber: $quotation->quotation_number,
                    customerName: $customer->name,
                    reason: $reason,
                ));
            }

            Log::info('quotation_rejected', [
                'quotation_id' => $quotation->id,
                'quotation_number' => $quotation->quotation_number,
                'previous_status' => $previousStatus,
                'customer_id' => $customer->id,
                'reason' => $reason,
            ]);

            return $quotation->fresh();
        });
    }

    public function markAsExpired(Quotation $quotation): Quotation
    {
        if ($quotation->status === 'accepted' || $quotation->status === 'rejected' || $quotation->status === 'expired') {
            return $quotation;
        }

        return DB::transaction(function () use ($quotation) {
            $quotation->update(['status' => 'expired']);
            return $quotation->fresh();
        });
    }

    public function updateDraft(Quotation $quotation, array $data): Quotation
    {
        if ($quotation->status !== 'draft') {
            throw new InvalidArgumentException(
                "Hanya quotation draft yang dapat diubah. Status saat ini: '{$quotation->status}'."
            );
        }

        return DB::transaction(function () use ($quotation, $data) {
            $quotation->update(array_intersect_key($data, array_flip([
                'title', 'description', 'discount', 'tax',
                'terms_conditions', 'notes', 'valid_until',
                'quotable_type', 'quotable_id',
            ])));

            if (isset($data['items'])) {
                $quotation->items()->delete();
                $this->syncItems($quotation, $data['items']);
            }

            $this->recalculateTotals($quotation);

            return $quotation->fresh(['items', 'customer', 'quotable']);
        });
    }

    public function syncItems(Quotation $quotation, array $items): void
    {
        $sort = 0;
        foreach ($items as $item) {
            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'name' => $item['name'],
                'description' => $item['description'] ?? null,
                'qty' => (int) $item['qty'],
                'unit_price' => (float) $item['unit_price'],
                'sort' => $sort++,
            ]);
        }
    }

    public function recalculateTotals(Quotation $quotation): void
    {
        $subtotal = (float) $quotation->items()->sum(\DB::raw('qty * unit_price'));
        $discount = (float) $quotation->discount;
        $tax = (float) $quotation->tax;
        $total = max(0, $subtotal - $discount + $tax);

        $quotation->update([
            'subtotal' => $subtotal,
            'total' => $total,
        ]);
    }

    public function generateQuotationNumber(): string
    {
        $prefix = config('karteks.numbering.quotation.prefix', 'QTN');
        $padding = (int) config('karteks.numbering.quotation.padding', 5);
        $today = now()->format('Ymd');

        $lastQuotation = Quotation::where('quotation_number', 'like', "{$prefix}-{$today}-%")
            ->orderByDesc('id')
            ->first();

        if ($lastQuotation) {
            $parts = explode('-', $lastQuotation->quotation_number);
            $sequence = (int) end($parts);
            $sequence++;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%s-%0'.$padding.'d', $prefix, $today, $sequence);
    }

    protected function defaultTerms(): string
    {
        return "1. Harga belum termasuk PPN 11% (sudah di-include dalam total).\n"
            ."2. Quotation berlaku sampai tanggal yang tertera.\n"
            ."3. Pembayaran 50% di muka, 50% sebelum pengiriman.\n"
            ."4. Garansi produk 1 tahun untuk kerusakan pabrik.\n"
            ."5. Estimasi waktu produksi terhitung setelah pembayaran diterima.";
    }
}