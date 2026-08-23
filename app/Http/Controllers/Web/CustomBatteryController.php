<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CustomBatteryRequest;
use App\Services\V1\CustomBatteryRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class CustomBatteryController extends Controller
{
    public function __construct(protected CustomBatteryRequestService $service) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $requests = CustomBatteryRequest::where('customer_id', $user->id)
            ->withCount(['files', 'revisions'])
            ->latest('created_at')
            ->paginate(10);

        return view('dashboard.custom-battery.index', compact('requests'));
    }

    public function create()
    {
        $options = [
            'chemistry' => config('karteks.battery_options.chemistry'),
            'voltage' => config('karteks.battery_options.voltage'),
            'applications' => config('karteks.battery_options.applications'),
        ];

        return view('dashboard.custom-battery.create', compact('options'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'chemistry' => ['required', 'string', 'in:'.implode(',', config('karteks.battery_options.chemistry', []))],
            'voltage' => ['required', 'string', 'in:'.implode(',', config('karteks.battery_options.voltage', []))],
            'capacity' => ['nullable', 'string', 'max:50'],
            'kwh' => ['nullable', 'numeric', 'min:0', 'max:9999.99'],
            'application' => ['required', 'string', 'in:'.implode(',', array_keys(config('karteks.battery_options.applications', [])))],
            'current_load' => ['nullable', 'string', 'max:100'],
            'dimensions' => ['nullable', 'array'],
            'dimensions.length' => ['required_with:dimensions', 'numeric', 'min:0'],
            'dimensions.width' => ['required_with:dimensions', 'numeric', 'min:0'],
            'dimensions.height' => ['required_with:dimensions', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'deadline' => ['nullable', 'date', 'after:today'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $cbRequest = $this->service->submit($request->user(), $data);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['submit' => $e->getMessage()]);
        }

        return redirect()
            ->route('dashboard.custom-battery.show', $cbRequest->request_number)
            ->with('success', "Permintaan {$cbRequest->request_number} berhasil disubmit.");
    }

    public function show(Request $request, string $requestNumber)
    {
        $cbRequest = CustomBatteryRequest::where('request_number', $requestNumber)
            ->where('customer_id', $request->user()->id)
            ->with(['files', 'revisions'])
            ->first();

        if (! $cbRequest) {
            abort(404, 'Permintaan tidak ditemukan.');
        }

        return view('dashboard.custom-battery.show', [
            'request' => $cbRequest,
        ]);
    }

    public function cancel(Request $request, string $requestNumber)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $cbRequest = CustomBatteryRequest::where('request_number', $requestNumber)
            ->where('customer_id', $request->user()->id)
            ->first();

        if (! $cbRequest) {
            abort(404, 'Permintaan tidak ditemukan.');
        }

        try {
            $this->service->cancelByCustomer($cbRequest, $request->user(), $data['reason']);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['cancel' => $e->getMessage()]);
        }

        return redirect()
            ->route('dashboard.custom-battery.show', $requestNumber)
            ->with('success', "Permintaan {$requestNumber} berhasil dibatalkan.");
    }

    public function uploadFile(Request $request, string $requestNumber)
    {
        $cbRequest = CustomBatteryRequest::where('request_number', $requestNumber)
            ->where('customer_id', $request->user()->id)
            ->first();

        if (! $cbRequest) {
            abort(404, 'Permintaan tidak ditemukan.');
        }

        $request->validate([
            'file' => ['required', 'file', 'max:'.((int) config('karteks.upload.max_file_size_kb', 10240)), 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,zip,rar,dwg,dxf,step,iges'],
        ]);

        $this->service->uploadFile($cbRequest, $request->file('file'), 'customer');

        return back()->with('success', 'File berhasil diunggah.');
    }

    public function respondRevision(Request $request, string $requestNumber, int $revisionId)
    {
        $data = $request->validate([
            'customer_response' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $revision = \App\Models\CustomBatteryRequestRevision::where('id', $revisionId)
            ->whereHas('request', fn ($q) => $q->where('request_number', $requestNumber)->where('customer_id', $request->user()->id))
            ->first();

        if (! $revision) {
            abort(404, 'Revision tidak ditemukan.');
        }

        try {
            $this->service->respondRevision($revision, $request->user(), $data['customer_response']);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['revision' => $e->getMessage()]);
        }

        return back()->with('success', 'Tanggapan revisi berhasil dikirim.');
    }
}