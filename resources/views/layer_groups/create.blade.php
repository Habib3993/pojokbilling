<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
        {{-- (kode style Anda yang lain) --}}
    @endpush
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Grup Layer Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    
                    <form action="{{ route('layer-groups.store') }}" method="POST">
                        @csrf

                        <div class="space-y-6">
                            <div>
                                <x-input-label for="name" :value="__('Nama Grup')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="color" :value="__('Warna Grup')" />
                                <x-text-input id="color" name="color" type="color" class="mt-1 block w-full h-10" :value="old('color', '#3b82f6')" />
                                <x-input-error :messages="$errors->get('color')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="icon" value="Ikon Grup" />
                                <div class="mt-1 flex items-center gap-x-4">
                                    <div id="icon-preview" class="flex h-12 w-12 items-center justify-center rounded-lg border bg-gray-100 dark:bg-gray-700">
                                        <i id="icon-display" class="{{ old('icon', 'fa-solid fa-location-dot') }} fa-2x text-gray-700 dark:text-gray-200"></i>
                                    </div>
                                    <x-secondary-button type="button" id="pick-icon-btn">
                                        Pilih Ikon
                                    </x-secondary-button>
                                </div>
                                <input id="icon-input" name="icon" type="hidden" value="{{ old('icon', 'fa-solid fa-location-dot') }}">
                                <x-input-error :messages="$errors->get('icon')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <a href="{{ route('layer-groups.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ __('Batal') }}
                            </a>
                            <x-primary-button class="ms-4">
                                {{ __('Simpan') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            // Ambil daftar ikon dari variabel PHP yang dikirim Controller
            const iconList = @json($availableIcons ?? []);
            const pickIconButton = document.getElementById('pick-icon-btn');
            const iconInputElement = document.getElementById('icon-input');
            const iconDisplayElement = document.getElementById('icon-display');

            if (pickIconButton) {
                pickIconButton.addEventListener('click', function() {
                    // Buat HTML untuk grid ikon di dalam popup
                    let iconsHtml = '<div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; max-height: 40vh; overflow-y: auto;">';
                    iconList.forEach(iconClass => {
                        iconsHtml += `<div onclick="selectIcon('${iconClass}')" style="cursor: pointer; padding: 15px; border: 1px solid #ddd; border-radius: 5px;" class="hover:bg-gray-200 dark:hover:bg-gray-600">
                                        <i class="${iconClass} fa-2x"></i>
                                     </div>`;
                    });
                    iconsHtml += '</div>';
                    
                    // Tampilkan SweetAlert dengan pilihan ikon
                    Swal.fire({
                        title: 'Pilih Ikon',
                        html: iconsHtml,
                        showConfirmButton: false,
                        width: '450px'
                    });
                });
            }

            // Fungsi ini harus global agar bisa dipanggil dari HTML di dalam SweetAlert
            window.selectIcon = function(iconClass) {
                iconInputElement.value = iconClass;
                iconDisplayElement.className = iconClass + ' fa-2x text-gray-700 dark:text-gray-200';
                Swal.close();
            }
        </script>
    @endpush
</x-app-layout>