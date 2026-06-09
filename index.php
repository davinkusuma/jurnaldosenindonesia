<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Content Security Policy untuk mencegah XSS dan data injection -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net; img-src 'self' data: https:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta http-equiv="Referrer-Policy" content="strict-origin-when-cross-origin">
    
    <title>Jurnal Dosen Indonesia - Pencarian Akademik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/inter@5.0.8/index.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .search-container { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
        .btn-primary { transition: all 0.2s ease; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2); }
        .btn-primary:active { transform: translateY(0); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 0.5s ease-out forwards; }
        .result-card { transition: all 0.2s ease; }
        .result-card:hover { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); transform: translateY(-2px); }
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            line-clamp: 3;
            overflow: hidden;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col text-slate-800">

    <header class="w-full bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <svg class="w-10 h-10 text-blue-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
                <span class="font-bold text-lg text-slate-900 hidden sm:block">Jurnal Dosen Indonesia</span>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-500 bg-emerald-50 text-emerald-700 px-3 py-1.5 rounded-full border border-emerald-200">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                Sistem Aman & Aktif
            </div>
        </div>
    </header>

    <main class="flex-1 flex flex-col items-center pt-12 pb-20 px-4">
        <div class="w-full max-w-3xl flex flex-col items-center fade-in mb-10">
            <svg class="w-24 h-24 md:w-32 md:h-32 text-blue-700 mb-6 drop-shadow-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
            
            <h1 class="text-3xl md:text-5xl font-bold text-slate-900 mb-4 text-center tracking-tight">
                Jurnal Dosen Indonesia
            </h1>
            
            <p class="text-center text-slate-600 mb-8 max-w-2xl text-sm md:text-base leading-relaxed">
                Pencarian Akademik Terintegrasi: Cepat, Akurat, dan Terpercaya.
            </p>

            <form id="searchForm" class="w-full max-w-2xl">
                <div class="search-container flex flex-col sm:flex-row gap-3 bg-white p-3 rounded-2xl border border-slate-200">
                    <div class="flex-1 relative">
                        <svg class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input 
                            type="text" 
                            id="searchInput" 
                            placeholder="Cari judul, penulis, DOI, atau topik penelitian..." 
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-slate-700 placeholder-slate-400 transition-all bg-slate-50 focus:bg-white"
                            autocomplete="off"
                            maxlength="255"
                        >
                    </div>
                    <button 
                        type="submit" 
                        class="btn-primary bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-xl transition-colors flex items-center justify-center gap-2 min-w-[140px]"
                    >
                        <span id="btnText">Cari Jurnal</span>
                        <svg id="btnIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <svg id="loadingIcon" class="w-5 h-5 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <div id="statusMessage" class="w-full max-w-4xl px-4 mb-6 hidden">
            <div id="statusContent" class="rounded-xl p-4 text-sm font-medium"></div>
        </div>

        <div id="resultsArea" class="w-full max-w-4xl px-4 hidden">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-slate-900">Hasil Pencarian</h2>
                <span id="resultCount" class="text-sm text-slate-500 bg-slate-100 px-3 py-1 rounded-full"></span>
            </div>
            <div id="resultsContainer" class="flex flex-col gap-4"></div>
        </div>

        
                <div id="infoCards" class="w-full max-w-5xl px-4 grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                    <!-- Card 1 -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 mb-2 text-lg">Akses Akademik Terpadu</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">Terintegrasi langsung dengan indeks Google Scholar, memudahkan penelusuran artikel ilmiah, jurnal, dan prosiding terkini secara komprehensif.</p>
                    </div>

                    <!-- Card 2 -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 mb-2 text-lg">Fokus Dosen & Peneliti</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">Dirancang khusus untuk meningkatkan produktivitas riset dosen dan akademisi Indonesia dalam menjelajahi literatur global dengan efisien.</p>
                    </div>

                    <!-- Card 3 -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 mb-2 text-lg">Keamanan & Privasi Terjamin</h3>
                        <p class="text-sm text-slate-600 leading-relaxed">Infrastruktur backend yang aman dengan perlindungan kunci API di sisi server, validasi input ketat, dan enkripsi untuk menjaga kerahasiaan pencarian Anda.</p>
                    </div>
                </div>
    </main>

    <footer class="w-full border-t border-slate-200 bg-white mt-auto">
        <div class="max-w-6xl mx-auto px-4 py-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="text-sm text-slate-500">&copy; 2026 Jurnal Dosen Indonesia. Hak Cipta Dilindungi.</div>
            <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-slate-600">
                <button onclick="openModal('modalTentang')" class="hover:text-blue-600 transition-colors font-medium">Tentang</button>
                <button onclick="openModal('modalDonasi')" class="hover:text-blue-600 transition-colors font-medium">Donasi</button>
                <button onclick="openModal('modalKontak')" class="hover:text-blue-600 transition-colors font-medium">Kontak</button>
                <button onclick="openModal('modalPrivasi')" class="hover:text-blue-600 transition-colors font-medium">Kebijakan Privasi</button>
            </div>
        </div>
    </footer>

    <script>
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.getElementById('searchInput');
        const btnText = document.getElementById('btnText');
        const btnIcon = document.getElementById('btnIcon');
        const loadingIcon = document.getElementById('loadingIcon');
        const resultsArea = document.getElementById('resultsArea');
        const resultsContainer = document.getElementById('resultsContainer');
        const resultCount = document.getElementById('resultCount');
        const infoCards = document.getElementById('infoCards');
        const statusMessage = document.getElementById('statusMessage');
        const statusContent = document.getElementById('statusContent');

        function showStatus(message, type) {
            statusMessage.classList.remove('hidden');
            statusContent.textContent = message;
            if (type === 'error') statusContent.className = 'rounded-xl p-4 text-sm font-medium bg-red-50 text-red-700 border border-red-200';
            else if (type === 'success') statusContent.className = 'rounded-xl p-4 text-sm font-medium bg-green-50 text-green-700 border border-green-200';
            else if (type === 'warning') statusContent.className = 'rounded-xl p-4 text-sm font-medium bg-yellow-50 text-yellow-700 border border-yellow-200';
            setTimeout(() => statusMessage.classList.add('hidden'), 6000);
        }

        searchForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const query = searchInput.value.trim();
            if (!query) {
                searchInput.focus();
                searchInput.classList.add('ring-2', 'ring-red-500', 'border-red-500');
                setTimeout(() => searchInput.classList.remove('ring-2', 'ring-red-500', 'border-red-500'), 2000);
                return;
            }

            btnText.textContent = 'Mencari...';
            btnIcon.classList.add('hidden');
            loadingIcon.classList.remove('hidden');
            searchInput.disabled = true;
            resultsArea.classList.add('hidden');
            infoCards.classList.add('hidden');
            statusMessage.classList.add('hidden');

            try {
                // Sanitasi sisi klien sebelum dikirim
                const safeQuery = encodeURIComponent(query.replace(/[<>]/g, ''));
                const apiUrl = `index.php?route=api/search&q=${safeQuery}`;
                const response = await fetch(apiUrl);
                
                if (!response.ok) {
                    const errData = await response.json().catch(() => ({}));
                    throw new Error(errData.error || `HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.organic_results && data.organic_results.length > 0) {
                    renderResults(data.organic_results);
                } else {
                    showStatus('Tidak ada hasil ditemukan untuk pencarian ini. Coba kata kunci lain.', 'warning');
                    resultsArea.classList.add('hidden');
                    infoCards.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Search error:', error);
                showStatus(error.message || 'Terjadi kesalahan saat menghubungi server.', 'error');
                infoCards.classList.remove('hidden');
            } finally {
                btnText.textContent = 'Cari Jurnal';
                btnIcon.classList.remove('hidden');
                loadingIcon.classList.add('hidden');
                searchInput.disabled = false;
                searchInput.focus();
            }
        });

        function renderResults(results) {
            resultsArea.classList.remove('hidden');
            resultCount.textContent = `${results.length} hasil ditemukan`;
            resultsContainer.innerHTML = '';

            results.forEach((item, index) => {
                const card = document.createElement('div');
                card.className = 'result-card bg-white rounded-2xl border border-slate-200 p-6 fade-in';
                card.style.animationDelay = `${index * 0.05}s`;

                let typeBadge = '';
                if (item.type) {
                    const typeColor = item.type.toLowerCase() === 'pdf' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700';
                    typeBadge = `<span class="inline-block px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider ${typeColor} mb-2">${item.type}</span>`;
                }

                let authorsText = 'Penulis tidak tersedia';
                if (item.publication_info && item.publication_info.authors && item.publication_info.authors.length > 0) {
                    authorsText = item.publication_info.authors.map(a => a.name).join(', ');
                }

                let pubSummary = (item.publication_info && item.publication_info.summary) ? item.publication_info.summary : '';
                let snippet = item.snippet || 'Tidak ada abstrak tersedia.';

                let resourceLinks = '';
                if (item.resources && item.resources.length > 0) {
                    resourceLinks = item.resources.map(res => {
                        const isPdf = res.file_format && res.file_format.toUpperCase() === 'PDF';
                        const badgeColor = isPdf ? 'text-red-600 hover:text-red-800' : 'text-emerald-600 hover:text-emerald-800';
                        const icon = isPdf 
                            ? '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>'
                            : '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>';
                        return `
                            <a href="${res.link}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-sm font-medium ${badgeColor} transition-colors">
                                ${icon}
                                ${res.file_format || 'Link'} [${res.title}]
                            </a>
                        `;
                    }).join('');
                }

                let citedBy = '';
                if (item.inline_links && item.inline_links.cited_by && item.inline_links.cited_by.total) {
                    citedBy = `<span class="text-xs text-slate-500">Dikutip ${item.inline_links.cited_by.total} kali</span>`;
                }

                let versions = '';
                if (item.inline_links && item.inline_links.versions && item.inline_links.versions.total) {
                    versions = `<span class="text-xs text-slate-500">• ${item.inline_links.versions.total} versi</span>`;
                }

                card.innerHTML = `
                    <div class="flex flex-col gap-3">
                        <div>
                            ${typeBadge}
                            <a href="${item.link}" target="_blank" rel="noopener noreferrer" class="text-lg md:text-xl font-bold text-blue-700 hover:text-blue-900 hover:underline transition-colors leading-tight">
                                ${item.title}
                            </a>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-sm text-slate-700">
                            <span class="font-medium">${authorsText}</span>
                        </div>
                        <div class="text-sm text-slate-500">
                            ${pubSummary}
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                            ${citedBy}
                            ${versions}
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed line-clamp-3">${snippet}</p>
                        <div class="flex flex-wrap items-center gap-4 pt-3 border-t border-slate-100 mt-1">
                            <a href="${item.link}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                Buka di Google Scholar
                            </a>
                            ${resourceLinks}
                        </div>
                    </div>
                `;
                resultsContainer.appendChild(card);
            });
        }

        searchInput.addEventListener('input', function() {
            if (!resultsArea.classList.contains('hidden')) {
                resultsArea.classList.add('hidden');
                infoCards.classList.remove('hidden');
            }
        });
    </script>

    <!-- Modal: Kebijakan Privasi, Tentang, Donasi, Kontak -->
    <div id="modalPrivasi" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] overflow-y-auto p-6 relative">
            <button onclick="closeModal('modalPrivasi')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <h3 class="text-2xl font-bold text-slate-900 mb-4">Kebijakan Privasi</h3>
            <div class="prose prose-slate text-sm text-slate-600 leading-relaxed">
                <p>Privasi dan keamanan data Anda adalah prioritas utama kami. Jurnal Dosen Indonesia dirancang dengan prinsip <em>privacy-by-design</em>...</p>
                <!-- Tempel teks Versi Lengkap di sini -->
            </div>
        </div>
    </div>

    <div id="modalTentang" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] overflow-y-auto p-6 relative">
            <button onclick="closeModal('modalTentang')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <h3 class="text-2xl font-bold text-slate-900 mb-4">Tentang</h3>
            <div class="prose prose-slate text-sm text-slate-600 leading-relaxed">
                <p>Jurnal Dosen Indonesia adalah mesin pencari akademik yang membantu dosen dan peneliti menemukan literatur ilmiah.</p>
            </div>
        </div>
    </div>

    <div id="modalDonasi" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] overflow-y-auto p-6 relative">
            <button onclick="closeModal('modalDonasi')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <h3 class="text-2xl font-bold text-slate-900 mb-4">Donasi</h3>
            <div class="prose prose-slate text-sm text-slate-600 leading-relaxed">
                <p>Dukungan Anda membantu pengembangan platform dan menjaga layanan ini tetap bebas akses bagi akademisi.</p>
            </div>
        </div>
    </div>

    <div id="modalKontak" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] overflow-y-auto p-6 relative">
            <button onclick="closeModal('modalKontak')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <h3 class="text-2xl font-bold text-slate-900 mb-4">Kontak</h3>
            <div class="prose prose-slate text-sm text-slate-600 leading-relaxed">
                <p>Untuk pertanyaan atau kerja sama, hubungi kami melalui email: kontak@jurnaldosen.id</p>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.remove('hidden');
            el.classList.add('flex');
        }
        function closeModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.add('hidden');
            el.classList.remove('flex');
        }
        // Tutup modal jika klik di luar area konten
        window.addEventListener('click', function(event) {
            if (event.target.classList && event.target.classList.contains('fixed')) {
                event.target.classList.add('hidden');
                event.target.classList.remove('flex');
            }
        });
    </script>
</body>
</html>