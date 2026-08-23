<?php

namespace App\Http\Controllers\Api\V1\Booking;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\V1\Booking\AdminUpdateBookingRequest;
use App\Http\Requests\Api\V1\Booking\CancelBookingRequest;
use App\Http\Requests\Api\V1\Booking\CreateBookingRequest;
use App\Http\Requests\Api\V1\Booking\RescheduleBookingRequest;
use App\Http\Resources\V1\BookingStatusHistoryResource;
use App\Http\Resources\V1\ServiceBookingResource;
use App\Models\Service;
use App\Models\ServiceBooking;
use App\Services\V1\ServiceBookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ServiceBookingController extends Controller
{
    public function __construct(
        protected ServiceBookingService $service,
    ) {}

    public function availableSlots(Request $request): JsonResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'date' => ['required', 'date_format:Y-m-d'],
            'technician_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $service = Service::findOrFail($data['service_id']);
        $date = Carbon::createFromFormat('Y-m-d', $data['date']);

        $slots = $this->service->getAvailableSlots(
            $service,
            $date,
            $data['technician_id'] ?? null,
        );

        return $this->success([
            'service_id' => $service->id,
            'service_name' => $service->name,
            'date' => $date->format('Y-m-d'),
            'duration_minutes' => (int) ($service->duration_minutes ?? 60),
            'business_hours' => [
                'start' => '08:00',
                'end' => '17:00',
            ],
            'slots' => $slots,
            'available_count' => count(array_filter($slots, fn ($s) => $s['available'])),
            'total_count' => count($slots),
        ], 'Slot tersedia berhasil dimuat.');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = ServiceBooking::where('customer_id', $user->id)
            ->with(['service:id,name,slug', 'technician:id,name'])
            ->latest('scheduled_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($upcoming = $request->boolean('upcoming')) {
            $query->upcoming();
        }

        $bookings = $query->paginate(min((int) $request->input('per_page', 15), 100));

        return $this->success($bookings, 'Daftar booking berhasil dimuat.');
    }

    public function store(CreateBookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->service->createBooking($request->user(), $request->validated());
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            (new ServiceBookingResource($booking))->toArray($request),
            "Booking {$booking->booking_number} berhasil dibuat. Tim KARTEKS akan mengkonfirmasi dalam 1x24 jam.",
            201,
        );
    }

    public function show(Request $request, string $bookingNumber): JsonResponse
    {
        $booking = $this->findForCustomer($request, $bookingNumber);
        if (! $booking) {
            return $this->notFound('Booking tidak ditemukan atau bukan milik Anda.');
        }

        $booking->load([
            'service:id,name,slug,duration_minutes',
            'technician:id,name',
            'statusHistories' => fn ($q) => $q->latest('created_at'),
        ]);

        return $this->success([
            'booking' => (new ServiceBookingResource($booking))->toArray($request),
            'status_history' => BookingStatusHistoryResource::collection($booking->statusHistories)->toArray($request),
        ], 'Detail booking berhasil dimuat.');
    }

    public function reschedule(RescheduleBookingRequest $request, string $bookingNumber): JsonResponse
    {
        $booking = $this->findForCustomer($request, $bookingNumber);
        if (! $booking) {
            return $this->notFound('Booking tidak ditemukan.');
        }

        try {
            $booking = $this->service->reschedule(
                $booking,
                $request->user(),
                Carbon::parse($request->input('scheduled_at')),
                $request->input('notes'),
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            (new ServiceBookingResource($booking))->toArray($request),
            "Booking {$booking->booking_number} berhasil dijadwal ulang. Tim KARTEKS akan konfirmasi.",
        );
    }

    public function cancel(CancelBookingRequest $request, string $bookingNumber): JsonResponse
    {
        $booking = $this->findForCustomer($request, $bookingNumber);
        if (! $booking) {
            return $this->notFound('Booking tidak ditemukan.');
        }

        try {
            $booking = $this->service->cancelByCustomer(
                $booking,
                $request->user(),
                $request->input('reason'),
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            (new ServiceBookingResource($booking))->toArray($request),
            "Booking {$booking->booking_number} berhasil dibatalkan.",
        );
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $query = ServiceBooking::query()
            ->with(['customer:id,name,email', 'technician:id,name', 'service:id,name'])
            ->latest('scheduled_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($techId = $request->input('technician_id')) {
            $query->where('technician_id', $techId);
        }
        if ($date = $request->input('date')) {
            $query->whereDate('scheduled_at', $date);
        }

        $bookings = $query->paginate(min((int) $request->input('per_page', 15), 100));

        return $this->success($bookings, 'Daftar semua booking berhasil dimuat.');
    }

    public function adminShow(Request $request, string $bookingNumber): JsonResponse
    {
        $this->authorizeAdmin($request);

        $booking = ServiceBooking::where('booking_number', $bookingNumber)
            ->with(['service', 'customer:id,name,email', 'technician:id,name', 'statusHistories' => fn ($q) => $q->latest('created_at')])
            ->first();

        if (! $booking) {
            return $this->notFound('Booking tidak ditemukan.');
        }

        return $this->success([
            'booking' => (new ServiceBookingResource($booking))->toArray($request),
            'status_history' => BookingStatusHistoryResource::collection($booking->statusHistories)->toArray($request),
        ], 'Detail booking berhasil dimuat.');
    }

    public function confirm(Request $request, string $bookingNumber): JsonResponse
    {
        $this->authorizeAdmin($request);

        $booking = $this->findAdmin($bookingNumber);
        if (! $booking) {
            return $this->notFound('Booking tidak ditemukan.');
        }

        $data = $request->validate([
            'technician_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        try {
            $booking = $this->service->confirm($booking, $request->user(), $data['technician_id'] ?? null);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            (new ServiceBookingResource($booking))->toArray($request),
            "Booking {$booking->booking_number} berhasil dikonfirmasi.",
        );
    }

    public function start(Request $request, string $bookingNumber): JsonResponse
    {
        $this->authorizeAdmin($request);

        $booking = $this->findAdmin($bookingNumber);
        if (! $booking) {
            return $this->notFound('Booking tidak ditemukan.');
        }

        try {
            $booking = $this->service->start($booking, $request->user());
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            (new ServiceBookingResource($booking))->toArray($request),
            "Booking {$booking->booking_number} dimulai.",
        );
    }

    public function complete(AdminUpdateBookingRequest $request, string $bookingNumber): JsonResponse
    {
        $this->authorizeAdmin($request);

        $booking = $this->findAdmin($bookingNumber);
        if (! $booking) {
            return $this->notFound('Booking tidak ditemukan.');
        }

        try {
            $booking = $this->service->complete(
                $booking,
                $request->user(),
                $request->input('final_cost'),
                $request->input('admin_notes'),
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            (new ServiceBookingResource($booking))->toArray($request),
            "Booking {$booking->booking_number} selesai.",
        );
    }

    public function adminCancel(CancelBookingRequest $request, string $bookingNumber): JsonResponse
    {
        $this->authorizeAdmin($request);

        $booking = $this->findAdmin($bookingNumber);
        if (! $booking) {
            return $this->notFound('Booking tidak ditemukan.');
        }

        try {
            $booking = $this->service->cancelByAdmin(
                $booking,
                $request->user(),
                $request->input('reason'),
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            (new ServiceBookingResource($booking))->toArray($request),
            "Booking {$booking->booking_number} dibatalkan oleh admin.",
        );
    }

    public function assignTechnician(AdminUpdateBookingRequest $request, string $bookingNumber): JsonResponse
    {
        $this->authorizeAdmin($request);

        $booking = $this->findAdmin($bookingNumber);
        if (! $booking) {
            return $this->notFound('Booking tidak ditemukan.');
        }

        try {
            $booking = $this->service->assignTechnician(
                $booking,
                $request->user(),
                $request->input('technician_id'),
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            (new ServiceBookingResource($booking))->toArray($request),
            "Teknisi berhasil di-assign ke booking {$booking->booking_number}.",
        );
    }

    public function adminReschedule(RescheduleBookingRequest $request, string $bookingNumber): JsonResponse
    {
        $this->authorizeAdmin($request);

        $booking = $this->findAdmin($bookingNumber);
        if (! $booking) {
            return $this->notFound('Booking tidak ditemukan.');
        }

        try {
            $booking = $this->service->reschedule(
                $booking,
                $request->user(),
                Carbon::parse($request->input('scheduled_at')),
                $request->input('notes'),
            );
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            (new ServiceBookingResource($booking))->toArray($request),
            "Booking {$booking->booking_number} dijadwal ulang oleh admin.",
        );
    }

    protected function findForCustomer(Request $request, string $bookingNumber): ?ServiceBooking
    {
        return ServiceBooking::where('booking_number', $bookingNumber)
            ->where('customer_id', $request->user()->id)
            ->first();
    }

    protected function findAdmin(string $bookingNumber): ?ServiceBooking
    {
        return ServiceBooking::where('booking_number', $bookingNumber)->first();
    }

    protected function authorizeAdmin(Request $request): void
    {
        $user = $request->user();
        if (! $user->hasAnyRole(['super-admin', 'admin', 'manager', 'technician'])) {
            abort(403, 'Anda tidak memiliki akses untuk manage booking.');
        }
    }
}