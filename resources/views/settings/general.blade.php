<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pengaturan Umum') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border border-green-300 dark:border-green-700 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('settings.general.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- IDENTITAS APLIKASI/PERUSAHAAN -->
                        <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-3">Identitas Aplikasi</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <!-- Nama Aplikasi -->
                            <div>
                                <x-input-label for="app_name" :value="__('Nama Aplikasi/Toko')" />
                                <x-text-input id="app_name" class="block mt-1 w-full" type="text" name="app_name" :value="old('app_name', $settings['app_name'] ?? '')" />
                            </div>
                            <!-- Nomor Telepon -->
                            <div>
                                <x-input-label for="app_phone" :value="__('Nomor Telepon')" />
                                <x-text-input id="app_phone" class="block mt-1 w-full" type="text" name="app_phone" :value="old('app_phone', $settings['app_phone'] ?? '')" />
                            </div>
                            <!-- Alamat -->
                            <div class="md:col-span-2">
                                <x-input-label for="app_address" :value="__('Alamat')" />
                                <textarea id="app_address" name="app_address" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('app_address', $settings['app_address'] ?? '') }}</textarea>
                            </div>
                             <!-- Email -->
                            <div>
                                <x-input-label for="app_email" :value="__('Email')" />
                                <x-text-input id="app_email" class="block mt-1 w-full" type="email" name="app_email" :value="old('app_email', $settings['app_email'] ?? '')" />
                            </div>
                            <!-- Upload Logo -->
                            <div>
                                <x-input-label for="app_logo" :value="__('Logo Aplikasi (Kosongkan jika tidak diubah)')" />
                                <input id="app_logo" type="file" name="app_logo" class="block mt-1 w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100"/>
                                @if (!empty($settings['app_logo']))
                                    <div class="mt-4">
                                        <img src="{{ asset('storage/' . $settings['app_logo']) }}" alt="Logo saat ini" class="h-16 w-auto rounded-md bg-gray-200 p-1">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- PENGATURAN KEUANGAN & INVOICE -->
                        <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-3 mt-10">Keuangan & Invoice</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <!-- Simbol Mata Uang -->
                            <div>
                                <x-input-label for="app_currency_symbol" :value="__('Simbol Mata Uang')" />
                                <x-text-input id="app_currency_symbol" class="block mt-1 w-full" type="text" name="app_currency_symbol" :value="old('app_currency_symbol', $settings['app_currency_symbol'] ?? 'Rp')" />
                            </div>
                            <!-- Pajak (%) -->
                            <div>
                                <x-input-label for="app_tax_percentage" :value="__('Pajak PPN (%)')" />
                                <x-text-input id="app_tax_percentage" class="block mt-1 w-full" type="number" step="0.01" name="app_tax_percentage" :value="old('app_tax_percentage', $settings['app_tax_percentage'] ?? '11')" />
                            </div>
                            <!-- Prefix Invoice -->
                            <div>
                                <x-input-label for="invoice_prefix" :value="__('Prefix Nomor Invoice')" />
                                <x-text-input id="invoice_prefix" class="block mt-1 w-full" type="text" name="invoice_prefix" :value="old('invoice_prefix', $settings['invoice_prefix'] ?? 'INV/')" />
                            </div>
                            <!-- Footer Invoice -->
                            <div class="md:col-span-2">
                                <x-input-label for="invoice_footer_text" :value="__('Teks Footer Invoice')" />
                                <textarea id="invoice_footer_text" name="invoice_footer_text" rows="2" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('invoice_footer_text', $settings['invoice_footer_text'] ?? '') }}</textarea>
                            </div>
                        </div>

                        <!-- =================== BAGIAN BARU DI SINI =================== -->
                        <!-- PENGATURAN API EKSTERNAL -->
                        <h3 class="text-lg font-medium border-b border-gray-200 dark:border-gray-700 pb-3 mt-10">API Eksternal</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <!-- URL API Gateway -->
                            <div>
                                <x-input-label for="wa_gateway_url" :value="__('URL API WhatsApp Gateway')" />
                                <x-text-input id="wa_gateway_url" class="block mt-1 w-full" type="url" name="wa_gateway_url" :value="old('wa_gateway_url', $settings['wa_gateway_url'] ?? '')" placeholder="http://127.0.0.1:8000" />
                            </div>
                            <!-- API Key / Token -->
                            <div>
                                <x-input-label for="wa_gateway_key" :value="__('API Key / Token WhatsApp')" />
                                <x-text-input id="wa_gateway_key" class="block mt-1 w-full" type="password" name="wa_gateway_key" :value="old('wa_gateway_key', $settings['wa_gateway_key'] ?? '')" />
                            </div>
                            <!-- Google Maps API Key -->
                            <div class="md:col-span-2">
                                <x-input-label for="google_maps_api_key" :value="__('Google Maps API Key')" />
                                <x-text-input id="google_maps_api_key" class="block mt-1 w-full" type="text" name="google_maps_api_key" :value="old('google_maps_api_key', $settings['google_maps_api_key'] ?? '')" />
                            </div>
                        </div>
                        <!-- ========================================================== -->

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Simpan Pengaturan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
