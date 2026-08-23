<?php

namespace App\Services\V1;

use App\Models\CustomBatteryRequest;
use App\Models\CustomBatteryRequestFile;
use App\Models\CustomBatteryRequestRevision;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\CustomBatteryRequestSubmitted;
use App\Notifications\CustomBatteryRevisionRequested;
use App\Notifications\CustomBatteryStatusChanged;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CustomBatteryRequestService
{
    protected const ALLOWED_TRANSITIONS = [
        'submitted' => ['under_review', 'cancelled'],
        'under_review' => ['revision_requested', 'quoted', 'rejected', 'cancelled'],
        'revision_requested' => ['under_review', 'cancelled'],
        'quoted' => ['approved', 'rejected', 'cancelled'],
        'approved' => ['in_production', 'cancelled'],
        'rejected' => [],
        'in_production' => ['completed'],
        'completed' => [],
        'cancelled' => [],
    ];

    public function submit(User $customer, array $data): CustomBatteryRequest
    {
        return DB::transaction(function () use ($customer, $data) {
            $request = CustomBatteryRequest::create([
                'request_number' => $this->generateRequestNumber(),
                'customer_id' => $customer->id,
                'chemistry' => $data['chemistry'] ?? null,
                'voltage' => $data['voltage'] ?? null,
                'capacity' => $data['capacity'] ?? null,
                'kwh' => $data['kwh'] ?? null,
                'application' => $data['application'] ?? null,
                'current_load' => $data['current_load'] ?? null,
                'dimensions' => $data['dimensions'] ?? null,
                'quantity' => (int) ($data['quantity'] ?? 1),
                'deadline' => $data['deadline'] ?? null,
                'description' => $data['description'] ?? null,
                'customer_notes' => $data['customer_notes'] ?? null,
                'status' => 'submitted',
            ]);

            $this->notifyAdminsOfNewRequest($request, $customer);

            Log::info('custom_battery_request_submitted', [
                'request_id' => $request->id,
                'request_number' => $request->request_number,
                'customer_id' => $customer->id,
            ]);

            return $request->fresh(['files', 'revisions']);
        });
    }

    public function transitionStatus(
        CustomBatteryRequest $request,
        string $newStatus,
        ?User $changedBy = null,
        array $extra = []
    ): CustomBatteryRequest {
        if (! $this->canTransition($request->status, $newStatus)) {
            throw new InvalidArgumentException(
                "Tidak bisa transisi dari '{$request->status}' ke '{$newStatus}'."
            );
        }

        return DB::transaction(function () use ($request, $newStatus, $changedBy, $extra) {
            $previousStatus = $request->status;
            $now = now();

            $updates = ['status' => $newStatus];

            match ($newStatus) {
                'under_review' => $updates['assigned_at'] = $now,
                'quoted' => $updates['quoted_at'] = $now,
                'approved' => $updates['approved_at'] = $now,
                'completed' => $updates['completed_at'] = $now,
                default => null,
            };

            if (! empty($extra['admin_notes'])) {
                $updates['admin_notes'] = $extra['admin_notes'];
            }

            if (isset($extra['estimated_price'])) {
                $updates['estimated_price'] = $extra['estimated_price'];
            }

            if (isset($extra['final_price'])) {
                $updates['final_price'] = $extra['final_price'];
            }

            if (! empty($extra['assigned_to'])) {
                $updates['assigned_to'] = $extra['assigned_to'];
            }

            $request->update($updates);

            if ($customer = $request->customer) {
                $customer->notify(new CustomBatteryStatusChanged(
                    requestNumber: $request->request_number,
                    previousStatus: $previousStatus,
                    newStatus: $newStatus,
                    adminNote: $extra['admin_notes'] ?? null,
                ));
            }

            Log::info('custom_battery_status_changed', [
                'request_id' => $request->id,
                'request_number' => $request->request_number,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'changed_by' => $changedBy?->id,
            ]);

            return $request->fresh(['files', 'revisions']);
        });
    }

    public function cancelByCustomer(CustomBatteryRequest $request, User $customer, ?string $reason = null): CustomBatteryRequest
    {
        if ((int) $request->customer_id !== $customer->id) {
            throw new InvalidArgumentException('Request ini bukan milik Anda.');
        }

        if (! in_array($request->status, ['submitted', 'under_review', 'revision_requested', 'quoted'], true)) {
            throw new InvalidArgumentException(
                "Request dengan status '{$request->status}' tidak dapat dibatalkan."
            );
        }

        return DB::transaction(function () use ($request, $reason) {
            $previousStatus = $request->status;
            $request->update([
                'status' => 'cancelled',
                'customer_notes' => trim(($request->customer_notes ?? '')."\n\nCancel: {$reason}"),
            ]);

            Log::info('custom_battery_cancelled_by_customer', [
                'request_id' => $request->id,
                'request_number' => $request->request_number,
                'previous_status' => $previousStatus,
            ]);

            return $request->fresh();
        });
    }

    public function uploadFile(
        CustomBatteryRequest $request,
        UploadedFile $file,
        string $uploadedBy = 'customer'
    ): CustomBatteryRequestFile {
        $extension = $file->getClientOriginalExtension() ?: $file->extension();
        $originalName = $file->getClientOriginalName();
        $storedName = Str::uuid()->toString().'.'.strtolower($extension);
        $path = $file->storeAs(
            "custom-battery/{$request->request_number}",
            $storedName,
            'public'
        );

        $fileRecord = CustomBatteryRequestFile::create([
            'request_id' => $request->id,
            'file_path' => $path,
            'original_name' => $originalName,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => in_array($uploadedBy, ['customer', 'admin'], true) ? $uploadedBy : 'customer',
        ]);

        Log::info('custom_battery_file_uploaded', [
            'request_id' => $request->id,
            'file_id' => $fileRecord->id,
            'original_name' => $originalName,
            'uploaded_by' => $uploadedBy,
        ]);

        return $fileRecord;
    }

    public function deleteFile(CustomBatteryRequestFile $file): void
    {
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }
        $file->delete();
    }

    public function requestRevision(
        CustomBatteryRequest $request,
        User $admin,
        string $adminNote,
        ?array $fieldChanges = null
    ): CustomBatteryRequestRevision {
        if (! in_array($request->status, ['under_review', 'revision_requested'], true)) {
            throw new InvalidArgumentException(
                "Tidak bisa meminta revisi pada status '{$request->status}'."
            );
        }

        return DB::transaction(function () use ($request, $admin, $adminNote, $fieldChanges) {
            $revisionNumber = ((int) $request->revision_count) + 1;

            $revision = CustomBatteryRequestRevision::create([
                'request_id' => $request->id,
                'revision_number' => $revisionNumber,
                'requested_by' => 'admin',
                'admin_note' => $adminNote,
                'field_changes' => $fieldChanges,
                'status' => 'pending',
            ]);

            $request->update([
                'status' => 'revision_requested',
                'revision_count' => $revisionNumber,
            ]);

            if ($customer = $request->customer) {
                $customer->notify(new CustomBatteryRevisionRequested(
                    requestNumber: $request->request_number,
                    revisionNumber: $revisionNumber,
                    adminNote: $adminNote,
                    fieldChanges: $fieldChanges,
                ));
            }

            Log::info('custom_battery_revision_requested', [
                'request_id' => $request->id,
                'revision_id' => $revision->id,
                'revision_number' => $revisionNumber,
                'requested_by_admin' => $admin->id,
            ]);

            return $revision;
        });
    }

    public function respondRevision(
        CustomBatteryRequestRevision $revision,
        User $customer,
        string $response,
        ?array $updatedFields = null
    ): CustomBatteryRequestRevision {
        $request = $revision->request;

        if ((int) $request->customer_id !== $customer->id) {
            throw new InvalidArgumentException('Revision ini bukan milik Anda.');
        }

        if ($revision->status !== 'pending') {
            throw new InvalidArgumentException(
                "Revision #{$revision->revision_number} sudah berstatus '{$revision->status}'."
            );
        }

        return DB::transaction(function () use ($revision, $customer, $response, $updatedFields, $request) {
            $revision->update([
                'customer_response' => $response,
                'status' => 'responded',
                'responded_at' => now(),
            ]);

            if (! empty($updatedFields)) {
                $allowed = ['chemistry', 'voltage', 'capacity', 'kwh', 'application', 'current_load', 'dimensions', 'quantity', 'deadline', 'description', 'customer_notes'];
                $sanitized = array_intersect_key($updatedFields, array_flip($allowed));
                if (! empty($sanitized)) {
                    $request->update($sanitized);
                }
            }

            if ($request->status === 'revision_requested') {
                $request->update(['status' => 'under_review']);
            }

            Log::info('custom_battery_revision_responded', [
                'revision_id' => $revision->id,
                'request_id' => $request->id,
                'customer_id' => $customer->id,
            ]);

            return $revision->fresh();
        });
    }

    public function acceptRevision(
        CustomBatteryRequestRevision $revision,
        User $customer
    ): CustomBatteryRequestRevision {
        $request = $revision->request;

        if ((int) $request->customer_id !== $customer->id) {
            throw new InvalidArgumentException('Revision ini bukan milik Anda.');
        }

        if ($revision->status === 'accepted') {
            return $revision;
        }

        return DB::transaction(function () use ($revision, $request) {
            $revision->update(['status' => 'accepted']);

            if ($request->status === 'revision_requested') {
                $request->update(['status' => 'under_review']);
            }

            return $revision->fresh();
        });
    }

    public function generateRequestNumber(): string
    {
        $prefix = config('karteks.numbering.custom_battery.prefix', 'CBR');
        $padding = (int) config('karteks.numbering.custom_battery.padding', 5);
        $today = now()->format('Ymd');

        $lastRequest = CustomBatteryRequest::where('request_number', 'like', "{$prefix}-{$today}-%")
            ->orderByDesc('id')
            ->first();

        if ($lastRequest) {
            $parts = explode('-', $lastRequest->request_number);
            $sequence = (int) end($parts);
            $sequence++;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%s-%0'.$padding.'d', $prefix, $today, $sequence);
    }

    protected function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::ALLOWED_TRANSITIONS[$from] ?? [], true);
    }

    protected function notifyAdminsOfNewRequest(CustomBatteryRequest $request, User $customer): void
    {
        $admins = User::query()
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['super-admin', 'admin'])
                    ->where('guard_name', 'web');
            })
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new CustomBatteryRequestSubmitted(
                requestNumber: $request->request_number,
                customerName: $customer->name,
                chemistry: $request->chemistry ?? 'tidak disebutkan',
                voltage: $request->voltage ?? 'tidak disebutkan',
            ));
        }
    }
}