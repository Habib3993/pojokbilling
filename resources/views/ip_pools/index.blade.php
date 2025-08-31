<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Daftar IP Pool') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Tombol Tambah & Form Pencarian --}}
                    <div class="flex justify-between items-center mb-4">
                        <form action="{{ route('ip-pools.index') }}" method="GET" class="w-1/3">
                            <x-text-input type="text" name="search" placeholder="Cari IP Pool..." class="w-full" value="{{ request('search') }}" />
                        </form>
                        @role('superadmin')
                        <a href="{{ route('ip-pools.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700">
                            Tambah IP Pool
                        </a>
                        @endrole
                    </div>

                    {{-- Area Impor dari Router --}}
                    @role('superadmin')
                    <div class="mb-4 border-t border-b border-gray-200 dark:border-gray-700 py-4">
                        <h3 class="text-lg font-semibold mb-2">Impor dari Router</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Klik tombol di bawah untuk mengambil data IP Pool yang ada di router dan menyimpannya ke dalam aplikasi.</p>
                        @forelse($routers as $router)
                            <div class="mb-2 flex justify-between items-center bg-gray-100 dark:bg-gray-700 p-2 rounded">
                                <span>{{ $router->name }} ({{ $router->ip_address }})</span>
                                <a href="{{ route('ip-pools.sync', $router->id) }}"
                                   class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
                                   onclick="return confirm('Yakin ingin impor pool dari {{ $router->name }}? Ini akan menambahkan pool yang belum ada di database.')">
                                    Impor dari Router
                                </a>
                            </div>
                        @empty
                            <p class="text-gray-500">Tidak ada router. Silakan tambah router terlebih dahulu untuk bisa mengimpor IP Pool.</p>
                        @endforelse
                    </div>
                    @endrole

                    {{-- Tabel Data IP Pool --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Pool</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Range IP</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Router</th>
                                    @role('superadmin')
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    @endrole
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($ipPools as $ipPool)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $ipPool->pool_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $ipPool->ranges }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $ipPool->router->name ?? 'N/A' }}</td>
                                        @role('superadmin')
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('ip-pools.edit', $ipPool->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form action="{{ route('ip-pools.destroy', $ipPool->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 ml-2" onclick="return confirm('Yakin hapus IP Pool ini?')">Hapus</button>
                                            </form>
                                        </td>
                                        @endrole
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="@role('superadmin') 4 @else 3 @endrole" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                            Belum ada data IP Pool.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Navigasi Halaman --}}
                    <div class="mt-4">
                        {{ $ipPools->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
