// script.js

// ==========================================
// 1. STATE GLOBAL & FITUR UMUM (KAMERA / AKTIVITAS)
// ==========================================
let arrayFoto = [];

function handleFileSelect(event) {
    const files = event.target.files;
    const previewContainer = document.getElementById('photoPreview');
    if (!previewContainer) return;
    
    if (arrayFoto.length + files.length > 5) {
        alert("Maksimal 5 foto saja.");
        return;
    }

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();

        reader.onload = function(e) {
            const base64Image = e.target.result;
            arrayFoto.push(base64Image);

            if (arrayFoto.length === 1) {
                previewContainer.innerHTML = '';
            }

            const img = document.createElement('img');
            img.src = base64Image;
            img.style.width = '70px';
            img.style.height = '70px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '8px';
            previewContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
}

function getLocation() {
    alert("Mengambil titik kordinat GPS Anda saat ini...");
}

function simpanData() {
    const form = document.getElementById('formAktivitas');
    const jenisEl = document.getElementById('jenisAktivitas');
    const keteranganEl = document.getElementById('keteranganAktivitas');
    const lokasiEl = document.querySelector('#lokasiValue p');

    const jenis = jenisEl ? jenisEl.value : '';
    const keterangan = keteranganEl ? keteranganEl.value : '';
    const lokasi = lokasiEl ? lokasiEl.innerText : '';

    if(form && form.checkValidity() && arrayFoto.length > 0) {
        const existingActivities = JSON.parse(localStorage.getItem('userActivities') || '[]');

        const newActivity = {
            id: Date.now(),
            jenis: jenis,
            keterangan: keterangan,
            lokasi: lokasi,
            foto: arrayFoto[0],
            waktu: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            status: 'Selesai'
        };

        existingActivities.unshift(newActivity);
        localStorage.setItem('userActivities', JSON.stringify(existingActivities));

        alert("Aktivitas berhasil disimpan dan dikirim ke Supervisor!");
        window.location.href = "index.html";
    } else {
        alert("Mohon lengkapi semua field (*) dan tambahkan minimal 1 foto.");
    }
}

// ==========================================
// 2. KONTROL TAMPILAN MODAL / INTERAKSI UTAMA
// ==========================================
function toggleMenuUtama(e) {
    if (e && e.preventDefault) e.preventDefault();
    const full = document.getElementById('menuUtamaFull');
    if (!full) return;
    full.classList.toggle('hidden');
}

function openFeatureModal(event) {
    if(event) event.preventDefault();
    const modal = document.getElementById('featureModal');
    if(modal) modal.classList.add('show');
}

function closeFeatureModal() {
    const modal = document.getElementById('featureModal');
    if(modal) modal.classList.remove('show');
}

function openModal(modalId, event) {
    if(event) event.preventDefault();
    const modal = document.getElementById(modalId);
    if(modal) modal.classList.add('show');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if(modal) modal.classList.remove('show');
}

// Menutup modal saat klik di luar area card (area overlay yang gelap)
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal-overlay');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.classList.remove('show');
        }
    });
}

// Interaksi modern: close modal/bottom-sheet dengan tombol ESC
document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    const activeModal = document.querySelector('.modal-overlay.show');
    if (activeModal) activeModal.classList.remove('show');

    const promoModal = document.getElementById('customPromoModal');
    if (promoModal && promoModal.classList.contains('show-modal')) {
        promoModal.classList.remove('show-modal');
    }
});

