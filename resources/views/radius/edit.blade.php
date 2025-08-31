<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Klien NAS: ') . $nas->shortname }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('radius.update', $nas->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- NAS IP Address -->
                        <div>
                            <x-input-label for="nasname" :value="__('NAS IP Address')" />
                            <x-text-input id="nasname" class="block mt-1 w-full" type="text" name="nasname" :value="old('nasname', $nas->nasname)" required autofocus />
                            <x-input-error :messages="$errors->get('nasname')" class="mt-2" />
                        </div>

                        <!-- Shortname -->
                        <div class="mt-4">
                            <x-input-label for="shortname" :value="__('Shortname (Nama Pendek)')" />
                            <x-text-input id="shortname" class="block mt-1 w-full" type="text" name="shortname" :value="old('shortname', $nas->shortname)" required />
                            <x-input-error :messages="$errors->get('shortname')" class="mt-2" />
                        </div>

                        <!-- Secret -->
                        <div class="mt-4">
                            <x-input-label for="secret" :value="__('Secret Baru (Kosongkan jika tidak ingin diubah)')" />
                            <x-text-input id="secret" class="block mt-1 w-full" type="text" name="secret" />
                            <x-input-error :messages="$errors->get('secret')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Deskripsi (Opsional)')" />
                            <x-text-input id="description" class="block mt-1 w-full" type="text" name="description" :value="old('description', $nas->description)" />
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Update') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>