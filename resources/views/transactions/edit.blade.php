<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Transaksi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('transactions.update', $transaction->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Tanggal -->
                        <div>
                            <x-input-label for="date" :value="__('Tanggal')" />
                            <x-text-input id="date" class="block mt-1 w-full" type="date" name="date" :value="old('date', $transaction->date)" required style="color-scheme: dark;" />
                        </div>

                        <!-- Status -->
                        <div class="mt-4">
                            <x-input-label for="status" :value="__('Status')" />
                            <select name="status" id="status" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                <option value="MODAL" @if(old('status', $transaction->status) == 'MODAL') selected @endif>MODAL</option>
                                <option value="OPERASIONAL" @if(old('status', $transaction->status) == 'OPERASIONAL') selected @endif>OPERASIONAL</option>
                                <option value="EKSPANSI" @if(old('status', $transaction->status) == 'EKSPANSI') selected @endif>EKSPANSI</option>
                            </select>
                        </div>

                        <!-- Catatan -->
                        <div class="mt-4">
                            <x-input-label for="note" :value="__('Catatan')" />
                            <x-text-input id="note" class="block mt-1 w-full" type="text" name="note" :value="old('note', $transaction->note)" required />
                        </div>

                        <!-- Jumlah (Amount) -->
                        <div class="mt-4">
                            <x-input-label for="amount" :value="__('Jumlah (Debit/Kredit)')" />
                            {{-- Tampilkan nilai debit jika ada, jika tidak tampilkan nilai kredit --}}
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" name="amount" :value="old('amount', $transaction->debit > 0 ? $transaction->debit : $transaction->kredit)" required />
                        </div>

                        <!-- Catatan Opsional -->
                        <div class="mt-4">
                            <x-input-label for="optional_note" :value="__('Catatan Opsional')" />
                            <textarea name="optional_note" id="optional_note" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm">{{ old('optional_note', $transaction->optional_note) }}</textarea>
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Update Transaksi') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