function renderAktivitasTerakhir() {
    const container = document.getElementById('latestActivities');
    if (!container) return;

    const activities = JSON.parse(localStorage.getItem('userActivities') || '[]');
    
    if (activities.length === 0) {
        container.innerHTML = '<p style="text-align:center; font-size:12px; color:var(--text-muted); padding:20px;">Belum ada aktivitas.</p>';
        return;
    }

    container.innerHTML = activities.map(act => `
        <div class="activity-card">
            <img src="${act.foto}" alt="Aktivitas" class="activity-img">
            <div class="activity-details">
                <div class="activity-header">
                    <span class="activity-type">
                        <span class="dot-green"></span> ${act.jenis.charAt(0).toUpperCase() + act.jenis.slice(1)}
                    </span>
                    <span class="badge-status status-selesai">${act.status}</span>
                </div>
                <p class="activity-desc">${act.keterangan}</p>
                <div class="activity-footer">
                    <span class="activity-time"><i class="far fa-clock"></i> ${act.waktu}</span>
                    <span class="activity-time"><i class="fas fa-map-marker-alt"></i> ${act.lokasi}</span>
                </div>
            </div>
        </div>
    `).join('');
}

// ==========================================
// 3. FITUR PROMO & PENCARIAN
// ==========================================
let seluruhDataPaket = [];

const formatRupiah = (angka) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka || 0);
};

function sharePromo(carName, packageName, tdp) {
    const message = `Halo Bapak/Ibu! Kami sedang ada promo menarik:\n\n` +
                    `🚗 Model: ${carName}\n` +
                    `🎁 Promo: ${packageName}\n` +
                    `💰 Total Bayar Pertama (TDP): ${tdp}\n\n` +
                    `Silakan balas pesan ini jika Bapak/Ibu tertarik untuk informasi lebih lanjut!`;
    
    alert("Teks ini sudah siap disalin/dikirim ke WhatsApp Customer:\n\n" + message);
}

async function ambilDataPromo() {
    const container = document.getElementById('promo-container');
    if (!container) return; 

    try {
        const response = await fetch('../api/api_promo.php'); 
        const result = await response.json();

        if (result.ok === false) {
            container.innerHTML = `
                <div class="promo-empty promo-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <p>${result.message}</p>
                </div>`;
            return;
        }

        seluruhDataPaket = result;
        tampilkanPaket(seluruhDataPaket);
        populateFilterDropdown(seluruhDataPaket); 
    } catch (error) {
        console.error('Error fetching data:', error);
        container.innerHTML = `
            <div class="promo-empty promo-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <p>Gagal terhubung ke server/database. Coba muat ulang halaman.</p>
            </div>`;
    }
}

function populateFilterDropdown(daftarPaket) {
    const filterSelect = document.getElementById('filter-paket');
    if (!filterSelect) return;

    const uniquePaketNames = [...new Set(daftarPaket.map(p => p.nama_paket).filter(n => n))];
    
    const firstOption = filterSelect.options[0];
    filterSelect.innerHTML = '';
    filterSelect.appendChild(firstOption);
    
    uniquePaketNames.sort().forEach(nama => {
        const option = document.createElement('option');
        option.value = nama.toLowerCase().trim();
        option.textContent = nama;
        filterSelect.appendChild(option);
    });
}

