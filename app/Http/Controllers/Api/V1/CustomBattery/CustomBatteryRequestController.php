<?php

namespace App\Http\Controllers\Api\V1\CustomBattery;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\V1\CustomBattery\RespondRevisionRequest;
use App\Http\Requests\Api\V1\CustomBattery\StoreCustomBatteryRequest;
use App\Http\Requests\Api\V1\CustomBattery\StoreRevisionRequest;
use App\Http\Requests\Api\V1\CustomBattery\TransitionStatusRequest;
use App\Http\Requests\Api\V1\CustomBattery\UpdateCustomBatteryRequest;
use App\Http\Requests\Api\V1\CustomBattery\UploadFileRequest;
use App\Http\Resources\V1\CustomBatteryRequestFileResource;
use App\Http\Resources\V1\CustomBatteryRequestResource;
use App\Http\Resources\V1\CustomBatteryRequestRevisionResource;
use App\Models\CustomBatteryRequest;
use App\Models\CustomBatteryRequestFile;
use App\Models\CustomBatteryRequestRevision;
use App\Services\V1\CustomBatteryRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CustomBatteryRequestController extends Controller
{
    public function __construct(
        protected CustomBatteryRequestService $service,
    ) {}

    public function options(): JsonResponse
    {
        return $this->success([
            'chemistry' => config('karteks.battery_options.chemistry'),
            'voltage' => config('karteks.battery_options.voltage'),
            'applications' => config('karteks.battery_options.applications'),
        ], 'Custom battery options.');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = CustomBatteryRequest::query()
            ->where('customer_id', $user->id)
            ->withCount(['files', 'revisions'])
            ->latest('created_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(min((int) $request->input('per_page', 15), 100));

        return $this->success($requests, 'Daftar permintaan custom battery berhasil dimuat.');
    }

    public function store(StoreCustomBatteryRequest $request): JsonResponse
    {
        try {
            $cbRequest = $this->service->submit($request->user(), $request->validated());
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            new CustomBatteryRequestResource($cbRequest),
            "Permintaan {$cbRequest->request_number} berhasil disubmit. Tim kami akan meninjau dalam 1x24 jam.",
            201,
        );
    }

    public function show(Request $request, string $requestNumber): JsonResponse
    {
        $cbRequest = $this->findRequest($request, $requestNumber);

        if (! $cbRequest) {
            return $this->notFound('Permintaan tidak ditemukan atau bukan milik Anda.');
        }

        $cbRequest->load(['files', 'revisions', 'customer:id,name,email', 'assignedTo:id,name']);

        return $this->success(
            new CustomBatteryRequestResource($cbRequest),
            'Detail permintaan berhasil dimuat.',
        );
    }

    public function update(UpdateCustomBatteryRequest $request, string $requestNumber): JsonResponse
    {
        $cbRequest = $this->findRequest($request, $requestNumber);

        if (! $cbRequest) {
            return $this->notFound('Permintaan tidak ditemukan atau bukan milik Anda.');
        }

        if (! in_array($cbRequest->status, ['submitted', 'revision_requested'], true)) {
            return $this->error(
                "Permintaan dengan status '{$cbRequest->status}' tidak dapat diubah.",
                422
            );
        }

        $cbRequest->update($request->validated());

        return $this->success(
            new CustomBatteryRequestResource($cbRequest->fresh(['files', 'revisions'])),
            'Permintaan berhasil diperbarui.',
        );
    }

    public function cancel(Request $request, string $requestNumber): JsonResponse
    {
        $cbRequest = $this->findRequest($request, $requestNumber);

        if (! $cbRequest) {
            return $this->notFound('Permintaan tidak ditemukan.');
        }

        $reason = $request->input('reason');

        try {
            $cbRequest = $this->service->cancelByCustomer($cbRequest, $request->user(), $reason);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            new CustomBatteryRequestResource($cbRequest->fresh()),
            "Permintaan {$cbRequest->request_number} berhasil dibatalkan.",
        );
    }

    public function transitionStatus(TransitionStatusRequest $request, string $requestNumber): JsonResponse
    {
        $cbRequest = CustomBatteryRequest::where('request_number', $requestNumber)
            ->first();

        if (! $cbRequest) {
            return $this->notFound('Permintaan tidak ditemukan.');
        }

        try {
            $cbRequest = $this->service->transitionStatus(
                $cbRequest,
                $request->input('new_status'),
                $request->user(),
                $request->only(['admin_notes', 'estimated_price', 'final_price', 'assigned_to']),
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            new CustomBatteryRequestResource($cbRequest->fresh(['files', 'revisions'])),
            "Status permintaan diubah ke '{$cbRequest->status}'.",
        );
    }

    public function uploadFile(UploadFileRequest $request, string $requestNumber): JsonResponse
    {
        $cbRequest = $this->findRequest($request, $requestNumber);

        if (! $cbRequest) {
            return $this->notFound('Permintaan tidak ditemukan.');
        }

        $file = $this->service->uploadFile(
            $cbRequest,
            $request->file('file'),
            $request->user()->id === $cbRequest->customer_id ? 'customer' : 'admin',
        );

        return $this->success(
            new CustomBatteryRequestFileResource($file),
            'File berhasil diunggah.',
            201,
        );
    }

    public function deleteFile(Request $request, string $requestNumber, int $fileId): JsonResponse
    {
        $file = CustomBatteryRequestFile::where('id', $fileId)
            ->whereHas('request', fn ($q) => $q->where('request_number', $requestNumber))
            ->first();

        if (! $file) {
            return $this->notFound('File tidak ditemukan.');
        }

        $cbRequest = $file->request;
        if ((int) $cbRequest->customer_id !== $request->user()->id) {
            return $this->forbidden('Anda tidak memiliki akses untuk menghapus file ini.');
        }

        if (! in_array($cbRequest->status, ['submitted', 'revision_requested'], true)) {
            return $this->error(
                "File tidak dapat dihapus pada status '{$cbRequest->status}'.",
                422
            );
        }

        $this->service->deleteFile($file);

        return $this->success(null, 'File berhasil dihapus.');
    }

    public function requestRevision(StoreRevisionRequest $request, string $requestNumber): JsonResponse
    {
        $cbRequest = CustomBatteryRequest::where('request_number', $requestNumber)->first();
        if (! $cbRequest) {
            return $this->notFound('Permintaan tidak ditemukan.');
        }

        try {
            $revision = $this->service->requestRevision(
                $cbRequest,
                $request->user(),
                $request->input('admin_note'),
                $request->input('field_changes'),
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            new CustomBatteryRequestRevisionResource($revision),
            "Revisi #{$revision->revision_number} berhasil diminta.",
            201,
        );
    }

    public function respondRevision(
        RespondRevisionRequest $request,
        string $requestNumber,
        int $revisionId
    ): JsonResponse {
        $revision = CustomBatteryRequestRevision::where('id', $revisionId)
            ->whereHas('request', fn ($q) => $q->where('request_number', $requestNumber))
            ->first();

        if (! $revision) {
            return $this->notFound('Revision tidak ditemukan.');
        }

        try {
            $revision = $this->service->respondRevision(
                $revision,
                $request->user(),
                $request->input('customer_response'),
                $request->input('updated_fields'),
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            new CustomBatteryRequestRevisionResource($revision),
            "Revisi #{$revision->revision_number} berhasil ditanggapi.",
        );
    }

    public function acceptRevision(Request $request, string $requestNumber, int $revisionId): JsonResponse
    {
        $revision = CustomBatteryRequestRevision::where('id', $revisionId)
            ->whereHas('request', fn ($q) => $q->where('request_number', $requestNumber))
            ->first();

        if (! $revision) {
            return $this->notFound('Revision tidak ditemukan.');
        }

        try {
            $revision = $this->service->acceptRevision($revision, $request->user());
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            new CustomBatteryRequestRevisionResource($revision),
            "Revisi #{$revision->revision_number} diterima.",
        );
    }

    public function listFiles(Request $request, string $requestNumber): JsonResponse
    {
        $cbRequest = $this->findRequest($request, $requestNumber);
        if (! $cbRequest) {
            return $this->notFound('Permintaan tidak ditemukan.');
        }

        $files = $cbRequest->files()->latest('created_at')->get();

        return $this->success(
            CustomBatteryRequestFileResource::collection($files)->toArray($request),
            'Daftar file berhasil dimuat.',
        );
    }

    public function listRevisions(Request $request, string $requestNumber): JsonResponse
    {
        $cbRequest = $this->findRequest($request, $requestNumber);
        if (! $cbRequest) {
            return $this->notFound('Permintaan tidak ditemukan.');
        }

        $revisions = $cbRequest->revisions()->orderByDesc('revision_number')->get();

        return $this->success(
            CustomBatteryRequestRevisionResource::collection($revisions)->toArray($request),
            'Daftar revisi berhasil dimuat.',
        );
    }

    protected function findRequest(Request $request, string $requestNumber): ?CustomBatteryRequest
    {
        return CustomBatteryRequest::where('request_number', $requestNumber)
            ->where('customer_id', $request->user()->id)
            ->first();
    }
}