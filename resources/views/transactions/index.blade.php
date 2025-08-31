<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Buku Kas Transaksi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <form action="{{ route('transactions.index') }}" method="GET" class="w-1/3">
                            <x-text-input type="text" name="search" placeholder="Cari catatan..." class="w-full" value="{{ request('search') }}" />
                        </form>
                        <div class="text-right">
                            <div class="text-lg font-bold">Saldo Akhir: Rp {{ number_format($currentBalance) }}</div>
                            @can('create transactions')
                            <a href="{{ route('transactions.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700 mt-2">
                                Tambah Transaksi
                            </a>
                            @endcan
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catatan</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Debit</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Kredit</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Saldo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catatan Opsional</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @forelse ($transactions as $transaction)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ \Carbon\Carbon::parse($transaction->date)->format('d M Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->status }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->note }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">Rp {{ number_format($transaction->debit) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">Rp {{ number_format($transaction->kredit) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right font-semibold">Rp {{ number_format($transaction->balance) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $transaction->optional_note }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            {{-- Tombol Invoice HANYA MUNCUL jika ada payment_id --}}
                                            @if($transaction->payment_id)
                                            @can('view transactions')
                                                <a href="{{ route('invoices.download', $transaction->payment_id) }}" target="_blank" class="text-green-600 hover:text-green-900">Invoice</a>
                                            @endcan
                                            @endif
                                            @can('edit transactions')
                                                <a href="{{ route('transactions.edit', $transaction->id) }}" class="text-indigo-600 hover:text-indigo-900 ml-2">Edit</a>
                                            @endcan
                                            @can('delete transactions')
                                                <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 ml-2" onclick="return confirm('Yakin hapus transaksi ini?')">Hapus</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center">Belum ada data transaksi.</td>
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
