<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Pelanggan Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('customers.store') }}">
                        @csrf
                        
                        {{-- Nama --}}
                        <div>
                            <x-input-label for="name" :value="__('Nama')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        {{-- Lokasi --}}
                        <div class="mt-4">
                            <x-input-label for="lokasi" :value="__('Lokasi (Koordinat)')" />
                            <x-text-input id="lokasi" class="block mt-1 w-full" type="text" name="lokasi" :value="old('lokasi')" required placeholder="-7.xxxx, 112.xxxx" />
                            <x-input-error :messages="$errors->get('lokasi')" class="mt-2" />
                        </div>

                        {{-- Paket --}}
                        <div class="mt-4">
                            <x-input-label for="package_id" :value="__('Paket')" />
                            <select name="package_id" id="package_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih Paket --</option>
                                @foreach ($packages as $package)
                                    <option value="{{ $package->id }}">{{ $package->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('package_id')" class="mt-2" />
                        </div>

                        {{-- Serial Number --}}
                        <div class="mt-4">
                            <x-input-label for="serial_number" :value="__('Serial Number')" />
                            <x-text-input id="serial_number" class="block mt-1 w-full" type="text" name="serial_number" :value="old('serial_number')" required />
                            <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
                        </div>

                        {{-- Telephone --}}
                        <div class="mt-4">
                            <x-input-label for="phone" :value="__('Telephone')" />
                            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" required />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        {{-- OLT --}}
                        <div class="mt-4">
                            <x-input-label for="olt_id" :value="__('OLT')" />
                            <select name="olt_id" id="olt_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih OLT --</option>
                                @foreach ($olts as $olt)
                                    <option value="{{ $olt->id }}">{{ $olt->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('olt_id')" class="mt-2" />
                        </div>

                        {{-- Register --}}
                        <div class="mt-4">
                            <x-input-label for="register_port" :value="__('Register (Slot/Port/ONU ID)')" />
                            <x-text-input id="register_port" class="block mt-1 w-full" type="text" name="register_port" :value="old('register_port')" required placeholder="Contoh: 1/1/1:19" />
                            <x-input-error :messages="$errors->get('register_port')" class="mt-2" />
                        </div>

                        {{-- Vlan --}}
                        <div class="mt-4">
                            <x-input-label for="vlan_ids" :value="__('VLAN (Bisa pilih lebih dari satu)')" />
                            <select name="vlan_ids[]" id="vlan_ids" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" multiple required>
                                @foreach ($vlans as $vlan)
                                    <option value="{{ $vlan->id }}">{{ $vlan->vlan_id }} - {{ $vlan->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('vlan_ids')" class="mt-2" />
                        </div>

                        {{-- ODP --}}
                        <div class="mt-4">
                            <x-input-label for="odp" :value="__('ODP')" />
                            <x-text-input id="odp" class="block mt-1 w-full" type="text" name="odp" :value="old('odp')" />
                        </div>

                        {{-- Tgl Langganan --}}
                        <div class="mt-4">
                            <x-input-label for="subscription_date" :value="__('Tgl Langganan')" />
                            <x-text-input id="subscription_date" class="block mt-1 w-full" type="date" name="subscription_date" :value="old('subscription_date')" style="color-scheme: dark;" />
                        </div>

                        {{-- Sales --}}
                        <div class="mt-4">
                            <x-input-label for="sales" :value="__('Sales')" />
                            <x-text-input id="sales" class="block mt-1 w-full" type="text" name="sales" :value="old('sales')" />
                        </div>
                        {{-- Setor --}}
                        <div class="mt-4">
                            <x-input-label for="setor" :value="__('Setor')" />
                            <x-text-input id="setor" class="block mt-1 w-full" type="number" name="setor" :value="old('setor')" placeholder="Masukkan jumlah setor awal" />
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Simpan & Aktivasi') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        new TomSelect('#vlan_ids',{
                            plugins: ['remove_button'],
                            placeholder: 'Pilih satu atau lebih VLAN...'
                        });
                    });
                </script>
            </div>
        </div>
    </div>
</x-app-layout>
