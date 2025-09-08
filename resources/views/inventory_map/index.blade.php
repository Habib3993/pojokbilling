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
            
            /* Custom SweetAlert2 Styling */
            .swal-custom-container .swal2-popup {
                border-radius: 12px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                margin-top: 5vh !important;
                max-height: 80vh !important;
                overflow-y: auto !important;
            }
            
            .swal-form-container input:focus,
            .swal-form-container select:focus,
            .swal-form-container textarea:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }
            
            .swal-form-container .swal-field-group label {
                font-family: system-ui, -apple-system, sans-serif;
            }
            
            /* Popup styling yang lebih baik */
            .popup-edit-btn:hover {
                background-color: #2563eb !important;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
            }
            
            .leaflet-popup-content {
                line-height: 1.5;
            }

            /* Color grid styling untuk popup */
            .color-grid label {
                display: block;
            }
            
            .color-grid .w-8 {
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            .color-grid .w-8:hover {
                transform: scale(1.1);
                border-color: #374151 !important;
            }
            
            .swal-form-container .color-grid {
                max-width: 320px;
            }
            
            /* Styling untuk tooltip panjang polyline */
            .polyline-length-tooltip {
                background-color: rgba(0, 0, 0, 0.7) !important;
                border: none !important;
                color: white !important;
                font-size: 11px !important;
                font-weight: 600 !important;
                padding: 4px 8px !important;
                border-radius: 4px !important;
                box-shadow: 0 2px 8px rgba(0,0,0,0.3) !important;
            }
            
            .polyline-length-tooltip:before {
                display: none !important;
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

                // Fungsi helper untuk menghapus data dari array lokal
                function removeDataFromLocalArrays(id, type) {
                    if (type === 'point') {
                        // Hapus dari setiap grup layer
                        allLayerGroups.forEach(group => {
                            if (group.map_points) {
                                group.map_points = group.map_points.filter(point => point.id !== id);
                            }
                        });
                    } else if (type === 'polyline') {
                        // Hapus dari array polylines
                        const index = allPolylines.findIndex(line => line.id === id);
                        if (index > -1) {
                            allPolylines.splice(index, 1);
                        }
                    }
                }

                // Fungsi untuk membuat HTML form yang konsisten
                function createFormHTML(fields, title = '') {
                    let formHTML = `<div class="swal-form-container" style="text-align: left; padding: 1.2em; max-width: 420px;">`;
                    
                    if (title) {
                        formHTML += `<h3 style="margin: 0 0 1.2em 0; color: #374151; font-weight: 600;">${title}</h3>`;
                    }
                    
                    fields.forEach(field => {
                        formHTML += `<div class="swal-field-group" style="margin-bottom: 1rem;">`;
                        formHTML += `<label for="${field.id}" style="display: block; margin-bottom: 0.4rem; font-weight: 500; color: #374151; font-size: 0.875rem;">${field.label}${field.required ? ' <span style="color: #dc2626;">*</span>' : ''}</label>`;
                        
                        if (field.type === 'select') {
                            formHTML += `<select id="${field.id}" class="swal2-select" style="width: 100%; padding: 0.6rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">`;
                            if (field.placeholder) {
                                formHTML += `<option value="">${field.placeholder}</option>`;
                            }
                            formHTML += field.options;
                            formHTML += `</select>`;
                        } else if (field.type === 'textarea') {
                            formHTML += `<textarea id="${field.id}" class="swal2-textarea" placeholder="${field.placeholder || ''}" style="width: 100%; min-height: 70px; padding: 0.6rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem; resize: vertical;">${field.value || ''}</textarea>`;
                        } else if (field.type === 'color-dropdown') {
                            const colors = [
                                '#000000', '#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF', '#C0C0C0',
                                '#808080', '#800000', '#008000', '#000080', '#808000', '#800080', '#008080', '#FFFFFF',
                                '#FFA500', '#A52A2A', '#DDA0DD', '#98FB98', '#F0E68C', '#87CEEB', '#D2691E', '#FF69B4'
                            ];
                            formHTML += `<div class="flex items-center gap-3">
                                <div id="${field.id}-preview" class="w-8 h-6 rounded border-2 border-gray-300" style="background-color: ${field.value || '#e83e58'}"></div>
                                <div class="relative flex-1">
                                    <button type="button" id="${field.id}-btn" class="w-full inline-flex items-center justify-between px-3 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <span>Pilih Warna</span>
                                        <i class="fa fa-chevron-down ml-2"></i>
                                    </button>
                                    <div id="${field.id}-dropdown" class="hidden absolute z-50 mt-1 w-full bg-white rounded-md shadow-lg border border-gray-200 p-2 max-h-40 overflow-y-auto">
                                        <div class="grid grid-cols-6 gap-1">`;
                            colors.forEach(color => {
                                formHTML += `<button type="button" class="color-option-btn w-6 h-6 rounded border border-gray-300 hover:border-gray-500 focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                            style="background-color: ${color}" 
                                            data-color="${color}" 
                                            data-target="${field.id}"></button>`;
                            });
                            formHTML += `</div></div></div></div>`;
                            formHTML += `<input type="hidden" id="${field.id}" value="${field.value || '#e83e58'}">`;
                        } else {
                            formHTML += `<input type="${field.type || 'text'}" id="${field.id}" class="swal2-input" placeholder="${field.placeholder || ''}" value="${field.value || ''}" style="width: 100%; padding: 0.6rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 0.875rem;">`;
                        }
                        formHTML += `</div>`;
                    });
                    
                    formHTML += `</div>`;
                    
                    // Add event listeners after DOM is inserted
                    setTimeout(() => {
                        fields.forEach(field => {
                            if (field.type === 'color-dropdown') {
                                setupColorDropdown(field.id);
                            }
                        });
                    }, 100);
                    
                    return formHTML;
                }

                // Fungsi untuk setup color dropdown
                function setupColorDropdown(fieldId) {
                    const button = document.getElementById(`${fieldId}-btn`);
                    const dropdown = document.getElementById(`${fieldId}-dropdown`);
                    const input = document.getElementById(fieldId);
                    const preview = document.getElementById(`${fieldId}-preview`);

                    if (button && dropdown) {
                        button.addEventListener('click', function(e) {
                            e.preventDefault();
                            dropdown.classList.toggle('hidden');
                        });

                        // Close dropdown when clicking outside
                        document.addEventListener('click', function(e) {
                            if (!button.contains(e.target) && !dropdown.contains(e.target)) {
                                dropdown.classList.add('hidden');
                            }
                        });

                        // Handle color selection
                        dropdown.querySelectorAll('.color-option-btn').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.preventDefault();
                                const selectedColor = this.getAttribute('data-color');
                                input.value = selectedColor;
                                preview.style.backgroundColor = selectedColor;
                                dropdown.classList.add('hidden');
                            });
                        });
                    }
                }

                // Fungsi untuk menangani pemilihan warna
                window.selectColor = function(fieldId, color) {
                    document.getElementById(fieldId).value = color;
                    // Update visual selection
                    const colorGrid = document.querySelector(`#${fieldId}`).closest('.swal-field-group').querySelector('.color-grid');
                    colorGrid.querySelectorAll('.ring-2').forEach(el => el.classList.remove('ring-2', 'ring-blue-500'));
                    event.target.classList.add('ring-2', 'ring-blue-500');
                }

                // Fungsi untuk menghitung panjang polyline
                function calculatePolylineLength(polyline) {
                    const latlngs = polyline.getLatLngs();
                    let totalDistance = 0;
                    
                    for (let i = 0; i < latlngs.length - 1; i++) {
                        totalDistance += latlngs[i].distanceTo(latlngs[i + 1]);
                    }
                    
                    // Konversi ke format yang sesuai
                    if (totalDistance < 1000) {
                        return `${Math.round(totalDistance)} m`;
                    } else {
                        return `${(totalDistance / 1000).toFixed(2)} km`;
                    }
                }

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

                    const fields = [
                        {
                            id: 'swal-edit-name',
                            label: 'Nama',
                            type: 'text',
                            value: currentData.name,
                            required: true,
                            placeholder: type === 'point' ? 'Contoh: ODP-01' : 'Contoh: Kabel Feeder Utama'
                        }
                    ];

                    // Tambahkan field warna hanya untuk polyline
                    if (type === 'polyline') {
                        fields.push({
                            id: 'swal-edit-color',
                            label: 'Warna Kabel',
                            type: 'color-dropdown',
                            value: currentData.color || '#e83e58'
                        });
                    }

                    fields.push({
                        id: 'swal-edit-description',
                        label: 'Deskripsi',
                        type: 'textarea',
                        value: currentData.description || '',
                        placeholder: 'Deskripsi detail (opsional)'
                    });

                    Swal.fire({
                        title: `Edit ${type === 'point' ? 'Titik' : 'Kabel'}`,
                        html: createFormHTML(fields),
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fa fa-save"></i> Simpan',
                        denyButtonText: '<i class="fa fa-trash"></i> Hapus',
                        cancelButtonText: '<i class="fa fa-times"></i> Batal',
                        confirmButtonColor: '#3b82f6',
                        denyButtonColor: '#dc2626',
                        cancelButtonColor: '#6b7280',
                        width: '400px',
                        focusConfirm: false,
                        customClass: {
                            container: 'swal-custom-container'
                        },
                        preConfirm: () => {
                            // Validasi form sebelum mengirim
                            const name = document.getElementById('swal-edit-name').value.trim();
                            if (!name) {
                                Swal.showValidationMessage(`Nama tidak boleh kosong`);
                                return false;
                            }
                            
                            const updatedData = {
                                name: name,
                                description: document.getElementById('swal-edit-description').value.trim(),
                            };

                            // Tambahkan color untuk polyline
                            if (type === 'polyline') {
                                updatedData.color = document.getElementById('swal-edit-color').value;
                            }

                            return updatedData;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) { // Jika klik "Simpan"
                            const updatedData = result.value;
                            
                            fetch(`/inventory-map/${type}s/${id}`, {
                                method: 'PATCH',
                                headers: { 
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(updatedData)
                            })
                            .then(res => res.json().then(data => ({ status: res.status, body: data })))
                            .then(obj => {
                                if (obj.status >= 200 && obj.status < 300 && obj.body.success) {
                                    const dataFromServer = obj.body.point || obj.body.polyline;
                                    Object.assign(currentData, dataFromServer);
                                    
                                    // Update warna polyline di peta jika ada perubahan warna
                                    if (type === 'polyline' && updatedData.color) {
                                        layer.setStyle({ color: updatedData.color });
                                    }
                                    
                                    updatePopupContent(layer, type, currentData);
                                    buildSidebar();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Tersimpan!',
                                        text: 'Data berhasil diperbarui.',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                } else {
                                    throw obj.body;
                                }
                            })
                            .catch(error => {
                                let errorMsg = 'Gagal memperbarui data.';
                                if (error && error.errors) { 
                                    errorMsg = Object.values(error.errors).map(e => e[0]).join('<br>'); 
                                }
                                Swal.fire({ 
                                    icon: 'error', 
                                    title: 'Oops...', 
                                    html: errorMsg,
                                    confirmButtonColor: '#dc2626'
                                });
                            });

                        } else if (result.isDenied) { // Jika klik "Hapus"
                            deleteLayerFromServer(type, id);
                        }
                    });
                }

                /**
                 * FUNGSI BANTUAN: Memperbarui konten popup setelah diedit.
                 */
                function updatePopupContent(layer, type, data) {
                    let popupContent = `<div style="min-width: 150px;">
                        <strong style="font-size: 1.1em;">${data.name}</strong><br>`;
                    
                    // Jika polyline, tampilkan panjang dengan format sederhana seperti di gambar
                    if (type === 'polyline') {
                        const length = calculatePolylineLength(layer);
                        popupContent += `<div style="margin: 8px 0; color: #666; font-size: 0.9em;">
                            <i class="fa fa-arrow-left" style="margin-right: 6px; color: #888;"></i>${length}
                        </div>`;
                    }
                    
                    popupContent += `<p style="margin: 5px 0;">${data.description || '<em>Tidak ada deskripsi</em>'}</p>
                        <hr style="margin: 8px 0;">
                        <button class="popup-edit-btn" data-type="${type}" data-id="${data.id}" style="width:100%; background-color:#3b82f6; color:white; border:none; padding:8px; border-radius:4px; cursor:pointer; transition: all 0.2s;">
                            <i class="fa fa-edit" style="margin-right: 5px;"></i>Edit / Hapus
                        </button>
                    </div>`;
                    
                    layer.bindPopup(popupContent);
                }

                // Fungsi terpisah untuk menambahkan tooltip panjang pada polyline
                function addLengthTooltipToPolyline(polyline) {
                    const length = calculatePolylineLength(polyline);
                    polyline.bindTooltip(length, {
                        permanent: true,
                        direction: 'center',
                        className: 'polyline-length-tooltip',
                        offset: [0, 0]
                    });
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

                        // HAPUS: Tooltip panjang kabel yang permanen di peta
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

                        const fields = [
                            {
                                id: 'swal-name',
                                label: 'Nama Titik',
                                type: 'text',
                                required: true,
                                placeholder: 'Contoh: ODP-01'
                            },
                            {
                                id: 'swal-group',
                                label: 'Grup Layer',
                                type: 'select',
                                required: true,
                                placeholder: 'Pilih Grup Layer...',
                                options: groupOptions
                            },
                            {
                                id: 'swal-location',
                                label: 'Lokasi',
                                type: 'select',
                                placeholder: 'Pilih Lokasi...',
                                options: locationOptions
                            },
                            {
                                id: 'swal-description',
                                label: 'Deskripsi',
                                type: 'textarea',
                                placeholder: 'Deskripsi detail (opsional)'
                            }
                        ];

                        Swal.fire({
                            title: 'Tambah Titik Baru',
                            html: createFormHTML(fields),
                            confirmButtonText: '<i class="fa fa-save"></i> Simpan',
                            cancelButtonText: '<i class="fa fa-times"></i> Batal',
                            confirmButtonColor: '#3b82f6',
                            cancelButtonColor: '#6b7280',
                            width: '400px',
                            showCancelButton: true,
                            focusConfirm: false,
                            customClass: {
                                container: 'swal-custom-container'
                            },
                            preConfirm: () => {
                                const name = document.getElementById('swal-name').value.trim();
                                const groupId = document.getElementById('swal-group').value;
                                if (!name) {
                                    Swal.showValidationMessage(`Nama Titik wajib diisi`);
                                    return false;
                                }
                                if (!groupId) {
                                    Swal.showValidationMessage(`Grup Layer wajib dipilih`);
                                    return false;
                                }
                                return {
                                    name: name,
                                    layer_group_id: groupId,
                                    location_id: document.getElementById('swal-location').value,
                                    description: document.getElementById('swal-description').value.trim(),
                                    coordinates: coords
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
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Sukses!',
                                            text: 'Titik berhasil disimpan.',
                                            timer: 2000,
                                            showConfirmButton: false
                                        });
                                    } else { throw obj.body; }
                                })
                                .catch(error => {
                                    let errorMsg = 'Terjadi kesalahan pada server.';
                                    if (error && error.errors) { 
                                        errorMsg = Object.values(error.errors).map(e => e[0]).join('<br>'); 
                                    }
                                    Swal.fire({ 
                                        icon: 'error', 
                                        title: 'Gagal Menyimpan', 
                                        html: errorMsg,
                                        confirmButtonColor: '#dc2626'
                                    });
                                });
                            }
                        });
                    }

                    // --- LOGIKA UNTUK MENAMBAH GARIS BARU ---
                    if (type === 'polyline') {
                        const path = JSON.stringify(layer.getLatLngs());
                        
                        const fields = [
                            {
                                id: 'swal-name',
                                label: 'Nama Kabel',
                                type: 'text',
                                required: true,
                                placeholder: 'Contoh: Kabel Feeder Utama'
                            },
                            {
                                id: 'swal-color',
                                label: 'Warna Kabel',
                                type: 'color-dropdown',
                                value: '#e83e58'
                            },
                            {
                                id: 'swal-description',
                                label: 'Deskripsi',
                                type: 'textarea',
                                placeholder: 'Deskripsi detail (opsional)'
                            }
                        ];

                        Swal.fire({
                            title: 'Tambah Kabel Baru',
                            html: createFormHTML(fields),
                            confirmButtonText: '<i class="fa fa-save"></i> Simpan',
                            cancelButtonText: '<i class="fa fa-times"></i> Batal',
                            confirmButtonColor: '#3b82f6',
                            cancelButtonColor: '#6b7280',
                            width: '400px',
                            showCancelButton: true,
                            focusConfirm: false,
                            customClass: {
                                container: 'swal-custom-container'
                            },
                            preConfirm: () => {
                                const name = document.getElementById('swal-name').value.trim();
                                if (!name) {
                                    Swal.showValidationMessage(`Nama Kabel wajib diisi`);
                                    return false;
                                }
                                return {
                                    name: name,
                                    color: document.getElementById('swal-color').value,
                                    description: document.getElementById('swal-description').value.trim(),
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
                                        
                                        // Tambahkan tooltip panjang kabel
                                        addLengthTooltipToPolyline(newLine);
                                        
                                        // 2. Bangun ulang sidebar
                                        buildSidebar();
                                        
                                        // 3. Tampilkan notifikasi
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Sukses!',
                                            text: 'Kabel berhasil disimpan.',
                                            timer: 2000,
                                            showConfirmButton: false
                                        });
                                    } else { throw obj.body; }
                                })
                                .catch(error => {
                                    Swal.fire({ 
                                        icon: 'error', 
                                        title: 'Gagal Menyimpan', 
                                        text: 'Terjadi kesalahan pada server.',
                                        confirmButtonColor: '#dc2626'
                                    });
                                });
                            }
                        });
                    }
                });

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

        <script>
        // Hide sidebar dynamically on mobile devices
        function hideSidebarOnMobile() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth <= 768) {
                sidebar.style.display = 'none';
            } else {
                sidebar.style.display = 'block';
            }
        }

        // Run on page load and window resize
        window.addEventListener('load', hideSidebarOnMobile);
        window.addEventListener('resize', hideSidebarOnMobile);
        </script>
    @endpush
</x-app-layout>