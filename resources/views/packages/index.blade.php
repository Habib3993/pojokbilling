<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Daftar Paket') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <div class="flex justify-between items-center mb-4">
                        <form action="{{ route('packages.index') }}" method="GET" class="w-1/3">
                            <x-text-input type="text" name="search" placeholder="Cari paket..." class="w-full" value="{{ request('search') }}" />
                        </form>
                        {{-- Area Impor dari Router hanya untuk superadmin --}}
                        @role('superadmin')
                        <a href="{{ route('packages.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">Tambah Paket</a>
                        @endrole
                    </div>
                    
                    {{-- Area Impor dari Router --}}
                    {{-- Area Impor dari Router hanya untuk superadmin --}}
                    @role('superadmin')
                    <div class="mb-4 border-t border-b border-gray-200 dark:border-gray-700 py-4">
                        <h3 class="text-lg font-semibold mb-2">Impor dari Router</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Klik tombol di bawah untuk mengambil data Paket (PPP Profiles) yang ada di router dan menyimpannya ke dalam aplikasi.</p>
                        @forelse($routers as $router)
                            <div class="mb-2 flex justify-between items-center bg-gray-100 dark:bg-gray-700 p-2 rounded">
                                <span>{{ $router->name }} ({{ $router->ip_address }})</span>
                                <a href="{{ route('packages.sync', $router->id) }}"
                                   class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
                                   onclick="return confirm('Yakin ingin impor paket dari {{ $router->name }}?')">
                                    Impor Paket dari Router
                                </a>
                            </div>
                        @empty
                            <p class="text-gray-500">Tidak ada router. Silakan tambah router terlebih dahulu.</p>
                        @endforelse
                    </div>
                    @endrole
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Paket</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kecepatan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Router</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Pool</th>
                                    @role('superadmin')
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    @endrole
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($packages as $package)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $package->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $package->speed }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">Rp {{ number_format($package->price) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $package->router->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $package->ipPool->pool_name ?? 'N/A' }}</td>
                                        @role('superadmin')
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('packages.edit', $package->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form action="{{ route('packages.destroy', $package->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 ml-2" onclick="return confirm('Yakin hapus paket ini?')">Hapus</button>
                                            </form>
                                        </td>
                                        @endrole
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="@role('superadmin') 6 @else 5 @endrole" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            Belum ada data paket.
                                        </td>
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