<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit IP Pool') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form method="POST" action="{{ route('ip-pools.update', $ipPool->id) }}">
                        @csrf
                        @method('PUT')
                        
                        <!-- Pilih Router -->
                        <div>
                            <x-input-label for="router_id" :value="__('Router')" />
                            <select name="router_id" id="router_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih Router --</option>
                                @foreach ($routers as $router)
                                    <option value="{{ $router->id }}" {{ old('router_id', $ipPool->router_id) == $router->id ? 'selected' : '' }}>
                                        {{ $router->name }} ({{ $router->ip_address }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('router_id')" class="mt-2" />
                        </div>

                        <!-- Nama Pool -->
                        <div class="mt-4">
                            <x-input-label for="pool_name" :value="__('Nama Pool')" />
                            <x-text-input id="pool_name" class="block mt-1 w-full" type="text" name="pool_name" :value="old('pool_name', $ipPool->pool_name)" required autofocus />
                            <x-input-error :messages="$errors->get('pool_name')" class="mt-2" />
                        </div>

                        <!-- Range IP -->
                        <div class="mt-4">
                            <x-input-label for="ranges" :value="__('Range IP')" />
                            <x-text-input id="ranges" class="block mt-1 w-full" type="text" name="ranges" :value="old('ranges', $ipPool->ranges)" required />
                            <x-input-error :messages="$errors->get('ranges')" class="mt-2" />
                        </div>
                        
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Update IP Pool') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
