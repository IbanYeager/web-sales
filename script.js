// script.js

let arrayFoto = [];

// Fungsi untuk memproses file foto yang dipilih dari galeri atau kamera
function handleFileSelect(event) {
    const files = event.target.files;
    const previewContainer = document.getElementById('photoPreview');
    
    // Batasi maksimal 5 foto
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

            // Jika ini foto pertama, hapus icon placeholder
            if (arrayFoto.length === 1) {
                previewContainer.innerHTML = '';
            }

            // Buat elemen preview gambar
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

// Fungsi simulasi untuk tombol Ambil Lokasi di halaman Input
function getLocation() {
    alert("Mengambil titik kordinat GPS Anda saat ini...");
}

// Fungsi untuk tombol Simpan Aktivitas dan menyimpannya ke LocalStorage
function simpanData() {
    const form = document.getElementById('formAktivitas');
    const jenis = document.getElementById('jenisAktivitas').value;
    const keterangan = document.getElementById('keteranganAktivitas').value;
    const lokasi = document.querySelector('#lokasiValue p').innerText;

    if(form && form.checkValidity() && arrayFoto.length > 0) {
        // Ambil data lama dari localStorage
        const existingActivities = JSON.parse(localStorage.getItem('userActivities') || '[]');

        // Buat objek aktivitas baru
        const newActivity = {
            id: Date.now(),
            jenis: jenis,
            keterangan: keterangan,
            lokasi: lokasi,
            foto: arrayFoto[0], // Gunakan foto pertama sebagai thumbnail
            waktu: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            status: 'Selesai'
        };

        // Tambahkan ke urutan paling atas
        existingActivities.unshift(newActivity);

        // Simpan kembali ke localStorage
        localStorage.setItem('userActivities', JSON.stringify(existingActivities));

        alert("Aktivitas berhasil disimpan dan dikirim ke Supervisor!");
        window.location.href = "index.html";
    } else {
        alert("Mohon lengkapi semua field (*) dan tambahkan minimal 1 foto.");
    }
}

// FUNGSI BARU: Simulasi membagikan info Promo (Bisa dihubungkan ke WhatsApp API nanti)
function sharePromo(carName, packageName, tdp) {
    const message = `Halo Bapak/Ibu! Kami sedang ada promo menarik:\n\n` +
                    `🚗 Model: ${carName}\n` +
                    `🎁 Promo: ${packageName}\n` +
                    `💰 Total Bayar Pertama (TDP): ${tdp}\n\n` +
                    `Silakan balas pesan ini jika Bapak/Ibu tertarik untuk informasi lebih lanjut!`;
    
    alert("Teks ini sudah siap disalin/dikirim ke WhatsApp Customer:\n\n" + message);
}

// Mengatur active state saat klik bottom nav (tidak mencegah pindah halaman)
document.addEventListener("DOMContentLoaded", () => {
    const navItems = document.querySelectorAll('.nav-item');

    navItems.forEach(item => {
        item.addEventListener('click', function() {
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
        });
    });
});

// toggle untuk link “lihat semua” fitur di home
function toggleMenuUtama(e) {
    if (e && e.preventDefault) e.preventDefault();

    const full = document.getElementById('menuUtamaFull');
    if (!full) return;

    full.classList.toggle('hidden');
}


// Fungsi untuk membuka Pop-up "Lihat Semua Fitur"
function openFeatureModal(event) {
    if(event) event.preventDefault(); // Mencegah halaman reload
    document.getElementById('featureModal').classList.add('show');
}

// Fungsi untuk menutup Pop-up "Lihat Semua Fitur"
function closeFeatureModal() {
    document.getElementById('featureModal').classList.remove('show');
}
// Fungsi untuk membuka Modal apapun berdasarkan ID
function openModal(modalId, event) {
    if(event) event.preventDefault();
    document.getElementById(modalId).classList.add('show');
}

// Fungsi untuk menutup Modal apapun berdasarkan ID
function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

// Tambahan: Tutup modal jika user klik di area luar konten (overlay)
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal-overlay');
    modals.forEach(modal => {
        if (event.target == modal) {
            modal.classList.remove('show');
        }
    });
}

// Fungsi untuk menampilkan daftar aktivitas di halaman utama
// Pastikan ada elemen dengan ID 'latestActivities' di index.html Anda
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

// Jalankan fungsi render saat halaman dimuat
document.addEventListener("DOMContentLoaded", renderAktivitasTerakhir);