<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Peta Jaringan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div id="map" style="height: 75vh; width: 100%;" class="rounded-md"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
    const map = L.map('map').setView([-7.5489, 112.4480], 13);

    // Definisi berbagai tile layers dengan maxZoom tinggi
    const baseLayers = {
        "Satellite HD": L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 22,
            attribution: '© Google Satellite'
        }),
        "Satellite (Esri)": L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 20,
            attribution: '© Esri'
        }),
        "OpenStreetMap": L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }),
        "Hybrid (Google)": L.layerGroup([
            L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                maxZoom: 22,
                attribution: '© Google'
            }),
            L.tileLayer('https://mt1.google.com/vt/lyrs=h&x={x}&y={y}&z={z}', {
                maxZoom: 22,
                attribution: '© Google'
            })
        ]),
        "Terrain": L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            maxZoom: 17,
            attribution: '© OpenTopoMap'
        })
    };

    // Set Google Satellite HD sebagai layer default
    baseLayers["Satellite HD"].addTo(map);

    // Tambahkan layer control untuk switch antar layer
    L.control.layers(baseLayers).addTo(map);

    const customerIcon = L.icon({
        iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    // UBAH INI: Gunakan featureGroup agar bisa mendapatkan bounds
    const customerLayer = L.featureGroup().addTo(map); 
    
    // --- Proses Data Pelanggan ---
    const customers = @json($customers);

    // DEBUG 1: Tampilkan semua data pelanggan yang diterima dari PHP
    console.log('--- Data Pelanggan dari Server ---', customers);

    customers.forEach(customer => {
        if (customer.lat && customer.lng) {
            
            // DEBUG 2: Tampilkan data per pelanggan sebelum diproses
            console.log(`Memproses Pelanggan: ${customer.name}, Lat_String: "${customer.lat}", Lng_String: "${customer.lng}"`);

            const lat = parseFloat(customer.lat);
            const lng = parseFloat(customer.lng);

            // DEBUG 3: Tampilkan hasil setelah diubah menjadi angka
            console.log(`   -> Hasil parseFloat: Lat: ${lat}, Lng: ${lng}`);

            if (!isNaN(lat) && !isNaN(lng)) {
                L.marker([lat, lng], {icon: customerIcon}).addTo(customerLayer)
                    .bindPopup(`<b>Pelanggan: ${customer.name}</b>`);
                console.log(`   -> SUKSES: Marker untuk ${customer.name} ditambahkan.`);
            } else {
                console.error(`   -> GAGAL: Koordinat untuk ${customer.name} tidak valid.`);
            }
        }
    });

    // DEBUG 4: Cek berapa banyak marker yang berhasil ditambahkan
    console.log('--- Hasil Akhir ---');
    console.log('Total marker pelanggan yang berhasil ditambahkan:', customerLayer.getLayers().length);

    // Fokus ke layer pelanggan HANYA JIKA ada marker di dalamnya
    if (customerLayer.getLayers().length > 0) {
        map.fitBounds(customerLayer.getBounds());
        console.log('Fokus peta (fitBounds) berhasil dijalankan.');
    } else {
        console.warn('Tidak ada marker pelanggan, fitBounds tidak dijalankan.');
    }
</script>
    @endpush
</x-app-layout>
