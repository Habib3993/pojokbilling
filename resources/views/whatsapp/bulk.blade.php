<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kirim Pesan WhatsApp Massal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Kolom Form -->
            <div class="md:col-span-1">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form method="POST" action="{{ route('whatsapp.bulk.send') }}">
                            @csrf
                            <div>
                                <x-input-label for="odp_group" :value="__('Kelompok (Berdasarkan ODP)')" />
                                <select name="odp_group" id="odp_group" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">-- Pilih Grup ODP --</option>
                                    @foreach ($odpGroups as $odp)
                                        <option value="{{ $odp }}">{{ $odp }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mt-4">
                                <x-input-label for="delay" :value="__('Jeda Antar Pesan (Detik)')" />
                                <select name="delay" id="delay" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                    <option value="5">5 Detik</option>
                                    <option value="10">10 Detik</option>
                                    <option value="20">20 Detik</option>
                                </select>
                            </div>
                            <div class="mt-4">
                                <x-input-label for="message" :value="__('Pesan')" />
                                <textarea name="message" id="message" rows="8" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>{{ old('message') }}</textarea>
                            </div>
                            <div class="flex items-center justify-end mt-4">
                                <x-primary-button>Mulai Kirim Massal</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Kolom Hasil -->
            <div class="md:col-span-2">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium mb-4">Hasil Pengiriman Terakhir</h3>
                        @if (session('results'))
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telepon</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                        @foreach (session('results') as $result)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $result['name'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">{{ $result['phone'] }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($result['status'] == 'Sukses')
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sukses</span>
                                                    @else
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Gagal</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p>Belum ada proses pengiriman massal yang dijalankan.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
