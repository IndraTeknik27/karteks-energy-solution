<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceBooking;
use App\Services\V1\ServiceBookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ServiceBookingController extends Controller
{
    public function __construct(protected ServiceBookingService $service) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $bookings = ServiceBooking::where('customer_id', $user->id)
            ->with(['service:id,name,slug', 'technician:id,name'])
            ->latest('scheduled_at')
            ->paginate(10);

        return view('dashboard.booking.index', compact('bookings'));
    }

    public function create(Request $request, ?string $serviceSlug = null)
    {
        $services = Service::where('is_active', true)->orderBy('sort')->orderBy('name')->get();

        $preselectedService = null;
        if ($serviceSlug) {
            $preselectedService = $services->firstWhere('slug', $serviceSlug);
        }

        return view('dashboard.booking.create', [
            'services' => $services,
            'preselectedService' => $preselectedService,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,10}$/'],
            'location_type' => ['required', 'string', 'in:on_site,in_store,remote'],
            'location_address' => ['required_if:location_type,on_site', 'nullable', 'string', 'max:500'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $booking = $this->service->createBooking($request->user(), $data);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['submit' => $e->getMessage()]);
        }

        return redirect()
            ->route('dashboard.booking.show', $booking->booking_number)
            ->with('success', "Booking {$booking->booking_number} berhasil dibuat. Tim KARTEKS akan konfirmasi dalam 1x24 jam.");
    }

    public function show(Request $request, string $bookingNumber)
    {
        $booking = ServiceBooking::where('booking_number', $bookingNumber)
            ->where('customer_id', $request->user()->id)
            ->with(['service', 'technician', 'statusHistories' => fn ($q) => $q->latest('created_at')])
            ->first();

        if (! $booking) {
            abort(404, 'Booking tidak ditemukan.');
        }

        return view('dashboard.booking.show', [
            'booking' => $booking,
        ]);
    }

    public function reschedule(Request $request, string $bookingNumber)
    {
        $data = $request->validate([
            'scheduled_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $booking = ServiceBooking::where('booking_number', $bookingNumber)
            ->where('customer_id', $request->user()->id)
            ->first();

        if (! $booking) {
            abort(404, 'Booking tidak ditemukan.');
        }

        try {
            $this->service->reschedule($booking, $request->user(), Carbon::parse($data['scheduled_at']), $data['notes'] ?? null);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['reschedule' => $e->getMessage()]);
        }

        return redirect()
            ->route('dashboard.booking.show', $bookingNumber)
            ->with('success', "Booking {$bookingNumber} dijadwal ulang. Tim KARTEKS akan konfirmasi via email.");
    }

    public function cancel(Request $request, string $bookingNumber)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $booking = ServiceBooking::where('booking_number', $bookingNumber)
            ->where('customer_id', $request->user()->id)
            ->first();

        if (! $booking) {
            abort(404, 'Booking tidak ditemukan.');
        }

        try {
            $this->service->cancelByCustomer($booking, $request->user(), $data['reason']);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['cancel' => $e->getMessage()]);
        }

        return redirect()
            ->route('dashboard.booking.show', $bookingNumber)
            ->with('success', "Booking {$bookingNumber} berhasil dibatalkan.");
    }

    public function getSlots(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $service = Service::findOrFail($data['service_id']);
        $date = Carbon::createFromFormat('Y-m-d', $data['date']);

        $slots = $this->service->getAvailableSlots($service, $date);

        return response()->json([
            'data' => [
                'slots' => $slots,
                'duration_minutes' => (int) ($service->duration_minutes ?? 60),
            ],
        ]);
    }
}