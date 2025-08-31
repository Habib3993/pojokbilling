<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Transaksi Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('transactions.store') }}">
                        @csrf
                        
                        <!-- Tanggal -->
                        <div>
                            <x-input-label for="date" :value="__('Tanggal')" />
                            {{-- REVISI: Tambahkan style="color-scheme: dark;" untuk memperbaiki ikon kalender --}}
                            <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="old('date', date('Y-m-d'))" required style="color-scheme: dark;" />
                        </div>

                        <!-- Status -->
                        <div class="mt-4">
                            <x-input-label for="status" :value="__('Status')" />
                            <select name="status" id="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="MODAL" @if(old('status') == 'MODAL') selected @endif>MODAL</option>
                                <option value="OPERASIONAL" @if(old('status') == 'OPERASIONAL') selected @endif>OPERASIONAL</option>
                                <option value="EKSPANSI" @if(old('status') == 'EKSPANSI') selected @endif>EKSPANSI</option>
                            </select>
                        </div>

                        <!-- Catatan -->
                        <div class="mt-4">
                            <x-input-label for="note" :value="__('Catatan')" />
                            <x-text-input id="note" class="block mt-1 w-full" type="text" name="note" :value="old('note')" required />
                        </div>

                        <!-- Jumlah (Amount) -->
                        <div class="mt-4">
                            <x-input-label for="amount" :value="__('Jumlah (Debit/Kredit)')" />
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" name="amount" :value="old('amount')" required placeholder="Masukkan hanya angka, cth: 500000" />
                        </div>

                        <!-- Catatan Opsional -->
                        <div class="mt-4">
                            <x-input-label for="optional_note" :value="__('Catatan Opsional')" />
                            <textarea name="optional_note" id="optional_note" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('optional_note') }}</textarea>
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Simpan Transaksi') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
