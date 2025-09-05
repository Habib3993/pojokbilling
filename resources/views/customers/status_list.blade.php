<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __($title) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- REVISI: Tambahkan Form Pencarian dan Tombol Sinkronisasi -->
                    <div class="flex justify-between items-center mb-4">
                        <form action="{{ url()->current() }}" method="GET" class="w-1/3">
                            <x-text-input type="text" name="search" placeholder="Cari nama pengguna..." class="w-full" value="{{ request('search') }}" />
                            <x-text-input type="text" name="sales" placeholder="Cari berdasarkan sales..." class="w-full" value="{{ request('sales') }}" />
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                                Filter
                            </button>
                        </form>
                        @can('edit customers')
                        <form action="{{ route('customers.sync') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-gray-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                                    onclick="return confirm('Anda yakin ingin menyinkronkan semua pelanggan di daftar ini dengan MikroTik?')">
                                Sinkronisasi
                            </button>
                        </form>
                        @endcan
                    </div>

                    <!-- REVISI: Tambahkan area untuk menampilkan hasil sinkronisasi -->
                    @if (session('sync_results'))
                        <div class="mb-4 p-4 bg-blue-100 dark:bg-blue-900 border border-blue-400 dark:border-blue-600 rounded">
                            <h4 class="font-bold">Hasil Sinkronisasi:</h4>
                            <ul class="list-disc list-inside text-sm">
                                @foreach (session('sync_results') as $result)
                                    <li>{{ $result }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nama Pengguna</th>
                                    <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Paket</th>
                                    <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Dibuat</th>
                                    <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Kedaluwarsa</th>
                                    <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @forelse ($customers as $customer)
                                    <tr>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">{{ $customer->name }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">{{ $customer->package->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">{{ $customer->latestPayment ? \Carbon\Carbon::parse($customer->latestPayment->payment_date)->format('d M Y') : 'Belum ada' }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">{{ $customer->active_until ? \Carbon\Carbon::parse($customer->active_until)->format('d M Y') : 'N/A' }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                                        <td class="px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                                            {{-- Tombol Kirim Pesan WhatsApp --}}
                                            @can('send whatsapp messages')
                                            <a href="{{ route('whatsapp.private.create', ['customer_id' => $customer->id]) }}" class="inline-flex items-center text-gray-500 hover:text-gray-700" title="Kirim Pesan">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z" /><path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h7l3 3v-3h1a2 2 0 002-2V9a2 2 0 00-2-2h-1z" /></svg>
                                            </a>

                                            {{-- Tombol "Bayar" yang BARU dan DINAMIS --}}
                                            <a href="{{ route('recharge.create', ['customer_id' => $customer->id, 'source' => $status]) }}"
                                            class="inline-flex items-center ml-4 px-3 py-1 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-700">
                                                Bayar
                                            </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center">Tidak ada data pelanggan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
