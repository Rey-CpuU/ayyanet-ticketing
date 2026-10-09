@php $customer = $customer ?? null; @endphp
<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="name" class="label">Nama <span class="text-[var(--red-text)]">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $customer?->name) }}" required placeholder="Nama customer" class="input">
        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
    </div>

    <div>
        <label for="phone" class="label">No HP <span class="text-[var(--red-text)]">*</span></label>
        <input type="text" name="phone" id="phone" value="{{ old('phone', $customer?->phone) }}" required placeholder="mis. 0812-3456-7890" class="input">
        <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
    </div>

    <div class="sm:col-span-2">
        <label for="email" class="label">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email', $customer?->email) }}" placeholder="nama@contoh.com" class="input">
        <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
    </div>

    <div class="sm:col-span-2">
        <label for="address" class="label">Alamat <span class="text-[var(--red-text)]">*</span></label>
        <textarea name="address" id="address" rows="3" required placeholder="Alamat customer" class="input resize-none">{{ old('address', $customer?->address) }}</textarea>
        <x-input-error :messages="$errors->get('address')" class="mt-1.5" />
    </div>

    <div class="sm:col-span-2">
        <label for="package" class="label">Paket Internet</label>
        <input type="text" name="package" id="package" value="{{ old('package', $customer?->package) }}" placeholder="mis. Home 10Mbps" class="input">
        <x-input-error :messages="$errors->get('package')" class="mt-1.5" />
    </div>
</div>
