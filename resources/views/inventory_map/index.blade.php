<x-app-layout>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
        
        <style>
            :root {
                --bg-sidebar: #1f2937;
                --text-sidebar: #d1d5db;
                --border-sidebar: #374151;
                --hover-sidebar: #374151;
                --primary-blue: #3b82f6;
                --danger-red: #dc2626;
            }
            .content-wrapper {
                height: calc(100vh - 65px - 6rem);
                display: flex;
                flex-direction: column;
            }
            .map-container {
                display: flex;
                flex-grow: 1;
                overflow: hidden;
            }
            #sidebar {
                width: 280px;
                background-color: var(--bg-sidebar);
                color: var(--text-sidebar);
                padding: 1.25rem;
                overflow-y: auto;
                border-right: 1px solid var(--border-sidebar);
            }
            #map {
                flex-grow: 1;
                z-index: 1;
            }
            .sidebar-group h4 {
                font-weight: 600;
                font-size: 1.1em;
                margin-top: 1.25rem;
                margin-bottom: 0.75rem;
                cursor: pointer;
                display: flex;
                align-items: center;
            }
            .sidebar-group:first-child h4 { margin-top: 0; }
            .sidebar-group h4 input { margin-right: 10px; }
            .sidebar-item {
                display: flex;
                align-items: center;
                padding: 8px 12px 8px 35px;
                cursor: pointer;
                border-radius: 6px;
                font-size: 0.95em;
                transition: background-color 0.2s ease, color 0.2s ease;
            }
            .sidebar-item:hover {
                background-color: var(--hover-sidebar);
                color: white;
            }
            .leaflet-popup-content-wrapper {
                background: #ffffff;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            .leaflet-popup-content {
                margin: 15px 20px;
                font-size: 14px;
            }
            .leaflet-popup-close-button {
                padding: 8px 8px 0 0;
            }
            .map-label {
                background-color: rgba(255, 255, 255, 0.85);
                border: none;
                border-radius: 4px;
                padding: 2px 6px;
                font-size: 11px;
                font-weight: 600;
                white-space: nowrap;
                box-shadow: 0 1px 3px rgba(0,0,0,0.2);
                color: #333;
            }
            .leaflet-draw-toolbar a {
                transform: none !important;
            }
        </style>
    @endpush

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Peta Inventaris Jaringan
            </h2>
            
            <div class="w-1/3">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="map-search-input" class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-300 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari nama titik...">
                </div>
            </div>
            
            <a href="{{ route('layer-groups.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                Manajemen Layer
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg content-wrapper">
                <div class="map-container">
                    <div id="sidebar">
                        <h3 class="font-bold text-lg mb-4">Layer Kontrol</h3>
                        <div id="sidebar-content"></div>
                    </div>
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <script>
        document.addEventListener('DOMContentLoaded', function () {
                
                // ===================================================================
                // INISIALISASI PETA & VARIABEL GLOBAL
                // ===================================================================
                const map = L.map('map').setView([-7.5489, 112.4480], 13);
                
                // Definisi berbagai tile layers
                const baseLayers = {
                    "Satellite": L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 19,
                        attribution: '© Esri'
                    }),
                    "OpenStreetMap": L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    }),
                    "Hybrid": L.layerGroup([
                        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                            maxZoom: 19,
                            attribution: '© Esri'
                        }),
                        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                            maxZoom: 19,
                            attribution: '© Esri'
                        })
                    ]),
                    "Terrain": L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                        maxZoom: 17,
                        attribution: '© OpenTopoMap'
                    })
                };

                // Set satellite sebagai layer default
                baseLayers["Satellite"].addTo(map);
                
                // Tambahkan layer control untuk switch antar layer
                L.control.layers(baseLayers).addTo(map);

                const permissions = @json($permissions);
                let allLayerGroups = @json($layerGroups);
                let allLocations = @json($locations);
                let allPolylines = @json($polylines);

                const drawnItems = new L.FeatureGroup().addTo(map);
                const layerReferences = {}; 
                const sidebarLayerGroups = {};

                // ===================================================================
                // BAGIAN FUNGSI-FUNGSI BANTUAN
                // ===================================================================

                // Fungsi terpusat untuk menghapus layer dari server
                function deleteLayerFromServer(type, id) {
                    const layer = layerReferences[`${type}_${id}`];
                    if (!layer) {
                        console.error("Layer to be deleted not found in references.");
                        return;
                    }

                    Swal.fire({
                        title: 'Anda yakin?',
                        text: "Data tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const url = `/inventory-map/${type}s/${id}`;
                            fetch(url, {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    // Melempar error jika response dari server bukan 2xx
                                    throw new Error('Server responded with an error!');
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    // --- INILAH LOGIKA BARU TANPA RELOAD ---

                                    // 1. Hapus layer dari semua grup di peta
                                    drawnItems.removeLayer(layer);
                                    for (const groupName in sidebarLayerGroups) {
                                        if (sidebarLayerGroups[groupName].hasLayer(layer)) {
                                            sidebarLayerGroups[groupName].removeLayer(layer);
                                        }
                                    }
                                    delete layerReferences[`${type}_${id}`];

                                    // 2. Hapus data dari variabel JavaScript lokal
                                    if (type === 'point') {
                                        removeDataFromLocalArrays(id, 'point');
                                    } else if (type === 'polyline') {
                                        removeDataFromLocalArrays(id, 'polyline');
                                    }
                                    
                                    // 3. Bangun ulang sidebar untuk update tampilan & jumlah item
                                    buildSidebar();

                                    // 4. Tampilkan notifikasi sukses
                                    Swal.fire('Dihapus!', 'Item telah berhasil dihapus.', 'success');
                                } else {
                                    Swal.fire('Gagal!', data.message || 'Gagal menghapus dari database.', 'error');
                                }
                            })
                            .catch(() => Swal.fire('Error!', 'Gagal menghubungi server.', 'error'));
                        }
                    });
                }

                // FUNGSI UNTUK MEMBANGUN SIDEBAR (DARI KODE LAMA ANDA)
                function buildSidebar() {
                    const sidebarContent = document.getElementById('sidebar-content');
                    sidebarContent.innerHTML = ''; 

                    allLayerGroups.forEach(group => {
                        const groupName = group.name;
                        if (!sidebarLayerGroups[groupName]) {
                            sidebarLayerGroups[groupName] = L.featureGroup();
                            if (map.hasLayer(sidebarLayerGroups[groupName]) == false) {
                                sidebarLayerGroups[groupName].addTo(map);
                            }
                        }

                        const groupContainer = document.createElement('div');
                        groupContainer.className = 'sidebar-group';
                        
                        const header = document.createElement('h4');
                        header.innerHTML = `<input type="checkbox" data-group-name="${groupName}" checked> ${groupName} (${group.map_points ? group.map_points.length : 0})`;
                        groupContainer.appendChild(header);

                        if(group.map_points) {
                            group.map_points.forEach(point => {
                                const itemEl = document.createElement('div');
                                itemEl.className = 'sidebar-item';
                                itemEl.innerHTML = `<span>${point.name}</span>`;
                                itemEl.onclick = () => {
                                    const layer = layerReferences[`point_${point.id}`];
                                    if (layer) {
                                        map.flyTo(layer.getLatLng(), 18);
                                        layer.openPopup();
                                    }
                                };
                                groupContainer.appendChild(itemEl);
                            });
                        }
                        sidebarContent.appendChild(groupContainer);
                    });

                    // Event listener untuk checkbox show/hide layer
                    sidebarContent.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                        checkbox.addEventListener('change', (e) => {
                            const groupName = e.target.dataset.groupName;
                            if (e.target.checked) {
                                map.addLayer(sidebarLayerGroups[groupName]);
                            } else {
                                map.removeLayer(sidebarLayerGroups[groupName]);
                            }
                        });
                    });
                }
                function openEditPopup(type, id) {
                    const layer = layerReferences[`${type}_${id}`];
                    if (!layer) return;

                    // Ambil data lama dari variabel lokal untuk ditampilkan di form
                    let currentData;
                    if (type === 'point') {
                        currentData = allLayerGroups.flatMap(g => g.map_points).find(p => p && p.id === id);
                    } else { // polyline
                        currentData = allPolylines.find(l => l && l.id === id);
                    }
                    if (!currentData) return;

                    Swal.fire({
                        title: `Edit ${type === 'point' ? 'Titik' : 'Kabel'}`,
                        html: `
                            <div style="text-align: left; padding: 1em;">
                                <label for="swal-edit-name">Nama:</label>
                                <input id="swal-edit-name" class="swal2-input" value="${currentData.name}">
                                
                                <label for="swal-edit-description" style="margin-top:1rem; display:block;">Deskripsi:</label>
                                <textarea id="swal-edit-description" class="swal2-textarea">${currentData.description || ''}</textarea>
                            </div>`,
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Simpan',
                        denyButtonText: `<i class="fa fa-trash"></i> Hapus`,
                        denyButtonColor: '#d33',
                        focusConfirm: false,
                        preConfirm: () => {
                            // Validasi form sebelum mengirim
                            const name = document.getElementById('swal-edit-name').value;
                            if (!name) {
                                Swal.showValidationMessage(`Nama tidak boleh kosong`);
                                return false;
                            }
                            return {
                                name: name,
                                description: document.getElementById('swal-edit-description').value,
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) { // Jika klik "Simpan"
                            const updatedData = result.value;
                            
                            fetch(`/inventory-map/${type}s/${id}`, {
                                method: 'PATCH', // Menggunakan PATCH untuk update parsial
                                headers: { 
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(updatedData)
                            })
                            .then(res => res.json().then(data => ({ status: res.status, body: data })))
                            .then(obj => {
                                if (obj.status >= 200 && obj.status < 300 && obj.body.success) {
                                    // --- LOGIKA BARU TANPA RELOAD ---
                                    const dataFromServer = obj.body.point || obj.body.polyline;

                                    // 1. Update data di variabel JavaScript lokal
                                    Object.assign(currentData, dataFromServer);

                                    // 2. Update konten popup di peta
                                    updatePopupContent(layer, type, currentData);
                                    
                                    // 3. Bangun ulang sidebar untuk update nama item
                                    buildSidebar();

                                    // 4. Tampilkan notifikasi sukses
                                    Swal.fire('Tersimpan!', 'Data berhasil diperbarui.', 'success');
                                } else {
                                    throw obj.body;
                                }
                            })
                            .catch(error => {
                                let errorMsg = 'Gagal memperbarui data.';
                                if (error && error.errors) { errorMsg = Object.values(error.errors).map(e => e[0]).join('<br>'); }
                                Swal.fire({ icon: 'error', title: 'Oops...', html: errorMsg });
                            });

                        } else if (result.isDenied) { // Jika klik "Hapus"
                            deleteLayerFromServer(type, id);
                        }
                    });
                }

                /**
                 * FUNGSI BANTUAN: Memperbarui konten popup setelah diedit.
                 * (Pastikan Anda juga punya fungsi ini dari revisi sebelumnya)
                 */
                function updatePopupContent(layer, type, data) {
                    const popupContent = `
                        <div style="min-width: 150px;">
                            <strong style="font-size: 1.1em;">${data.name}</strong><br>
                            <p style="margin: 5px 0;">${data.description || '<em>Tidak ada deskripsi</em>'}</p>
                            <hr style="margin: 8px 0;">
                            <button class="popup-edit-btn" data-type="${type}" data-id="${data.id}" style="width:100%; background-color:#3b82f6; color:white; border:none; padding:5px; border-radius:4px; cursor:pointer;">Edit / Hapus</button>
                        </div>
                    `;
                    layer.bindPopup(popupContent).openPopup(); // Langsung buka popup setelah update
                }

                /**
                 * FUNGSI BANTUAN: Membuat dan menambahkan titik ke peta dengan ikon & label.
                 */
                function addPointToMap(point, group) {
                    const coords = point.coordinates.split(',');

                    // 1. Membuat Ikon Dinamis
                    const iconHtml = `<i class="${group.icon || 'fa-solid fa-location-dot'}" style="font-size: 1.5rem; color: ${group.color || '#3b82f6'};"></i>`;
                    const customIcon = L.divIcon({
                        html: iconHtml,
                        className: 'custom-map-icon', // class kosong untuk styling jika perlu
                        iconSize: [24, 24],
                        iconAnchor: [12, 24],
                        popupAnchor: [0, -24]
                    });

                    const marker = L.marker([parseFloat(coords[0]), parseFloat(coords[1])], { 
                        icon: customIcon, // Gunakan ikon kustom
                        pointId: point.id, 
                        layerType: 'point',
                        groupName: group.name
                    });

                    // 2. Menambahkan Label Permanen
                    marker.bindTooltip(point.name, {
                        permanent: true,
                        direction: 'top',
                        offset: [0, -24],
                        className: 'map-label'
                    });
                    
                    // Hubungkan popup dan simpan referensi
                    updatePopupContent(marker, 'point', point);
                    drawnItems.addLayer(marker);
                    layerReferences[`point_${point.id}`] = marker;
                    
                    // 3. FIX: Menambahkan marker ke grup layer sidebar yang benar
                    if(sidebarLayerGroups[group.name]) {
                    sidebarLayerGroups[group.name].addLayer(marker);
                    }
                }

                /**
                 * FUNGSI BANTUAN: Membuat dan menambahkan garis ke peta.
                 */
                function addPolylineToMap(line) {
                    try {
                        const path = JSON.parse(line.path);
                        const polyline = L.polyline(path, { 
                            color: line.color || '#e83e58', // Warna default jika tidak ada
                            weight: 3,
                            polylineId: line.id, 
                            layerType: 'polyline'
                        });
                        
                        // Hubungkan popup dan simpan referensi
                        updatePopupContent(polyline, 'polyline', line);
                        drawnItems.addLayer(polyline);
                        layerReferences[`polyline_${line.id}`] = polyline;

                        // Menambahkan polyline ke grup 'Kabel' (jika ada)
                        if(sidebarLayerGroups['Kabel']) {
                            sidebarLayerGroups['Kabel'].addLayer(polyline);
                        }
                    } catch (e) { console.error('Gagal parsing path polyline ID:', line.id, e); }
                }



                // ===================================================================
                // BAGIAN MEMUAT DATA AWAL
                // ===================================================================
                
                // Memuat semua titik (points) ke peta
                allLayerGroups.forEach(group => {
                    // Pastikan grup layer untuk sidebar sudah dibuat
                    if (!sidebarLayerGroups[group.name]) {
                        sidebarLayerGroups[group.name] = L.featureGroup().addTo(map);
                    }
                    // Loop melalui setiap titik dalam grup
                    if (group.map_points) {
                        group.map_points.forEach(point => addPointToMap(point, group));
                    }
                });

                // Memuat semua garis dari database menggunakan fungsi bantuan
                if (allPolylines && allPolylines.length > 0) {
                    // Buat grup layer 'Kabel' khusus untuk polyline
                    sidebarLayerGroups['Kabel'] = L.featureGroup().addTo(map);
                    allPolylines.forEach(line => addPolylineToMap(line));
                }

                // Setelah semua data dimuat dan dipetakan, panggil fungsi untuk membangun sidebar
                buildSidebar();

                // ===================================================================
                // BAGIAN KONTROL PETA & EVENT HANDLERS
                // ===================================================================

                // Hanya tampilkan kontrol gambar jika user memiliki izin
                if (permissions.canCreate) {
                    const drawControl = new L.Control.Draw({
                        edit: { featureGroup: drawnItems },
                        draw: { 
                            polygon: false, 
                            circle: false, 
                            rectangle: false, 
                            circlemarker: false, 
                            marker: true, 
                            polyline: true 
                        }
                    });
                    map.addControl(drawControl);
                }

                /**
                 * EVENT HANDLER: Dipicu setelah selesai menggambar objek baru di peta.
                 */
                map.on(L.Draw.Event.CREATED, function (e) {
                    const type = e.layerType;
                    const layer = e.layer;
                    
                    // --- LOGIKA UNTUK MENAMBAH TITIK BARU ---
                    if (type === 'marker') {
                        const coords = `${layer.getLatLng().lat.toFixed(6)},${layer.getLatLng().lng.toFixed(6)}`;
                        let groupOptions = allLayerGroups.map(g => `<option value="${g.id}">${g.name}</option>`).join('');
                        let locationOptions = allLocations.map(loc => `<option value="${loc.id}">${loc.name}</option>`).join('');

                        Swal.fire({
                            title: 'Tambah Titik Baru',
                            html: `
                                <div style="text-align: left; padding: 1em;">
                                    <label for="swal-name">Nama Titik:</label>
                                    <input id="swal-name" class="swal2-input" placeholder="cth: ODP-01">
                                    
                                    <label for="swal-group">Grup Layer:</label>
                                    <select id="swal-group" class="swal2-select">${groupOptions}</select>
                                        const selectedOption = this.options[this.selectedIndex];
                                            document.getElementById('swal-color').value = selectedOption.getAttribute('data-color');
                                        ">
                                            <option value="">Pilih Grup...</option>
                                                ${groupOptions}
                                            </select>
                                    <label for="swal-location">Lokasi:</label>
                                    <select id="swal-location" class="swal2-select">${locationOptions}</select>
                                    
                                    <label for="swal-color">Warna Ikon:</label>
                                    <input type="color" id="swal-color" class="swal2-input" value="#3b82f6" style="padding: 5px; height: 40px;">

                                    <label for="swal-description">Deskripsi:</label>
                                    <textarea id="swal-description" class="swal2-textarea" placeholder="(Opsional)"></textarea>
                                </div>`,
                            focusConfirm: false,
                            preConfirm: () => {
                                const name = document.getElementById('swal-name').value;
                                if (!name) {
                                    Swal.showValidationMessage(`Nama Titik wajib diisi`);
                                    return false;
                                }
                                return {
                                    name: name,
                                    layer_group_id: document.getElementById('swal-group').value,
                                    location_id: document.getElementById('swal-location').value,
                                    description: document.getElementById('swal-description').value,
                                    coordinates: coords
                                    // 'color' akan diambil dari grup layer di backend atau saat render
                                };
                            }
                        }).then((result) => {
                            if (result.isConfirmed && result.value) {
                                fetch('{{ route("inventory.map.storePoint") }}', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                    body: JSON.stringify(result.value)
                                })
                                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                                .then(obj => {
                                    if (obj.status >= 200 && obj.status < 300 && obj.body.success) {
                                        const newPoint = obj.body.point;
                                        const group = allLayerGroups.find(g => g.id == newPoint.layer_group_id);
                                        
                                        // 1. Update data lokal & gambar di peta
                                        if (group) {
                                            group.map_points.push(newPoint);
                                            addPointToMap(newPoint, group);
                                        }
                                        
                                        // 2. Bangun ulang sidebar
                                        buildSidebar();
                                        
                                        // 3. Tampilkan notifikasi
                                        Swal.fire('Sukses!', 'Titik berhasil disimpan.', 'success');
                                    } else { throw obj.body; }
                                })
                                .catch(error => {
                                    let errorMsg = 'Terjadi kesalahan pada server.';
                                    if (error && error.errors) { errorMsg = Object.values(error.errors).map(e => e[0]).join('<br>'); }
                                    Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', html: errorMsg });
                                });
                            }
                            document.getElementById('swal-group').dispatchEvent(new Event('change'));
                        });
                    }

                    // --- LOGIKA UNTUK MENAMBAH GARIS BARU ---
                    if (type === 'polyline') {
                        const path = JSON.stringify(layer.getLatLngs());
                        Swal.fire({
                            title: 'Tambah Kabel Baru',
                            html: `
                                <div style="text-align: left; padding: 1em;">
                                    <label for="swal-name">Nama Kabel:</label>
                                    <input id="swal-name" class="swal2-input" placeholder="cth: Kabel Feeder Utama">
                                    
                                    <label for="swal-color">Warna:</label>
                                    <input type="color" id="swal-color" class="swal2-input" value="#e83e58" style="padding: 5px; height: 40px;">

                                    <label for="swal-description">Deskripsi:</label>
                                    <textarea id="swal-description" class="swal2-textarea" placeholder="(Opsional)"></textarea>
                                </div>`,
                            focusConfirm: false,
                            preConfirm: () => {
                                const name = document.getElementById('swal-name').value;
                                if (!name) {
                                    Swal.showValidationMessage(`Nama Kabel wajib diisi`);
                                    return false;
                                }
                                return {
                                    name: name,
                                    color: document.getElementById('swal-color').value,
                                    description: document.getElementById('swal-description').value,
                                    path: path
                                };
                            }
                        }).then((result) => {
                            if (result.isConfirmed && result.value) {
                                fetch('{{ route("inventory.map.storePolyline") }}', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                    body: JSON.stringify(result.value)
                                })
                                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                                .then(obj => {
                                    if (obj.status >= 200 && obj.status < 300) {
                                        const newLine = obj.body;
                                        
                                        // 1. Update data lokal & gambar di peta
                                        allPolylines.push(newLine);
                                        addPolylineToMap(newLine);
                                        
                                        // 2. Bangun ulang sidebar
                                        buildSidebar();
                                        
                                        // 3. Tampilkan notifikasi
                                        Swal.fire('Sukses!', 'Kabel berhasil disimpan.', 'success');
                                    } else { throw obj.body; }
                                })
                                .catch(error => {
                                    // (Error handling serupa dengan di atas)
                                    Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', text: 'Terjadi kesalahan pada server.' });
                                });
                            }
                        });
                    }
                });

                /**
                 * EVENT HANDLER: Dipicu saat popup terbuka untuk mengaktifkan tombol di dalamnya.
                 */
                map.on('popupopen', function (e) {
                    const popupNode = e.popup.getElement();
                    if (!popupNode) return;
                    
                    const editBtn = popupNode.querySelector('.popup-edit-btn');
                    if (editBtn) {
                        // Hapus event listener lama untuk mencegah duplikasi
                        editBtn.onclick = null; 
                        editBtn.onclick = function() {
                            // Panggil fungsi edit yang sudah kita revisi
                            openEditPopup(this.dataset.type, parseInt(this.dataset.id));
                        }
                    }
                });
                
                // ===================================================================
                // BAGIAN KONTROL PETA & EVENT HANDLERS
                // ===================================================================

                // Hanya tampilkan kontrol gambar jika user memiliki izin
                if (permissions.canCreate || permissions.canEdit || permissions.canDelete) {
                    const drawControl = new L.Control.Draw({
                        // Opsi Edit diaktifkan dan terhubung dengan layer yang ada di peta
                        edit: { 
                            featureGroup: drawnItems,
                            remove: permissions.canDelete // Hanya tampilkan tombol hapus jika diizinkan
                        },
                        // Opsi Draw (gambar baru)
                        draw: permissions.canCreate ? { 
                            polygon: false, 
                            circle: false, 
                            rectangle: false, 
                            circlemarker: false, 
                            marker: true, 
                            polyline: true 
                        } : false // Sembunyikan semua tombol draw jika tidak diizinkan
                    });
                    map.addControl(drawControl);
                }
                /**
                 * REVISI: EVENT HANDLER: Dipicu setelah selesai MENGEDIT GEOMETRI (memindahkan/mengubah bentuk).
                 */
                map.on(L.Draw.Event.EDITED, function (e) {
                    e.layers.eachLayer(function (layer) {
                        const id = layer.options.pointId || layer.options.polylineId;
                        const type = layer.options.layerType;
                        let updatedData = {};

                        if (type === 'point') {
                            const latLng = layer.getLatLng();
                            updatedData.coordinates = `${latLng.lat.toFixed(6)},${latLng.lng.toFixed(6)}`;
                        } else if (type === 'polyline') {
                            updatedData.path = JSON.stringify(layer.getLatLngs());
                        }

                        // Kirim perubahan ke server
                        fetch(`/inventory-map/${type}s/${id}`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(updatedData)
                        })
                        .then(res => res.json().then(data => ({ status: res.status, body: data })))
                        .then(obj => {
                            if (obj.status >= 200 && obj.status < 300 && obj.body.success) {
                                // Update data di variabel lokal
                                const dataFromServer = obj.body.point || obj.body.polyline;
                                // (Logika untuk update data di array lokal bisa ditambahkan di sini jika perlu)
                                Swal.fire('Sukses!', 'Posisi berhasil diperbarui.', 'success');
                            } else { throw obj.body; }
                        })
                        .catch(() => Swal.fire('Error', 'Gagal menyimpan perubahan posisi.', 'error'));
                    });
                });


                /**
                 * REVISI: EVENT HANDLER: Dipicu setelah MENGHAPUS objek dari toolbar.
                 */
                map.on(L.Draw.Event.DELETED, function (e) {
                    e.layers.eachLayer(function (layer) {
                        const id = layer.options.pointId || layer.options.polylineId;
                        const type = layer.options.layerType;
                        
                        if (id && type) {
                            // Panggil fungsi hapus yang sudah kita revisi (tanpa passing 'layer')
                            deleteLayerFromServer(type, id);
                        }
                    });
                });

                /**
                 * REVISI: EVENT HANDLER: Dipicu saat popup terbuka untuk mengaktifkan tombol di dalamnya.
                 */
                map.on('popupopen', function (e) {
                    const popupNode = e.popup.getElement();
                    if (!popupNode) return;
                    
                    const editBtn = popupNode.querySelector('.popup-edit-btn');
                    if (editBtn) {
                        // Hapus event listener lama untuk mencegah duplikasi
                        editBtn.onclick = null; 
                        editBtn.onclick = function() {
                            // Panggil fungsi edit yang sudah kita revisi
                            openEditPopup(this.dataset.type, parseInt(this.dataset.id));
                        }
                    }
                });

                /**
                 * FUNGSI BARU: Mengaktifkan Fitur Pencarian.
                 */
                const searchInput = document.getElementById('map-search-input');
                searchInput.addEventListener('keyup', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    
                    // Reset tampilan jika search box kosong
                    if (searchTerm === '') {
                        // (Logika untuk mereset highlight bisa ditambahkan di sini)
                        return;
                    }

                    // Cari item yang cocok
                    for (const key in layerReferences) {
                        const layer = layerReferences[key];
                        let layerName = '';

                        if (layer.options.layerType === 'point') {
                            const pointData = allLayerGroups.flatMap(g => g.map_points).find(p => p && p.id === layer.options.pointId);
                            if(pointData) layerName = pointData.name.toLowerCase();
                        } else {
                            const lineData = allPolylines.find(l => l.id === layer.options.polylineId);
                            if(lineData) layerName = lineData.name.toLowerCase();
                        }

                        if (layerName.includes(searchTerm)) {
                            // Jika ditemukan, terbangkan peta ke lokasi dan buka popup
                            if(layer.getLatLng) { // Untuk point
                                map.flyTo(layer.getLatLng(), 18);
                            } else if (layer.getBounds) { // Untuk polyline
                                map.flyToBounds(layer.getBounds());
                            }
                            layer.openPopup();
                            return; // Hentikan pencarian setelah menemukan yang pertama
                        }
                    }
                });
            }); // Penutup untuk DOMContentLoaded
        </script>
    @endpush
</x-app-layout>