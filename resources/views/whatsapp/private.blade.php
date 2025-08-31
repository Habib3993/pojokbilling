<x-app-layout>
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kirim Pesan WhatsApp Pribadi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('whatsapp.private.send') }}">
                        @csrf
                        <div>
                            <x-input-label for="customer_id" :value="__('Pilih Pelanggan')" />
                            <select id="select-customer" name="customer_id" placeholder="Ketik untuk mencari pelanggan..." required>
                                <option value="">Ketik untuk mencari pelanggan...</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="message" :value="__('Pesan')" />
                            <textarea name="message" id="message" rows="5" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>{{ old('message') }}</textarea>
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <x-secondary-button type="reset" class="me-3">Batal</x-secondary-button>
                            <x-primary-button>Kirim</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        new TomSelect("#select-customer",{ create: false, sortField: { field: "text", direction: "asc" } });
    </script>
    @endpush
</x-app-layout>
