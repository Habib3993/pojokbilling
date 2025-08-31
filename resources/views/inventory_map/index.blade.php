<x-app-layout>
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
    <style>
        .map-container { display: flex; height: 80vh; }
        #sidebar { width: 250px; background-color: #1f2937; color: #d1d5db; padding: 15px; overflow-y: auto; border-right: 1px solid #374151; }
        #map { flex-grow: 1; }
        .sidebar-group h4 { font-weight: bold; margin-top: 10px; margin-bottom: 5px; cursor: pointer; display: flex; align-items: center; }
        .sidebar-group h4 input { margin-right: 8px; }
        .sidebar-item { display: flex; align-items: center; padding: 5px 5px 5px 30px; cursor: pointer; border-radius: 4px; font-size: 0.9em; }
        .sidebar-item:hover { background-color: #374151; }
        .sidebar-item img { width: 16px; height: 16px; margin-right: 8px; }
        .leaflet-popup-content-wrapper { border-radius: 8px; }
        .popup-form-label { font-weight: bold; margin-bottom: 2px; display: block; font-size: 0.9em; }
        .popup-form-input, .popup-form-textarea, .popup-form-select { width: 100%; padding: 6px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 8px; }
        .popup-form-button { padding: 6px 10px; border: none; border-radius: 4px; cursor: pointer; }
        .popup-save-btn { background-color: #2563eb; color: white; }
        .popup-delete-btn { background-color: #dc2626; color: white; margin-left: 5px; }
    </style>
    @endpush

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Peta Inventaris Jaringan') }}
        </h2>
    </x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Peta Inventaris Jaringan') }}
            </h2>
            
            {{-- TOMBOL TAMBAHAN DI SINI --}}
            <a href="{{ route('layer-groups.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 disabled:opacity-25 transition">
                Layer
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="map-container">
                    <div id="sidebar">
                        <h3>Layer Kontrol</h3>
                        <div id="sidebar-content"></div>
                    </div>
                    <div id="map" class="rounded-md"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://unpkg.com/leaflet-geometryutil"></script>
        {{-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCB6ZGeR04MT9Jb_MV4sKIrJ6zkncqdOWg"></script> --}}
        {{-- <script src="https://unpkg.com/leaflet.gridlayer.googlemutant@0.10.0/dist/Leaflet.GoogleMutant.js"></script> --}}
        
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                
                const map = L.map('map').setView([-7.5489, 112.4480], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(map);
                const permissions = @json($permissions);
                const allLayerGroups = @json($layerGroups);
                const allLocations = @json($locations);
                const allPolylines = @json($polylines);

                // Objek untuk menyimpan semua layer yang digambar di peta
                const drawnItems = new L.FeatureGroup().addTo(map);
                // Objek untuk menyimpan referensi ke setiap layer berdasarkan ID unik
                const layerReferences = {}; 
                // Objek untuk layer group di sidebar
                const sidebarLayerGroups = {};

                // ===================================================================
                // BAGIAN FUNGSI-FUNGSI BANTUAN
                // ===================================================================

                // Fungsi terpusat untuk menghapus layer dari server
                function deleteLayerFromServer(type, id, layer) {
                    Swal.fire({
                        title: 'Anda yakin?',
                        text: "Data tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, hapus!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const url = `/inventory-map/${type}s/${id}`;
                            fetch(url, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    drawnItems.removeLayer(layer);
                                    delete layerReferences[`${type}_${id}`];
                                    Swal.fire('Dihapus!', 'Item telah dihapus.', 'success').then(() => location.reload()); // Reload untuk update sidebar
                                } else {
                                    Swal.fire('Gagal!', 'Gagal menghapus dari database.', 'error');
                                }
                            })
                            .catch(() => Swal.fire('Error!', 'Gagal menghubungi server.', 'error'));
                        }
                    });
                }

                // FUNGSI UNTUK MEMBANGUN SIDEBAR (DARI KODE LAMA ANDA)
                function buildSidebar() {
                    const sidebarContent = document.getElementById('sidebar-content');
                    sidebarContent.innerHTML = ''; // Kosongkan dulu

                    // Buat grup untuk titik
                    allLayerGroups.forEach(group => {
                        const groupName = group.name;
                        if (!sidebarLayerGroups[groupName]) {
                            sidebarLayerGroups[groupName] = L.featureGroup().addTo(map);
                        }

                        const groupContainer = document.createElement('div');
                        groupContainer.className = 'sidebar-group';
                        groupContainer.innerHTML = `<h4><input type="checkbox" data-group-name="${groupName}" checked> ${groupName} (${group.map_points ? group.map_points.length : 0})</h4>`;
                        
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

                    // Buat grup untuk kabel
                    if (allPolylines.length > 0) {
                        if (!sidebarLayerGroups['Kabel']) {
                            sidebarLayerGroups['Kabel'] = L.featureGroup().addTo(map);
                        }
                        const groupContainer = document.createElement('div');
                        groupContainer.className = 'sidebar-group';
                        groupContainer.innerHTML = `<h4><input type="checkbox" data-group-name="Kabel" checked> Kabel (${allPolylines.length})</h4>`;
                        sidebarContent.appendChild(groupContainer);
                    }

                    // Tambahkan event listener untuk checkbox
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

                    // Ambil data lama untuk ditampilkan di form
                    let name = '', description = '';
                    if (type === 'point') {
                        const pointData = allLayerGroups.flatMap(g => g.map_points).find(p => p && p.id === id);
                        if (pointData) {
                            name = pointData.name;
                            description = pointData.description || '';
                        }
                    } else { // polyline
                        const lineData = allPolylines.find(l => l.id === id);
                        if (lineData) {
                            name = lineData.name;
                            description = lineData.description || '';
                        }
                    }

                    Swal.fire({
                        title: `Edit ${type === 'point' ? 'Titik' : 'Kabel'}`,
                        html: `
                            <div style="text-align: left;">
                                <label for="swal-edit-name" class="swal2-label">Nama:</label>
                                <input id="swal-edit-name" class="swal2-input" value="${name}">
                                <label for="swal-edit-description" class="swal2-label" style="margin-top:1rem;">Deskripsi:</label>
                                <textarea id="swal-edit-description" class="swal2-textarea">${description}</textarea>
                            </div>
                        `,
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Simpan Perubahan',
                        denyButtonText: `<i class="fa fa-trash"></i> Hapus`,
                        denyButtonColor: '#d33',
                    }).then((result) => {
                        if (result.isConfirmed) { // Jika klik "Simpan"
                            const updatedData = {
                                name: document.getElementById('swal-edit-name').value,
                                description: document.getElementById('swal-edit-description').value,
                                // Kita hanya mengirim data yang bisa diubah di form ini
                            };
                            
                            fetch(`/inventory-map/${type}s/${id}`, {
                                method: 'PATCH',
                                headers: { 
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(updatedData)
                            })
                            .then(res => res.json())
                            .then(() => Swal.fire('Tersimpan!', 'Data berhasil diperbarui.', 'success').then(() => location.reload()));

                        } else if (result.isDenied) { // Jika klik "Hapus"
                            deleteLayerFromServer(type, id, layer);
                        }
                    });
                }


                // ===================================================================
                // BAGIAN MEMUAT DATA AWAL
                // ===================================================================
                
                // Memuat semua titik (points) ke peta
                allLayerGroups.forEach(group => {
                    if(group.map_points) {
                        group.map_points.forEach(point => {
                            const coords = point.coordinates.split(',');
                            const marker = L.marker([parseFloat(coords[0]), parseFloat(coords[1])], { 
                                pointId: point.id, layerType: 'point' 
                            });
                            let editButtonHtml = '';
                            if (permissions.canEdit || permissions.canDelete) {
                                editButtonHtml = `<hr style="margin: 8px 0;">
                                <button class="popup-edit-btn" data-type="point" data-id="${point.id}" style="width:100%; background-color:#3b82f6; color:white; border:none; padding:5px; border-radius:4px; cursor:pointer;">Edit / Hapus</button>`;
                            }
                            const popupContent = `
                                <div style="min-width: 150px;">
                                    <strong style="font-size: 1.1em;">${point.name}</strong><br>
                                    <p style="margin: 5px 0;">${point.description || '<em>Tidak ada deskripsi</em>'}</p>
                                    <hr style="margin: 8px 0;">
                                    <button class="popup-edit-btn" data-type="point" data-id="${point.id}" style="width:100%; background-color:#3b82f6; color:white; border:none; padding:5px; border-radius:4px; cursor:pointer;">Edit / Hapus</button>
                                </div>
                            `;
                            marker.bindPopup(popupContent);
                            drawnItems.addLayer(marker);
                            layerReferences[`point_${point.id}`] = marker;
                        });
                    }
                });

                // Memuat semua garis (polylines) ke peta
                allPolylines.forEach(line => {
                    try {
                        const path = JSON.parse(line.path);
                        const polyline = L.polyline(path, { 
                            color: line.color, polylineId: line.id, layerType: 'polyline'
                        });
                        let editButtonHtml = '';
                        if (permissions.canEdit || permissions.canDelete) {
                            editButtonHtml = `<hr style="margin: 8px 0;">
                            <button class="popup-edit-btn" data-type="polyline" data-id="${line.id}" style="width:100%; background-color:#3b82f6; color:white; border:none; padding:5px; border-radius:4px; cursor:pointer;">Edit / Hapus</button>`;
                        }
                        const popupContent = `
                            <div style="min-width: 150px;">
                                <strong style="font-size: 1.1em;">${line.name}</strong><br>
                                <p style="margin: 5px 0;">${line.description || '<em>Tidak ada deskripsi</em>'}</p>
                                <hr style="margin: 8px 0;">
                                <button class="popup-edit-btn" data-type="polyline" data-id="${line.id}" style="width:100%; background-color:#3b82f6; color:white; border:none; padding:5px; border-radius:4px; cursor:pointer;">Edit / Hapus</button>
                            </div>
                        `;
                        polyline.bindPopup(popupContent);
                        drawnItems.addLayer(polyline);
                        layerReferences[`polyline_${line.id}`] = polyline;
                    } catch (e) { console.error('Gagal parsing path polyline ID:', line.id, e); }
                });

                // Panggil fungsi untuk membangun sidebar setelah semua data dimuat
                buildSidebar();

                // ===================================================================
                // BAGIAN KONTROL PETA (DRAW, EDIT, DELETE)
                // ===================================================================
                if (permissions.canCreate) {
                    const drawControl = new L.Control.Draw({
                        edit: { featureGroup: drawnItems },
                        draw: { polygon: false, circle: false, rectangle: false, circlemarker: false, marker: true, polyline: true }
                    });
                    map.addControl(drawControl);
                }
                // EVENT HANDLER UNTUK MENYIMPAN (DARI KODE LAMA ANDA)
                map.on(L.Draw.Event.CREATED, function (e) {
                    const type = e.layerType;
                    const layer = e.layer;
                    
                    // Siapkan options untuk dropdown
                    let groupOptions = allLayerGroups.map(g => `<option value="${g.id}">${g.name}</option>`).join('');
                    let locationOptions = allLocations.map(loc => `<option value="${loc.id}">${loc.name}</option>`).join('');

                    if (type === 'marker') {
                        const coords = `${layer.getLatLng().lat.toFixed(6)},${layer.getLatLng().lng.toFixed(6)}`;
                        Swal.fire({
                            title: 'Tambah Titik Baru',
                            // --- PERBAIKAN STRUKTUR HTML DI SINI ---
                            html: `
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem; padding: 1rem; max-width: 250px; margin: 10px;">
                                    <div style="width: 100%; text-align: left;">
                                        <label for="swal-name" class="swal2-label">Nama Titik:</label>
                                        <input id="swal-name" class="swal2-input" placeholder="cth: ODP-01" style="width: 100%; box-sizing: border-box;">
                                    </div>
                                    <div style="width: 100%; text-align: left;">
                                        <label for="swal-group" class="swal2-label">Grup Layer:</label>
                                        <select id="swal-group" class="swal2-select" style="width: 100%; box-sizing: border-box;">
                                            ${groupOptions}
                                        </select>
                                    </div>
                                    <div style="width: 100%; text-align: left;">
                                        <label for="swal-location" class="swal2-label">Lokasi:</label>
                                        <select id="swal-location" class="swal2-select" style="width: 100%; box-sizing: border-box;">
                                            ${locationOptions}
                                        </select>
                                    </div>
                                    <div style="width: 100%; text-align: left;">
                                        <label for="swal-description" class="swal2-label">Deskripsi:</label>
                                        <textarea id="swal-description" class="swal2-textarea" placeholder="(Opsional)" style="width: 100%; height: 80px; box-sizing: border-box;"></textarea>
                                    </div>
                                </div>
                            `,
                            width: '30%',
                            focusConfirm: false,
                            preConfirm: () => {
                                const name = document.getElementById('swal-name').value;
                                const layer_group_id = document.getElementById('swal-group').value;

                                if (!name || !layer_group_id) {
                                    Swal.showValidationMessage(`Nama Titik dan Grup Layer wajib diisi`);
                                    return false;
                                }

                                return {
                                    name: name,
                                    layer_group_id: layer_group_id,
                                    location_id: document.getElementById('swal-location').value,
                                    description: document.getElementById('swal-description').value,
                                    coordinates: coords,
                                    color: 'blue'
                                };
                            }
                        }).then((result) => {
                            if (result.isConfirmed && result.value) {
                                fetch('{{ route("inventory.map.storePoint") }}', {
                                    method: 'POST',
                                    headers: { 
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify(result.value)
                                })
                                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                                .then(obj => {
                                    if (obj.status === 200 && obj.body.success) {
                                        Swal.fire('Sukses!', 'Titik berhasil disimpan.', 'success').then(() => location.reload());
                                    } else {
                                        throw obj.body;
                                    }
                                })
                                .catch(error => {
                                    let errorMsg = 'Terjadi kesalahan pada server.';
                                    if (error && error.errors) {
                                        errorMsg = Object.values(error.errors).map(e => e[0]).join('<br>');
                                    }
                                    Swal.fire({ icon: 'error', title: 'Gagal Menyimpan', html: errorMsg });
                                });
                            }
                        });
                    }
                    // ... (kode untuk 'polyline' Anda tetap sama, tidak perlu diubah) ...
                    if (type === 'polyline') {
                        const path = JSON.stringify(layer.getLatLngs());
                        Swal.fire({
                            title: 'Tambah Kabel Baru',
                            html: `<input id="swal-name" class="swal2-input" placeholder="Nama Kabel">
                                <input id="swal-color" class="swal2-input" placeholder="Warna (cth: red, #ff0000)">
                                <textarea id="swal-description" class="swal2-textarea" placeholder="Deskripsi"></textarea>`,
                            focusConfirm: false,
                            preConfirm: () => {
                                const name = document.getElementById('swal-name').value;
                                const color = document.getElementById('swal-color').value;

                                if (!name || !color) {
                                    Swal.showValidationMessage('Nama dan Warna wajib diisi');
                                    return false;
                                }

                                return {
                                    name: name,
                                    color: color,
                                    description: document.getElementById('swal-description').value,
                                    path: path
                                };
                            }
                        }).then((result) => {
                            if (result.isConfirmed && result.value) {
                                fetch('{{ route("inventory.map.storePolyline") }}', {
                                    method: 'POST',
                                    headers: { 
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify(result.value)
                                })
                                .then(res => res.json())
                                .then(() => Swal.fire('Sukses!', 'Kabel berhasil disimpan.', 'success').then(() => location.reload()))
                                .catch(() => Swal.fire('Gagal!', 'Gagal menyimpan kabel.', 'error'));
                            }
                        });
                    }
                });
                
                // EVENT HANDLER UNTUK MENGHAPUS DARI TOOLBAR
                map.on(L.Draw.Event.DELETED, function (e) {
                    e.layers.eachLayer(function (layer) {
                        const id = layer.options.pointId || layer.options.polylineId;
                        const type = layer.options.layerType;
                        
                        if (id && type) {
                            deleteLayerFromServer(type, id, layer);
                        }
                    });
                });

                // EVENT HANDLER UNTUK TOMBOL HAPUS DI POPUP
                map.on('popupopen', function (e) {
                    const popupNode = e.popup.getElement();
                    if (!popupNode) return;
                    const editBtn = popupNode.querySelector('.popup-edit-btn');
                    
                    if (editBtn) {
                        // Mencegah event listener ganda
                        editBtn.onclick = null; 
                        editBtn.onclick = function () {
                            const type = this.dataset.type;
                            const id = parseInt(this.dataset.id);
                            openEditPopup(type, id);
                        };
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
