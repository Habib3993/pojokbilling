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

                    {{-- PERBAIKAN: Tampilkan semua error validasi di bagian atas --}}
                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded-lg">
                            <div class="font-bold text-lg mb-2">❌ Terjadi Kesalahan:</div>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- PERBAIKAN: Tampilkan pesan sukses/error dari session --}}
                    @if (session('success'))
                        <div class="mb-6 p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-200 rounded-lg">
                            <div class="font-bold">✅ {{ session('success') }}</div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 p-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-200 rounded-lg">
                            <div class="font-bold">❌ {{ session('error') }}</div>
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="mb-6 p-4 bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-600 text-yellow-700 dark:text-yellow-200 rounded-lg">
                            <div class="font-bold">⚠️ {{ session('warning') }}</div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('customers.store') }}" id="customerForm">
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
                                    <option value="{{ $package->id }}" {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                        {{ $package->name }} - {{ $package->speed }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('package_id')" class="mt-2" />
                        </div>

                        {{-- Serial Number --}}
                        <div class="mt-4">
                            <x-input-label for="serial_number" :value="__('Serial Number')" />
                            <x-text-input id="serial_number" class="block mt-1 w-full" type="text" name="serial_number" :value="old('serial_number')" required placeholder="Contoh: ZTEGC1234567" />
                            <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
                        </div>

                        {{-- Telephone --}}
                        <div class="mt-4">
                            <x-input-label for="phone" :value="__('Telephone')" />
                            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" required placeholder="Contoh: 08123456789" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        {{-- OLT --}}
                        <div class="mt-4">
                            <x-input-label for="olt_id" :value="__('OLT')" />
                            <select name="olt_id" id="olt_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih OLT --</option>
                                @foreach ($olts as $olt)
                                    <option value="{{ $olt->id }}" {{ old('olt_id') == $olt->id ? 'selected' : '' }}>
                                        {{ $olt->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('olt_id')" class="mt-2" />
                        </div>

                        {{-- Register --}}
                        <div class="mt-4">
                            <x-input-label for="register_port" :value="__('Register (Slot/Port/ONU ID)')" />
                            <x-text-input id="register_port" class="block mt-1 w-full" type="text" name="register_port" :value="old('register_port')" required placeholder="Contoh: 1/2/8:11" />
                            <x-input-error :messages="$errors->get('register_port')" class="mt-2" />
                            <p class="text-sm text-gray-500 mt-1">
                                Format: slot/subslot/port:onu_id (contoh: 1/2/8:11)<br>
                                Untuk ZTE C320: gunakan slot 1, subslot 1-4, port 1-16, onu_id 1-128<br>
                                <span class="text-yellow-600">⚠️ Pastikan port belum digunakan (cek terminal OLT)</span>
                            </p>
                        </div>

                        {{-- Vlan --}}
                        <div class="mt-4">
                            <x-input-label for="vlan_ids" :value="__('VLAN (Bisa pilih lebih dari satu)')" />
                            <select name="vlan_ids[]" id="vlan_ids" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" multiple required>
                                @foreach ($vlans as $vlan)
                                    <option value="{{ $vlan->id }}" {{ in_array($vlan->id, old('vlan_ids', [])) ? 'selected' : '' }}>
                                        {{ $vlan->vlan_id }} - {{ $vlan->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('vlan_ids')" class="mt-2" />
                        </div>

                        {{-- ODP --}}
                        <div class="mt-4">
                            <x-input-label for="odp" :value="__('ODP')" />
                            <x-text-input id="odp" class="block mt-1 w-full" type="text" name="odp" :value="old('odp')" placeholder="Opsional" />
                        </div>

                        {{-- Tgl Langganan --}}
                        <div class="mt-4">
                            <x-input-label for="subscription_date" :value="__('Tgl Langganan')" />
                            <x-text-input id="subscription_date" class="block mt-1 w-full" type="date" name="subscription_date" :value="old('subscription_date')" style="color-scheme: dark;" />
                        </div>

                        {{-- Sales --}}
                        <div class="mt-4">
                            <x-input-label for="sales" :value="__('Sales')" />
                            <x-text-input id="sales" class="block mt-1 w-full" type="text" name="sales" :value="old('sales')" placeholder="Nama sales" />
                        </div>

                        {{-- Setor --}}
                        <div class="mt-4">
                            <x-input-label for="setor" :value="__('Setor')" />
                            <x-text-input id="setor" class="block mt-1 w-full" type="number" name="setor" :value="old('setor')" placeholder="Masukkan jumlah setor awal" />
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button id="submitBtn">
                                {{ __('Simpan & Aktivasi') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
                
                {{-- PERBAIKAN: Loading indicator --}}
                <div id="loadingIndicator" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center space-x-4">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <div class="text-gray-700 dark:text-gray-300">
                            <div class="font-semibold">Memproses...</div>
                            <div class="text-sm">Menyimpan ke database, mengkonfigurasi OLT dan MikroTik</div>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        // Initialize TomSelect for VLAN multiple select
                        new TomSelect('#vlan_ids',{
                            plugins: ['remove_button'],
                            placeholder: 'Pilih satu atau lebih VLAN...'
                        });

                        // Handle form submission
                        const form = document.getElementById('customerForm');
                        const submitBtn = document.getElementById('submitBtn');
                        const loadingIndicator = document.getElementById('loadingIndicator');

                        form.addEventListener('submit', function(e) {
                            // Show loading indicator
                            loadingIndicator.classList.remove('hidden');
                            
                            // Disable submit button
                            submitBtn.disabled = true;
                            submitBtn.textContent = 'Memproses...';
                            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        });

                        // Hide loading if there are validation errors (page reloaded)
                        @if ($errors->any())
                            loadingIndicator.classList.add('hidden');
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Simpan & Aktivasi';
                            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        @endif
                    });
                </script>
            </div>
        </div>
    </div>
</x-app-layout>
