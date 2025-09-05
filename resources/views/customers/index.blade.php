<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Daftar Pelanggan') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <form action="{{ route('customers.index') }}" method="GET" class="w-1/3">
                            <x-text-input type="text" name="search" placeholder="Cari pelanggan..." class="w-full" value="{{ request('search') }}" />
                        </form>
                        @can('create customers')
                        <a href="{{ route('customers.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">
                            Tambah Pelanggan
                        </a>
                        @endcan
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nama</th>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Lokasi</th>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Paket</th>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Serial Number</th>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Telephone</th>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">OLT</th>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Register</th>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Vlan</th>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ODP</th>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tgl Langganan</th>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Sales</th>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tagihan</th>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Setor</th>
                                <th class="px-6 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Aksi</th>
                            </tr>
                        </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800">
                                @forelse ($customers as $customer)
                                    <tr>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">{{ $customer->name }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">{{ $customer->lokasi }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">{{ $customer->package->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">{{ $customer->serial_number }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">{{ $customer->phone }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">{{ $customer->olt->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">{{ $customer->register_port }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">{{ $customer->vlans->pluck('vlan_id')->implode(', ') }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">{{ $customer->odp }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">{{ $customer->subscription_date }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">{{ $customer->sales }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">Rp {{ number_format($customer->package->price ?? 0) }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-xs">Rp {{ number_format($customer->setor ?? 0) }}</td>
                                        <td class="px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                                            {{-- Tombol Sync RADIUS (hanya muncul jika belum terdaftar) --}}
                                            @can('edit customers') 
                                            @if (!$customer->radcheck)
                                            <form action="{{ route('customers.sync_radius', $customer->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900" title="Sinkronkan pelanggan ini ke RADIUS">
                                                    Sync Radius
                                                </button>
                                            </form>
                                            @endif

                                            {{-- Tombol Edit --}}
                                            <a href="{{ route('customers.edit', $customer->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            {{-- Tombol Hapus --}}
                                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 ml-2" onclick="return confirm('Yakin?')">Hapus</button>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="14" class="px-6 py-4 text-center">Tidak ada data pelanggan.</td>
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
