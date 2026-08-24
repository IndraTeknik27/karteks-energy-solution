<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileAddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_primary')->orderByDesc('updated_at')->get();
        return view('profile.addresses.index', compact('addresses'));
    }

    public function create()
    {
        $address = new Address();
        return view('profile.addresses.create', compact('address'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $user = $request->user();

        DB::transaction(function () use ($user, $data) {
            if (! empty($data['is_primary'])) {
                $user->addresses()->update(['is_primary' => false]);
            } else {
                $data['is_primary'] = $user->addresses()->doesntExist();
            }
            $user->addresses()->create($data);
        });

        return redirect()->route('dashboard.addresses')->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function edit(Request $request, Address $address)
    {
        abort_unless($address->customer_id === $request->user()->id, 403);
        return view('profile.addresses.edit', compact('address'));
    }

    public function update(Request $request, Address $address)
    {
        abort_unless($address->customer_id === $request->user()->id, 403);
        $data = $this->validated($request);
        $user = $request->user();

        DB::transaction(function () use ($user, $address, $data) {
            if (! empty($data['is_primary'])) {
                $user->addresses()->where('id', '!=', $address->id)->update(['is_primary' => false]);
            }
            $address->fill($data)->save();
        });

        return redirect()->route('dashboard.addresses')->with('success', 'Alamat berhasil diperbarui.');
    }

    public function destroy(Request $request, Address $address)
    {
        abort_unless($address->customer_id === $request->user()->id, 403);
        $wasPrimary = $address->is_primary;
        $customerId = $address->customer_id;
        $address->delete();
        if ($wasPrimary) {
            Address::where('customer_id', $customerId)->orderByDesc('updated_at')->limit(1)->update(['is_primary' => true]);
        }
        return redirect()->route('dashboard.addresses')->with('success', 'Alamat berhasil dihapus.');
    }

    public function setPrimary(Request $request, Address $address)
    {
        abort_unless($address->customer_id === $request->user()->id, 403);
        $user = $request->user();
        DB::transaction(function () use ($user, $address) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_primary' => false]);
            $address->update(['is_primary' => true]);
        });
        return redirect()->route('dashboard.addresses')->with('success', 'Alamat utama diperbarui.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'label' => ['nullable', 'string', 'max:100'],
            'recipient' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,10}$/'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'village' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'regex:/^[0-9]{5}$/'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_primary' => ['nullable', 'boolean'],
        ]);
    }
}