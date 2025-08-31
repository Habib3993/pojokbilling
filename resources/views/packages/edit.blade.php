<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Paket') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('packages.update', $package->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- REVISI: Tambahkan Dropdown Router -->
                        <div>
                            <x-input-label for="router_id" :value="__('Router')" />
                            <select name="router_id" id="router_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih Router --</option>
                                @foreach ($routers as $router)
                                    <option value="{{ $router->id }}" {{ old('router_id', $package->router_id) == $router->id ? 'selected' : '' }}>
                                        {{ $router->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- REVISI: Tambahkan Dropdown IP Pool -->
                        <div class="mt-4">
                            <x-input-label for="ip_pool_id" :value="__('IP Pool')" />
                            <select name="ip_pool_id" id="ip_pool_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih IP Pool --</option>
                                @foreach ($ipPools as $ipPool)
                                    <option value="{{ $ipPool->id }}" {{ old('ip_pool_id', $package->ip_pool_id) == $ipPool->id ? 'selected' : '' }}>
                                        {{ $ipPool->pool_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Nama Paket -->
                        <div class="mt-4">
                            <x-input-label for="name" :value="__('Nama Paket')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $package->name)" required autofocus />
                        </div>

                        <!-- Kecepatan -->
                        <div class="mt-4">
                            <x-input-label for="speed" :value="__('Kecepatan')" />
                            <x-text-input id="speed" class="block mt-1 w-full" type="text" name="speed" :value="old('speed', $package->speed)" required />
                        </div>

                        <!-- Harga -->
                        <div class="mt-4">
                            <x-input-label for="price" :value="__('Harga')" />
                            <x-text-input id="price" class="block mt-1 w-full" type="number" name="price" :value="old('price', $package->price)" required />
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Update Paket') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
