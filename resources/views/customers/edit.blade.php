<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Pelanggan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('customers.update', $customer->id) }}">
                        @csrf
                        @method('PUT')
                        
                        {{-- Nama --}}
                        <div>
                            <x-input-label for="name" :value="__('Nama')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $customer->name)" required autofocus />
                        </div>

                        {{-- Lokasi --}}
                        <div class="mt-4">
                            <x-input-label for="lokasi" :value="__('Lokasi (Koordinat)')" />
                            <x-text-input id="lokasi" class="block mt-1 w-full" type="text" name="lokasi" :value="old('lokasi', $customer->lokasi)" required />
                        </div>

                        {{-- Paket --}}
                        <div class="mt-4">
                            <x-input-label for="package_id" :value="__('Paket')" />
                            <select name="package_id" id="package_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                @foreach ($packages as $package)
                                    <option value="{{ $package->id }}" {{ old('package_id', $customer->package_id) == $package->id ? 'selected' : '' }}>
                                        {{ $package->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Serial Number --}}
                        <div class="mt-4">
                            <x-input-label for="serial_number" :value="__('Serial Number')" />
                            <x-text-input id="serial_number" class="block mt-1 w-full" type="text" name="serial_number" :value="old('serial_number', $customer->serial_number)" required />
                        </div>

                        {{-- Telephone --}}
                        <div class="mt-4">
                            <x-input-label for="phone" :value="__('Telephone')" />
                            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $customer->phone)" required />
                        </div>

                        {{-- OLT --}}
                        <div class="mt-4">
                            <x-input-label for="olt_id" :value="__('OLT')" />
                            <select name="olt_id" id="olt_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                @foreach ($olts as $olt)
                                    <option value="{{ $olt->id }}" {{ old('olt_id', $customer->olt_id) == $olt->id ? 'selected' : '' }}>
                                        {{ $olt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Register --}}
                        <div class="mt-4">
                            <x-input-label for="register_port" :value="__('Register (Slot/Port/ONU ID)')" />
                            <x-text-input id="register_port" class="block mt-1 w-full" type="text" name="register_port" :value="old('register_port', $customer->register_port)" required />
                        </div>

                        {{-- Vlan --}}
                        <div class="mt-4">
                            <x-input-label for="vlan_ids" :value="__('VLAN (Tahan Ctrl untuk pilih lebih dari satu)')" />
                            <select name="vlan_ids[]" id="vlan_ids" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" multiple required>
                                @php
                                    $selectedVlans = old('vlan_ids', $customer->vlans->pluck('id')->toArray());
                                @endphp
                                @foreach ($vlans as $vlan)
                                    <option value="{{ $vlan->id }}" {{ in_array($vlan->id, $selectedVlans) ? 'selected' : '' }}>
                                        {{ $vlan->vlan_id }} - {{ $vlan->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- ODP --}}
                        <div class="mt-4">
                            <x-input-label for="odp" :value="__('ODP')" />
                            <x-text-input id="odp" class="block mt-1 w-full" type="text" name="odp" :value="old('odp', $customer->odp)" />
                        </div>

                        {{-- Tgl Langganan --}}
                        <div class="mt-4">
                            <x-input-label for="subscription_date" :value="__('Tgl Langganan')" />
                            <x-text-input id="subscription_date" class="block mt-1 w-full" type="date" name="subscription_date" :value="old('subscription_date', $customer->subscription_date)" style="color-scheme: dark;" />
                        </div>

                        {{-- Sales --}}
                        <div class="mt-4">
                            <x-input-label for="sales" :value="__('Sales')" />
                            <x-text-input id="sales" class="block mt-1 w-full" type="text" name="sales" :value="old('sales', $customer->sales)" />
                        </div>
                        {{-- Setor --}}
                        <div class="mt-4">
                            <x-input-label for="setor" :value="__('setor')" />
                            <x-text-input id="setor" class="block mt-1 w-full" type="text" name="setor" :value="old('setor', $customer->setor)" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Update Pelanggan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
