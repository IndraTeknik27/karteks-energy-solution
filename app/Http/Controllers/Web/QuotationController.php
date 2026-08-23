<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Services\V1\QuotationService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class QuotationController extends Controller
{
    public function __construct(protected QuotationService $service) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $quotations = Quotation::where('customer_id', $user->id)
            ->withCount('items')
            ->latest('created_at')
            ->paginate(10);

        return view('dashboard.quotation.index', compact('quotations'));
    }

    public function show(Request $request, string $quotationNumber)
    {
        $quotation = Quotation::where('quotation_number', $quotationNumber)
            ->where('customer_id', $request->user()->id)
            ->with(['items', 'creator:id,name', 'quotable'])
            ->first();

        if (! $quotation) {
            abort(404, 'Quotation tidak ditemukan.');
        }

        if ($quotation->status === 'sent') {
            try {
                $this->service->markAsViewed($quotation, $request->user());
                $quotation->refresh();
            } catch (InvalidArgumentException $e) {
                // ignore
            }
        }

        return view('dashboard.quotation.show', [
            'quotation' => $quotation,
            'company' => config('karteks.company'),
        ]);
    }

    public function accept(Request $request, string $quotationNumber)
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $quotation = Quotation::where('quotation_number', $quotationNumber)
            ->where('customer_id', $request->user()->id)
            ->first();

        if (! $quotation) {
            abort(404, 'Quotation tidak ditemukan.');
        }

        try {
            $this->service->accept($quotation, $request->user(), $data['notes'] ?? null);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['accept' => $e->getMessage()]);
        }

        return redirect()
            ->route('dashboard.quotation.show', $quotationNumber)
            ->with('success', "Quotation {$quotationNumber} telah diterima. Tim KARTEKS akan menghubungi Anda untuk langkah selanjutnya.");
    }

    public function reject(Request $request, string $quotationNumber)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $quotation = Quotation::where('quotation_number', $quotationNumber)
            ->where('customer_id', $request->user()->id)
            ->first();

        if (! $quotation) {
            abort(404, 'Quotation tidak ditemukan.');
        }

        try {
            $this->service->reject($quotation, $request->user(), $data['reason']);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['reject' => $e->getMessage()]);
        }

        return redirect()
            ->route('dashboard.quotation.show', $quotationNumber)
            ->with('success', "Quotation {$quotationNumber} telah ditolak. Terima kasih atas tanggapannya.");
    }
}