<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\V1\Customer\StoreAddressRequest;
use App\Http\Requests\Api\V1\Customer\UpdateAddressRequest;
use App\Http\Resources\V1\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $addresses = $user
            ->addresses()
            ->orderByDesc('is_primary')
            ->orderByDesc('updated_at')
            ->get();

        return $this->success(
            AddressResource::collection($addresses),
            'Daftar alamat pelanggan.',
        );
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $makePrimary = (bool) ($data['is_primary'] ?? false);

        $address = DB::transaction(function () use ($user, $data, $makePrimary) {
            $isFirstAddress = $user->addresses()->doesntExist();

            if ($isFirstAddress || $makePrimary) {
                $user->addresses()->update(['is_primary' => false]);
                $data['is_primary'] = true;
            } else {
                $data['is_primary'] = false;
            }

            return $user->addresses()->create($data);
        });

        return $this->success(
            new AddressResource($address),
            'Alamat berhasil ditambahkan.',
            201,
        );
    }

    public function show(Request $request, Address $address): JsonResponse
    {
        $this->authorizeOwnership($request, $address);

        return $this->success(
            new AddressResource($address),
            'Detail alamat.',
        );
    }

    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        $this->authorizeOwnership($request, $address);
        $data = $request->validated();
        $makePrimary = array_key_exists('is_primary', $data)
            ? (bool) $data['is_primary']
            : false;

        DB::transaction(function () use ($address, $data, $makePrimary) {
            if ($makePrimary) {
                Address::where('customer_id', $address->customer_id)
                    ->where('id', '!=', $address->id)
                    ->update(['is_primary' => false]);
                $data['is_primary'] = true;
            }

            $address->fill($data)->save();
        });

        return $this->success(
            new AddressResource($address->fresh()),
            'Alamat berhasil diperbarui.',
        );
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        $this->authorizeOwnership($request, $address);
        $wasPrimary = $address->is_primary;
        $customerId = $address->customer_id;
        $address->delete();

        if ($wasPrimary) {
            Address::where('customer_id', $customerId)
                ->orderByDesc('updated_at')
                ->limit(1)
                ->update(['is_primary' => true]);
        }

        return $this->success(null, 'Alamat berhasil dihapus.');
    }

    public function setPrimary(Request $request, Address $address): JsonResponse
    {
        $this->authorizeOwnership($request, $address);

        DB::transaction(function () use ($address) {
            Address::where('customer_id', $address->customer_id)
                ->where('id', '!=', $address->id)
                ->update(['is_primary' => false]);

            $address->forceFill(['is_primary' => true])->save();
        });

        return $this->success(
            new AddressResource($address->fresh()),
            'Alamat utama berhasil diperbarui.',
        );
    }

    protected function authorizeOwnership(Request $request, Address $address): void
    {
        abort_if(
            $address->customer_id !== $request->user()->id,
            403,
            'Anda tidak memiliki akses ke alamat ini.',
        );
    }
}