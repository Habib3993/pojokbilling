<x-app-layout>
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Isi Ulang / Recharge Pelanggan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('recharge.store') }}">
                        @csrf
                        <input type="hidden" name="source" value="{{ request('source') }}">

                        <div>
                            <x-input-label for="customer_id" :value="__('Pilih Pelanggan')" />
                            <select id="select-customer" name="customer_id" 
                                class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 
                                    focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                                placeholder="Ketik untuk mencari pelanggan..." required>
                                <option value="">Ketik untuk mencari pelanggan...</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
                        </div>

                        {{-- ... sisa form tetap sama ... --}}
                        <div class="mt-4">
                            <x-input-label for="payment_date" :value="__('Tanggal Pembayaran')" />
                            <x-text-input id="payment_date" class="block mt-1 w-full" type="date" name="payment_date" :value="old('payment_date', date('Y-m-d'))" required style="color-scheme: dark;" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="package_id" :value="__('Paket Layanan (Otomatis)')" />
                            <select name="package_id" id="package_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required readonly>
                                <option value="">-- Pilih pelanggan terlebih dahulu --</option>
                                @foreach ($packages as $package)
                                    <option value="{{ $package->id }}" data-price="{{ $package->price }}">{{ $package->name }} - (Rp {{ number_format($package->price) }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('package_id')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                             <x-input-label for="duration_months" :value="__('Pilih Durasi Pembayaran')" />
                             <select name="duration_months" id="duration_months" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                 <option value="1">1 Bulan</option>
                                 <option value="2">2 Bulan</option>
                                 <option value="3">3 Bulan</option>
                                 <option value="6">6 Bulan</option>
                                 <option value="12">12 Bulan</option>
                             </select>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="payment_method" :value="__('Metode Pembayaran')" />
                                <select name="payment_method" id="payment_method" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                    <option value="Tunai">Tunai</option>
                                    <option value="Transfer">Transfer</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="amount" :value="__('Nominal Pembayaran (Otomatis)')" />
                                <x-text-input id="amount" class="block mt-1 w-full" type="number" name="amount" required placeholder="0" readonly/>
                                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            </div>
                        </div>
                        <div class="flex items-center justify-end mt-6">
                        @can('create transactions')
                            <x-primary-button>
                                {{ __('Simpan, Perpanjang & Cetak Nota') }}
                            </x-primary-button>
                        @endcan
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- Memuat JS untuk dropdown pencarian --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ... inisialisasi TomSelect tetap sama ...

            const customerSelect = document.getElementById('select-customer');
            const packageSelect = document.getElementById('package_id');
            const durationSelect = document.getElementById('duration_months');
            const amountInput = document.getElementById('amount');

            // PERUBAHAN: Variabel ini sekarang menyimpan harga setor
            let baseSetorPrice = 0;

            // Fungsi untuk menghitung dan update total nominal
            function updateAmount() {
                const months = parseInt(durationSelect.value) || 1;
                // PERUBAHAN: Kalkulasi berdasarkan harga setor
                const totalAmount = baseSetorPrice * months;
                amountInput.value = totalAmount;
            }

            // Event listener saat pelanggan dipilih
            customerSelect.addEventListener('change', function() {
                const customerId = this.value;
                if (!customerId) {
                    packageSelect.value = '';
                    amountInput.value = '';
                    baseSetorPrice = 0; // Reset harga setor
                    return;
                }

                // Ambil data paket & HARGA SETOR dari server
                fetch(`/recharge/customer/${customerId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Set package & HARGA SETOR
                        packageSelect.value = data.package_id;
                        baseSetorPrice = data.customer_setor; // <-- PERUBAHAN UTAMA
                        
                        // Langsung panggil fungsi update
                        updateAmount();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Gagal mengambil data pelanggan.');
                        packageSelect.value = '';
                        amountInput.value = '';
                        baseSetorPrice = 0;
                    });
            });

            // Event listener saat durasi diubah
            durationSelect.addEventListener('change', updateAmount);
        });
    </script>
    @endpush
</x-app-layout>
