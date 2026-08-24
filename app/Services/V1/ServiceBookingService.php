<?php

namespace App\Services\V1;

use App\Models\BookingStatusHistory;
use App\Models\Service;
use App\Models\ServiceBooking;
use App\Models\User;
use App\Notifications\ServiceBookingCancelledNotification;
use App\Notifications\ServiceBookingConfirmedNotification;
use App\Notifications\ServiceBookingCreatedNotification;
use App\Notifications\ServiceBookingRescheduledNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ServiceBookingService
{
    protected const ALLOWED_TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['in_progress', 'rescheduled', 'cancelled'],
        'rescheduled' => ['confirmed', 'cancelled'],
        'in_progress' => ['completed', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    protected const BUSINESS_HOURS_START = 8;

    protected const BUSINESS_HOURS_END = 17;

    public function getAvailableSlots(
        Service $service,
        Carbon $date,
        ?int $technicianId = null,
        int $intervalMinutes = 60
    ): array {
        $duration = $this->resolveDuration($service);

        $slots = [];
        $start = $date->copy()->setTime(self::BUSINESS_HOURS_START, 0);
        $end = $date->copy()->setTime(self::BUSINESS_HOURS_END, 0);

        while ($start->copy()->addMinutes($duration) <= $end) {
            $slotEnd = $start->copy()->addMinutes($duration);

            $conflict = $this->checkConflict(
                serviceId: $service->id,
                technicianId: $technicianId,
                start: $start,
                end: $slotEnd,
            );

            $slots[] = [
                'start' => $start->format('Y-m-d H:i:s'),
                'end' => $slotEnd->format('Y-m-d H:i:s'),
                'start_formatted' => $start->format('H:i'),
                'end_formatted' => $slotEnd->format('H:i'),
                'available' => ! $conflict,
                'past' => $start->isPast(),
            ];

            $start->addMinutes($intervalMinutes);
        }

        return $slots;
    }

    public function createBooking(User $customer, array $data): ServiceBooking
    {
        $service = Service::findOrFail($data['service_id']);
        $scheduledAt = Carbon::parse($data['scheduled_at']);
        $duration = $this->resolveDuration($service);
        $endsAt = $scheduledAt->copy()->addMinutes($duration);

        if ($scheduledAt->isPast()) {
            throw new InvalidArgumentException('Tidak bisa booking di waktu yang sudah lewat.');
        }

        $conflict = $this->checkConflict(
            serviceId: $service->id,
            technicianId: $data['technician_id'] ?? null,
            start: $scheduledAt,
            end: $endsAt,
        );

        if ($conflict) {
            throw new InvalidArgumentException('Slot waktu ini sudah terisi. Silakan pilih slot lain.');
        }

        return DB::transaction(function () use ($customer, $data, $service, $scheduledAt, $duration) {
            $booking = ServiceBooking::create([
                'booking_number' => $this->generateBookingNumber(),
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'technician_id' => $data['technician_id'] ?? null,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? $customer->email,
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => $duration,
                'location_type' => $data['location_type'] ?? 'on_site',
                'location_address' => $data['location_address'] ?? null,
                'location_coordinates' => $data['location_coordinates'] ?? null,
                'customer_notes' => $data['customer_notes'] ?? null,
                'estimated_cost' => $data['estimated_cost'] ?? $this->resolveEstimatedCost($service),
                'status' => ServiceBooking::STATUS_PENDING,
            ]);

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'from_status' => null,
                'to_status' => ServiceBooking::STATUS_PENDING,
                'note' => 'Booking dibuat. Menunggu konfirmasi tim KARTEKS.',
                'changed_by' => $customer->id,
            ]);

            $this->notifyAdminsOfNewBooking($booking);

            Log::info('service_booking_created', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'scheduled_at' => $scheduledAt->toIso8601String(),
            ]);

            return $booking->fresh(['service', 'technician']);
        });
    }

    public function confirm(ServiceBooking $booking, User $admin, ?int $technicianId = null): ServiceBooking
    {
        if (! in_array(ServiceBooking::STATUS_CONFIRMED, self::ALLOWED_TRANSITIONS[$booking->status] ?? [], true)) {
            throw new InvalidArgumentException(
                "Booking dengan status '{$booking->status}' tidak dapat dikonfirmasi."
            );
        }

        return DB::transaction(function () use ($booking, $admin, $technicianId) {
            $previousStatus = $booking->status;
            $updates = ['status' => ServiceBooking::STATUS_CONFIRMED];

            if ($technicianId) {
                $updates['technician_id'] = $technicianId;
            }

            $booking->update($updates);

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'from_status' => $previousStatus,
                'to_status' => ServiceBooking::STATUS_CONFIRMED,
                'note' => $technicianId ? "Dikonfirmasi. Teknisi: User #{$technicianId}." : 'Dikonfirmasi oleh tim KARTEKS.',
                'changed_by' => $admin->id,
            ]);

            if ($customer = $booking->customer) {
                $customer->notify(new ServiceBookingConfirmedNotification($booking));
            }

            Log::info('service_booking_confirmed', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'previous_status' => $previousStatus,
                'technician_id' => $technicianId,
                'admin_id' => $admin->id,
            ]);

            return $booking->fresh(['service', 'technician', 'customer']);
        });
    }

    public function reschedule(
        ServiceBooking $booking,
        User $changedBy,
        Carbon $newScheduledAt,
        ?string $notes = null
    ): ServiceBooking {
        if (! $booking->is_cancellable) {
            throw new InvalidArgumentException(
                "Booking dengan status '{$booking->status}' tidak dapat dijadwalkan ulang."
            );
        }

        $duration = $booking->duration_minutes ?? $this->resolveDuration($booking->service);
        $endsAt = $newScheduledAt->copy()->addMinutes($duration);

        if ($newScheduledAt->isPast()) {
            throw new InvalidArgumentException('Tidak bisa reschedule ke waktu yang sudah lewat.');
        }

        $conflict = $this->checkConflict(
            serviceId: $booking->service_id,
            technicianId: $booking->technician_id,
            start: $newScheduledAt,
            end: $endsAt,
            excludeBookingId: $booking->id,
        );

        if ($conflict) {
            throw new InvalidArgumentException('Slot baru ini sudah terisi. Silakan pilih slot lain.');
        }

        return DB::transaction(function () use ($booking, $changedBy, $newScheduledAt, $notes) {
            $previousStatus = $booking->status;
            $oldScheduledAt = $booking->scheduled_at;

            $booking->update([
                'scheduled_at' => $newScheduledAt,
                'status' => ServiceBooking::STATUS_RESCHEDULED,
            ]);

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'from_status' => $previousStatus,
                'to_status' => ServiceBooking::STATUS_RESCHEDULED,
                'note' => $notes ?? "Dijadwal ulang dari {$oldScheduledAt->format('d M Y H:i')} ke {$newScheduledAt->format('d M Y H:i')}",
                'changed_by' => $changedBy->id,
            ]);

            $this->notifyBothParties($booking, 'rescheduled', [
                'old_scheduled_at' => $oldScheduledAt,
                'new_scheduled_at' => $newScheduledAt,
                'changed_by_name' => $changedBy->name,
            ]);

            Log::info('service_booking_rescheduled', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'old_scheduled_at' => $oldScheduledAt->toIso8601String(),
                'new_scheduled_at' => $newScheduledAt->toIso8601String(),
                'changed_by_id' => $changedBy->id,
            ]);

            return $booking->fresh(['service', 'technician', 'customer']);
        });
    }

    public function start(ServiceBooking $booking, User $user): ServiceBooking
    {
        if (! in_array(ServiceBooking::STATUS_IN_PROGRESS, self::ALLOWED_TRANSITIONS[$booking->status] ?? [], true)) {
            throw new InvalidArgumentException(
                "Booking dengan status '{$booking->status}' tidak bisa di-start."
            );
        }

        return DB::transaction(function () use ($booking, $user) {
            $previousStatus = $booking->status;
            $booking->update([
                'status' => ServiceBooking::STATUS_IN_PROGRESS,
                'started_at' => now(),
            ]);

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'from_status' => $previousStatus,
                'to_status' => ServiceBooking::STATUS_IN_PROGRESS,
                'note' => 'Layanan dimulai oleh teknisi.',
                'changed_by' => $user->id,
            ]);

            Log::info('service_booking_started', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'previous_status' => $previousStatus,
            ]);

            return $booking->fresh();
        });
    }

    public function complete(
        ServiceBooking $booking,
        User $user,
        ?float $finalCost = null,
        ?string $adminNotes = null
    ): ServiceBooking {
        if (! in_array(ServiceBooking::STATUS_COMPLETED, self::ALLOWED_TRANSITIONS[$booking->status] ?? [], true)) {
            throw new InvalidArgumentException(
                "Booking dengan status '{$booking->status}' tidak bisa diselesaikan."
            );
        }

        return DB::transaction(function () use ($booking, $user, $finalCost, $adminNotes) {
            $previousStatus = $booking->status;
            $updates = [
                'status' => ServiceBooking::STATUS_COMPLETED,
                'completed_at' => now(),
            ];

            if ($finalCost !== null) {
                $updates['final_cost'] = $finalCost;
            }

            if ($adminNotes !== null) {
                $updates['admin_notes'] = $adminNotes;
            }

            $booking->update($updates);

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'from_status' => $previousStatus,
                'to_status' => ServiceBooking::STATUS_COMPLETED,
                'note' => 'Layanan telah selesai.'.($adminNotes ? " Catatan: {$adminNotes}" : ''),
                'changed_by' => $user->id,
            ]);

            Log::info('service_booking_completed', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'final_cost' => $finalCost,
            ]);

            return $booking->fresh();
        });
    }

    public function cancelByCustomer(ServiceBooking $booking, User $customer, string $reason): ServiceBooking
    {
        if ((int) $booking->customer_id !== $customer->id) {
            throw new InvalidArgumentException('Booking ini bukan milik Anda.');
        }

        if (! $booking->is_cancellable) {
            throw new InvalidArgumentException(
                "Booking dengan status '{$booking->status}' tidak dapat dibatalkan."
            );
        }

        return $this->cancelInternal($booking, $customer, $reason, 'customer');
    }

    public function cancelByAdmin(ServiceBooking $booking, User $admin, string $reason): ServiceBooking
    {
        if (! $booking->is_cancellable) {
            throw new InvalidArgumentException(
                "Booking dengan status '{$booking->status}' tidak dapat dibatalkan."
            );
        }

        return $this->cancelInternal($booking, $admin, $reason, 'admin');
    }

    protected function cancelInternal(
        ServiceBooking $booking,
        User $changedBy,
        string $reason,
        string $cancelledBy
    ): ServiceBooking {
        return DB::transaction(function () use ($booking, $changedBy, $reason, $cancelledBy) {
            $previousStatus = $booking->status;
            $booking->update([
                'status' => ServiceBooking::STATUS_CANCELLED,
            ]);

            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'from_status' => $previousStatus,
                'to_status' => ServiceBooking::STATUS_CANCELLED,
                'note' => "Dibatalkan oleh {$cancelledBy}: {$reason}",
                'changed_by' => $changedBy->id,
            ]);

            $this->notifyBothParties($booking, 'cancelled', [
                'reason' => $reason,
                'cancelled_by' => $cancelledBy,
                'cancelled_by_name' => $changedBy->name,
            ]);

            Log::info('service_booking_cancelled', [
                'booking_id' => $booking->id,
                'booking_number' => $booking->booking_number,
                'cancelled_by' => $cancelledBy,
                'cancelled_by_id' => $changedBy->id,
                'previous_status' => $previousStatus,
            ]);

            return $booking->fresh();
        });
    }

    public function assignTechnician(ServiceBooking $booking, User $admin, int $technicianId): ServiceBooking
    {
        $technician = User::find($technicianId);
        if (! $technician) {
            throw new InvalidArgumentException('Teknisi tidak ditemukan.');
        }

        if (! in_array($booking->status, [ServiceBooking::STATUS_PENDING, ServiceBooking::STATUS_CONFIRMED], true)) {
            throw new InvalidArgumentException(
                "Teknisi hanya bisa di-assign pada status 'pending' atau 'confirmed'."
            );
        }

        $conflict = $this->checkConflict(
            serviceId: $booking->service_id,
            technicianId: $technicianId,
            start: $booking->scheduled_at,
            end: $booking->ends_at,
            excludeBookingId: $booking->id,
        );

        if ($conflict) {
            throw new InvalidArgumentException('Teknisi sudah punya booking di slot waktu ini.');
        }

        $booking->update(['technician_id' => $technicianId]);

        Log::info('service_booking_technician_assigned', [
            'booking_id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'technician_id' => $technicianId,
            'assigned_by' => $admin->id,
        ]);

        return $booking->fresh(['technician']);
    }

    public function checkConflict(
        int $serviceId,
        ?int $technicianId,
        Carbon $start,
        Carbon $end,
        ?int $excludeBookingId = null,
    ): bool {
        $query = ServiceBooking::query()
            ->whereNotIn('status', [ServiceBooking::STATUS_CANCELLED, ServiceBooking::STATUS_COMPLETED])
            ->where(function ($q) use ($start, $end) {
                $q->where('scheduled_at', '<', $end)
                    ->whereRaw('DATE_ADD(scheduled_at, INTERVAL COALESCE(duration_minutes, 60) MINUTE) > ?', [$start]);
            });

        if ($technicianId) {
            $query->where('technician_id', $technicianId);
        } else {
            $query->where('service_id', $serviceId);
        }

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->exists();
    }

    public function generateBookingNumber(): string
    {
        $prefix = config('karteks.numbering.booking.prefix', 'BKG');
        $padding = (int) config('karteks.numbering.booking.padding', 5);
        $today = now()->format('Ymd');

        $lastBooking = ServiceBooking::where('booking_number', 'like', "{$prefix}-{$today}-%")
            ->orderByDesc('id')
            ->first();

        if ($lastBooking) {
            $parts = explode('-', $lastBooking->booking_number);
            $sequence = (int) end($parts);
            $sequence++;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%s-%0'.$padding.'d', $prefix, $today, $sequence);
    }

    protected function resolveDuration(Service $service): int
    {
        return (int) ($service->duration_minutes ?? 60);
    }

    protected function resolveEstimatedCost(Service $service): ?float
    {
        return match ($service->pricing_type) {
            'fixed' => $service->base_price !== null ? (float) $service->base_price : null,
            'starting_price' => $service->starting_price !== null ? (float) $service->starting_price : null,
            default => null,
        };
    }

    protected function notifyAdminsOfNewBooking(ServiceBooking $booking): void
    {
        $admins = User::query()
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'admin', 'manager', 'technician'])
                    ->where('guard_name', 'web');
            })
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new ServiceBookingCreatedNotification($booking));
        }
    }

    protected function notifyBothParties(
        ServiceBooking $booking,
        string $event,
        array $context = []
    ): void {
        $notifiables = collect();
        if ($booking->customer) {
            $notifiables->push($booking->customer);
        }
        if ($booking->technician) {
            $notifiables->push($booking->technician);
        }

        $admins = User::query()
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'admin', 'manager'])
                    ->where('guard_name', 'web');
            })
            ->get();
        $notifiables = $notifiables->merge($admins)->unique('id');

        foreach ($notifiables as $user) {
            if ($event === 'rescheduled') {
                $user->notify(new ServiceBookingRescheduledNotification($booking));
            } elseif ($event === 'cancelled') {
                $recipient = $user->id === $booking->customer_id ? 'customer' : 'staff';
                $user->notify(new ServiceBookingCancelledNotification($booking, $recipient));
            }
        }
    }
}