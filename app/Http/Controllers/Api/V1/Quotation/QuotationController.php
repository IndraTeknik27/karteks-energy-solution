<?php

namespace App\Http\Controllers\Api\V1\Quotation;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\V1\Quotation\RejectQuotationRequest;
use App\Http\Requests\Api\V1\Quotation\RespondQuotationRequest;
use App\Http\Requests\Api\V1\Quotation\StoreQuotationRequest;
use App\Http\Requests\Api\V1\Quotation\UpdateQuotationRequest;
use App\Http\Resources\V1\QuotationResource;
use App\Models\CustomBatteryRequest;
use App\Models\Quotation;
use App\Services\V1\QuotationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class QuotationController extends Controller
{
    public function __construct(
        protected QuotationService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Quotation::query()
            ->where('customer_id', $user->id)
            ->withCount('items')
            ->latest('created_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $quotations = $query->paginate(min((int) $request->input('per_page', 15), 100));

        return $this->success($quotations, 'Daftar quotation berhasil dimuat.');
    }

    public function show(Request $request, string $quotationNumber): JsonResponse
    {
        $quotation = Quotation::where('quotation_number', $quotationNumber)
            ->where('customer_id', $request->user()->id)
            ->with(['items', 'customer:id,name,email', 'quotable', 'creator:id,name'])
            ->first();

        if (! $quotation) {
            return $this->notFound('Quotation tidak ditemukan atau bukan milik Anda.');
        }

        if (in_array($quotation->status, ['sent'], true)) {
            try {
                $this->service->markAsViewed($quotation, $request->user());
                $quotation->refresh();
            } catch (InvalidArgumentException $e) {
                // ignore
            }
        }

        return $this->success(
            (new QuotationResource($quotation))->toArray($request),
            'Detail quotation berhasil dimuat.',
        );
    }

    public function accept(RespondQuotationRequest $request, string $quotationNumber): JsonResponse
    {
        $quotation = $this->findForCustomer($request, $quotationNumber);
        if (! $quotation) {
            return $this->notFound('Quotation tidak ditemukan.');
        }

        try {
            $quotation = $this->service->accept(
                $quotation,
                $request->user(),
                $request->input('notes'),
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            (new QuotationResource($quotation->fresh(['items'])))->toArray($request),
            "Quotation {$quotation->quotation_number} berhasil diterima.",
        );
    }

    public function reject(RejectQuotationRequest $request, string $quotationNumber): JsonResponse
    {
        $quotation = $this->findForCustomer($request, $quotationNumber);
        if (! $quotation) {
            return $this->notFound('Quotation tidak ditemukan.');
        }

        try {
            $quotation = $this->service->reject(
                $quotation,
                $request->user(),
                $request->input('reason'),
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            (new QuotationResource($quotation->fresh(['items'])))->toArray($request),
            "Quotation {$quotation->quotation_number} ditolak.",
        );
    }

    public function pdf(Request $request, string $quotationNumber)
    {
        $quotation = $this->findForCustomer($request, $quotationNumber);
        if (! $quotation) {
            return $this->notFound('Quotation tidak ditemukan.');
        }

        if (! class_exists(\Spatie\LaravelPdf\Facades\Pdf::class)) {
            return $this->error(
                'PDF generation belum tersedia. Install dompdf/dompdf untuk mengaktifkan fitur ini.',
                501
            );
        }

        try {
            $pdf = \Spatie\LaravelPdf\Facades\Pdf::view('pdf.quotation', ['quotation' => $quotation->load(['items', 'customer', 'creator'])])
                ->format('a4')
                ->margins(20, 20, 20, 20);

            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"Quotation-{$quotation->quotation_number}.pdf\"",
            ]);
        } catch (\Throwable $e) {
            return $this->error('Gagal generate PDF: '.$e->getMessage(), 500);
        }
    }

    public function store(StoreQuotationRequest $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        if ($request->filled('quotable_type') && $request->filled('quotable_id')) {
            $quotable = $request->input('quotable_type')::find($request->input('quotable_id'));
            if (! $quotable) {
                return $this->error('Quotable entity tidak ditemukan.', 422);
            }
        }

        try {
            $quotation = $this->service->createDraft($request->user(), $request->validated());
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            (new QuotationResource($quotation))->toArray($request),
            "Draft quotation {$quotation->quotation_number} berhasil dibuat.",
            201,
        );
    }

    public function update(UpdateQuotationRequest $request, string $quotationNumber): JsonResponse
    {
        $this->authorizeAdmin($request);

        $quotation = Quotation::where('quotation_number', $quotationNumber)->first();
        if (! $quotation) {
            return $this->notFound('Quotation tidak ditemukan.');
        }

        try {
            $quotation = $this->service->updateDraft($quotation, $request->validated());
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            (new QuotationResource($quotation))->toArray($request),
            'Quotation berhasil diperbarui.',
        );
    }

    public function send(Request $request, string $quotationNumber): JsonResponse
    {
        $this->authorizeAdmin($request);

        $quotation = Quotation::where('quotation_number', $quotationNumber)->first();
        if (! $quotation) {
            return $this->notFound('Quotation tidak ditemukan.');
        }

        try {
            $quotation = $this->service->send($quotation, $request->user());
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            (new QuotationResource($quotation))->toArray($request),
            "Quotation {$quotation->quotation_number} berhasil dikirim ke customer.",
        );
    }

    public function markExpired(Request $request, string $quotationNumber): JsonResponse
    {
        $this->authorizeAdmin($request);

        $quotation = Quotation::where('quotation_number', $quotationNumber)->first();
        if (! $quotation) {
            return $this->notFound('Quotation tidak ditemukan.');
        }

        $quotation = $this->service->markAsExpired($quotation);

        return $this->success(
            (new QuotationResource($quotation))->toArray($request),
            "Quotation {$quotation->quotation_number} ditandai kadaluarsa.",
        );
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $query = Quotation::query()
            ->with(['customer:id,name,email', 'creator:id,name'])
            ->withCount('items')
            ->latest('created_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        $quotations = $query->paginate(min((int) $request->input('per_page', 15), 100));

        return $this->success($quotations, 'Daftar semua quotation berhasil dimuat.');
    }

    public function adminShow(Request $request, string $quotationNumber): JsonResponse
    {
        $this->authorizeAdmin($request);

        $quotation = Quotation::where('quotation_number', $quotationNumber)
            ->with(['items', 'customer:id,name,email', 'creator:id,name', 'quotable'])
            ->first();

        if (! $quotation) {
            return $this->notFound('Quotation tidak ditemukan.');
        }

        return $this->success(
            (new QuotationResource($quotation))->toArray($request),
            'Detail quotation berhasil dimuat.',
        );
    }

    protected function findForCustomer(Request $request, string $quotationNumber): ?Quotation
    {
        return Quotation::where('quotation_number', $quotationNumber)
            ->where('customer_id', $request->user()->id)
            ->first();
    }

    protected function authorizeAdmin(Request $request): void
    {
        $user = $request->user();
        if (! $user->hasAnyRole(['super-admin', 'admin', 'sales', 'manager'])) {
            abort(403, 'Anda tidak memiliki akses untuk membuat quotation.');
        }
    }
}