function tampilkanPaket(daftarPaket) {
    const container = document.getElementById('promo-container');
    const totalData = document.getElementById('total-data');
    if (!container) return;

    totalData.innerText = `${daftarPaket.length} paket ditemukan`;
    container.innerHTML = '';

    if (daftarPaket.length === 0) {
        container.innerHTML = `
            <div class="promo-empty">
                <i class="fa-solid fa-box-open"></i>
                <p>Tidak ada paket yang cocok. Coba ubah kata kunci atau filter.</p>
            </div>`;
        return;
    }

    const grupPaket = {};
    daftarPaket.forEach(paket => {
        const namaPaket = paket.nama_paket || 'Paket Lainnya';
        if (!grupPaket[namaPaket]) {
            grupPaket[namaPaket] = [];
        }
        grupPaket[namaPaket].push(paket);
    });

    const namaPaketUrut = Object.keys(grupPaket).sort();

    namaPaketUrut.forEach((namaPaket, index) => {
        const paketList = grupPaket[namaPaket];
        const groupId = `promo-group-${index}`;
        const isOpen = index === 0; 

        const cardsHTML = paketList.map(paket => {
            const skemaBadge = paket.skema
                ? `<span class="promo-badge">${paket.skema}</span>`
                : '';

            return `
                <div class="promo-card">
                    <div class="promo-card-top">
                        <span class="promo-car-name"><i class="fa-solid fa-car-side"></i> ${paket.tipe_mobil}</span>
                        ${skemaBadge}
                    </div>
                    <span class="promo-price-label">Angsuran / bulan</span>
                    <div class="promo-price-main">${formatRupiah(paket.angsuran)}</div>
                    <div class="promo-price-sub">TDP <b>${formatRupiah(paket.tdp)}</b> &middot; Tenor <b>${paket.tenor} bln</b></div>
                    <button type="button" class="btn-outline-blue promo-share-btn" onclick="sharePromo('${paket.tipe_mobil}', '${paket.nama_paket}', '${formatRupiah(paket.tdp)}')">
                        <i class="fa-brands fa-whatsapp"></i> Bagikan Info
                    </button>
                </div>`;
        }).join('');

        const groupHTML = `
            <div class="promo-group">
                <button type="button" class="promo-group-header" onclick="toggleGroup('${groupId}')">
                    <span class="promo-group-title">
                        <i class="fa-solid fa-tags"></i> ${namaPaket}
                        <span class="promo-group-count">${paketList.length}</span>
                    </span>
                    <i class="fa-solid fa-chevron-down promo-group-chevron${isOpen ? ' rotated' : ''}" id="chevron-${groupId}"></i>
                </button>
                <div class="promo-group-body${isOpen ? ' open' : ''}" id="${groupId}">
                    ${cardsHTML}
                </div>
            </div>`;

        container.insertAdjacentHTML('beforeend', groupHTML);
    });
}

function toggleGroup(groupId) {
    const body = document.getElementById(groupId);
    const chevron = document.getElementById(`chevron-${groupId}`);
    if (!body) return;
    body.classList.toggle('open');
    if (chevron) chevron.classList.toggle('rotated');
}

function saringDataPromo() {
    const searchInput = document.getElementById('search-mobil');
    const filterInput = document.getElementById('filter-paket');
    const resetBtn = document.getElementById('reset-filter-btn');
    if (!searchInput || !filterInput) return;

    const kataKunci = searchInput.value.toLowerCase().trim();
    const tipeFilter = filterInput.value.toLowerCase().trim();

    const dataTersaring = seluruhDataPaket.filter(paket => {
        const mobil = paket.tipe_mobil ? paket.tipe_mobil.toLowerCase() : '';
        const nama = paket.nama_paket ? paket.nama_paket.toLowerCase().trim() : '';
        
        const cocokMobil = kataKunci === "" || mobil.includes(kataKunci);
        const cocokPaket = tipeFilter === "" || nama === tipeFilter;
        
        return cocokMobil && cocokPaket;
    });

    if (resetBtn) {
        resetBtn.style.display = (kataKunci || tipeFilter) ? 'flex' : 'none';
    }

    tampilkanPaket(dataTersaring);
}

function resetFilterPromo() {
    const searchInput = document.getElementById('search-mobil');
    const filterInput = document.getElementById('filter-paket');
    if (searchInput) searchInput.value = '';
    if (filterInput) filterInput.value = '';
    saringDataPromo();
}

// ==========================================
// 4. INISIALISASI AKTIVITAS HALAMAN (ONLOAD)
// ==========================================
document.addEventListener("DOMContentLoaded", () => {
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
        });
    });

    if (document.getElementById('latestActivities')) {
        renderAktivitasTerakhir();
    }

    if(document.getElementById('promo-container')) {
        ambilDataPromo();
        document.getElementById('search-mobil').addEventListener('input', saringDataPromo);
        document.getElementById('filter-paket').addEventListener('change', saringDataPromo);
    }
});