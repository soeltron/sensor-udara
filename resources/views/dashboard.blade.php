@extends('layouts.app')

@section('title', '')

@section('page-title', '')

@section('content')

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Sensor summary and detail panels --}}
    <div id="section-cards">
    @php
        $historyCount = $history->count();
        $avgTemp = $historyCount ? round($history->avg('temperature'), 1) : null;
        $avgHum = $historyCount ? round($history->avg('humidity'), 1) : null;
        $avgAir = $historyCount ? round($history->avg('air_quality'), 1) : null;
    @endphp
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="text-muted fw-semibold mb-0"><i class="fas fa-broadcast-tower me-2 text-primary"></i>Data Sensor</h6>
        <small class="text-muted">Klik label suhu untuk beralih °C / °F.</small>
    </div>

    {{-- Metric Cards --}}
    <div class="row g-4 mb-4">
        {{-- Suhu --}}
        <div class="col-md-4">
            <div class="metric-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <p class="text-muted mb-1 fw-medium" style="font-size:0.85rem;">SUHU SAAT INI</p>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-unit-toggle" onclick="toggleUnit()" style="font-size:0.78rem;">Ubah ke °{{ ($setting->temperature_unit ?? 'celsius') === 'celsius' ? 'F' : 'C' }}</button>
                        </div>
                        <h2 class="metric-value text-dark mb-0">
                            <span id="val-temp">{{ $latestData->temperature ?? '--' }}</span>
                            <span id="temp-unit-label" style="font-size:1.2rem;">°{{ strtoupper(substr($setting->temperature_unit ?? 'celsius', 0, 1)) }}</span>
                        </h2>
                        <small class="text-muted" id="temp-subtext">
                            @if($latestData && $setting)
                                {{ ($setting->temperature_unit ?? 'celsius') === 'fahrenheit' ? round(($latestData->temperature * 9/5) + 32, 1).'°F' : $latestData->temperature.'°C' }}
                            @endif
                        </small>
                    </div>
                    <div class="icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="fa-solid fa-temperature-three-quarters"></i>
                    </div>
                </div>
                @if($latestData && $setting)
                    @php
                        $tempVal = $latestData->temperature ?? 0;
                        $maxTemp = $setting->max_temperature ?? 30;
                        $isOverTemp = $tempVal > $maxTemp;
                    @endphp
                    @if($isOverTemp)
                    <div class="mt-2">
                        <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Melebihi Batas!</span>
                    </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Kelembapan --}}
        <div class="col-md-4" id="section-humidity">
            <div class="metric-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-medium" style="font-size:0.85rem;">KELEMBAPAN RUANGAN</p>
                        <h2 class="metric-value text-dark mb-0"><span id="val-hum">{{ $latestData->humidity ?? '--' }}</span><span style="font-size:1.2rem;">%</span></h2>
                    </div>
                    <div class="icon-box bg-info bg-opacity-10 text-info">
                        <i class="fa-solid fa-droplet"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kualitas Udara --}}
        <div class="col-md-4" id="section-air">
            <div class="metric-card p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fw-medium" style="font-size:0.75rem;">KUALITAS UDARA</p>
                        <div id="air-status-category" style="font-size:2.8rem; font-weight:900; line-height:1; margin-bottom:0.8rem; color:#3b82f6;">--</div>
                        <p class="text-muted mb-0" style="font-size:0.85rem;">Nilai ADC: <span id="val-air" class="fw-bold text-dark">{{ $latestData->air_quality ?? '--' }}</span></p>
                    </div>
                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-wind"></i>
                    </div>
                </div>
                @if($latestData && $setting)
                    @php
                        $aq = $latestData->air_quality ?? null;
                        $minAq = $setting->min_air_quality ?? 0;
                        $maxAq = $setting->max_air_quality ?? 500;
                        $aqCategory = null;
                        if ($aq !== null) {
                            if ($aq <= $minAq) $aqCategory = 'BAIK';
                            elseif ($aq <= $maxAq) $aqCategory = 'SEDANG';
                            else $aqCategory = 'BURUK';
                        }
                    @endphp
                    @if($aqCategory === 'BURUK')
                        <div class="mt-2">
                            <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Kualitas Buruk!</span>
                        </div>
                    @elseif($aqCategory === 'SEDANG')
                        <div class="mt-2">
                            <span class="badge bg-info text-dark"><i class="fas fa-info-circle me-1"></i>Kualitas Sedang</span>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div id="sensor-detail-section" class="mb-4">
        <div class="row g-4">
        <div class="col-12">
            <div class="card card-custom p-3" id="card-dht22" style="display: none;">
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 fw-semibold">Sensor DHT22</h6>
                        <small class="text-muted">Suhu dan kelembapan</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="hideSensorDetail('dht22')"><i class="fas fa-times"></i></button>
                </div>
                <div id="detail-dht22" class="sensor-detail-content">
                    <div class="d-flex gap-3 align-items-center mb-3">
                        <div class="circle-indicator border-danger" id="dht22-temp-indicator">
                            @if($latestData)
                                {{ ($setting->temperature_unit ?? 'celsius') === 'fahrenheit' ? round(($latestData->temperature * 9/5) + 32, 1).'°F' : $latestData->temperature.'°C' }}
                            @else
                                --
                            @endif
                        </div>
                        <div class="indicator-side" id="temp-indicator-side"></div>
                        <div class="circle-indicator border-primary" id="dht22-hum-indicator"></div>
                        <div class="indicator-side" id="hum-indicator-side"></div>
                        <div>
                            <div class="text-muted">Suhu sekarang</div>
                            <div class="fs-4 fw-bold"><span id="detail-temp-current">{{ $latestData->temperature ?? '--' }}</span> <span id="detail-temp-unit">°{{ strtoupper(substr($setting->temperature_unit ?? 'celsius', 0, 1)) }}</span></div>
                            <div class="text-muted">Kelembapan sekarang</div>
                            <div class="fs-5 fw-semibold"><span id="detail-hum-current">{{ $latestData->humidity ?? '--' }}</span>%</div>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">
                            <div class="p-3 rounded-3 bg-light">
                                <div class="text-muted">Rata-rata Suhu</div>
                                <div class="fs-5 fw-bold" id="detail-temp-average">{{ $avgTemp ?? '--' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 bg-light">
                                <div class="text-muted">Rata-rata Kelembapan</div>
                                <div class="fs-5 fw-bold" id="detail-hum-average">{{ $avgHum ?? '--' }}</div>
                            </div>
                        </div>
                    </div>
                    <p class="mb-0 text-muted">DHT22 adalah sensor suhu dan kelembapan yang umum dipakai untuk aplikasi monitoring lingkungan. Sensor ini mengukur suhu udara dan kelembapan relatif secara langsung.</p>
                    <div class="mt-3">
                        <div class="text-muted mb-2">Distribusi kategori suhu & kelembapan:</div>
                        <div class="row g-2">
                            <div class="col-6 col-md-4">
                                <div class="p-2 rounded-3 bg-light text-center">
                                    <div class="text-muted">Suhu ≤ 25</div>
                                    <div class="fs-6 fw-bold" id="temp-range-low-percent">--%</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-2 rounded-3 bg-light text-center">
                                    <div class="text-muted">Suhu 25-30</div>
                                    <div class="fs-6 fw-bold" id="temp-range-mid-percent">--%</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-2 rounded-3 bg-light text-center">
                                    <div class="text-muted">Suhu > 30</div>
                                    <div class="fs-6 fw-bold" id="temp-range-high-percent">--%</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-2 rounded-3 bg-light text-center">
                                    <div class="text-muted">Kelembapan ≤ 30</div>
                                    <div class="fs-6 fw-bold" id="hum-range-low-percent">--%</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-2 rounded-3 bg-light text-center">
                                    <div class="text-muted">Kelembapan 31-75</div>
                                    <div class="fs-6 fw-bold" id="hum-range-mid-percent">--%</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-2 rounded-3 bg-light text-center">
                                    <div class="text-muted">Kelembapan > 75</div>
                                    <div class="fs-6 fw-bold" id="hum-range-high-percent">--%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <div class="row g-4">
        <div class="col-12">
            <div class="card card-custom p-3" id="card-mq135" style="display: none;">
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 fw-semibold">Sensor MQ-135</h6>
                        <small class="text-muted">Kualitas udara</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="hideSensorDetail('mq135')"><i class="fas fa-times"></i></button>
                </div>
                <div id="detail-mq135" class="sensor-detail-content">
                    <div class="d-flex gap-3 align-items-center mb-3">
                        <div class="circle-indicator border-warning" id="mq135-indicator"></div>
                        <div>
                            <div class="text-muted">Kualitas udara sekarang</div>
                            <div class="fs-3 fw-bold"><span id="detail-air-current">{{ $latestData->air_quality ?? '--' }}</span> <span class="fs-6 text-muted">nilai ADC</span></div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mb-3">
                        <span id="air-status-badge" class="badge bg-primary">Status udara</span>
                    </div>
                    <div class="p-3 rounded-3 bg-light mb-3">
                        <div class="text-muted">Rata-rata Kualitas Udara</div>
                        <div class="fs-5 fw-bold" id="detail-air-average">{{ $avgAir ?? '--' }}</div>
                    </div>
                    <p class="mb-0 text-muted">MQ-135 digunakan untuk mendeteksi polutan udara dan gas berbahaya. Nilai ADC dari sensor dibandingkan dengan batas pengaturan untuk menentukan kategori kualitas udara.</p>
                    <div class="mt-3">
                        <div class="text-muted mb-2">Keterangan warna:</div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-success">BAIK (≤ batas bawah)</span>
                            <span class="badge bg-warning text-dark">SEDANG (antara batas bawah & atas)</span>
                            <span class="badge bg-danger">BURUK (> batas atas)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>

    {{-- Control Panel & Settings (hidden for guests) --}}
    <div id="section-control" class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card card-custom h-100">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-toggle-on text-primary me-2"></i>Control Panel</span>
                    @guest
                    <span class="badge bg-warning text-dark"><i class="fas fa-lock me-1"></i>Login diperlukan</span>
                    @endguest
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <label class="form-label fw-medium mb-0">Mode Kipas</label>
                            <div class="form-text">Pilih mode operasi kipas: AUTO atau MANUAL.</div>
                        </div>
                        <div>
                            @auth
                            <div class="btn-group" role="group" aria-label="Fan mode" id="fan-mode-group">
                                <button type="button" id="fan-mode-auto" class="btn btn-outline-primary" onclick="setFanMode('auto')">AUTO</button>
                                <button type="button" id="fan-mode-manual" class="btn btn-outline-secondary" onclick="setFanMode('manual')">MANUAL</button>
                            </div>
                            @else
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary" disabled>AUTO</button>
                                <button type="button" class="btn btn-outline-secondary" disabled>MANUAL</button>
                            </div>
                            @endauth
                        </div>
                    </div>

                    <div id="fan-manual-controls" class="d-none">
                        <div class="d-flex align-items-center justify-content-between gap-3 p-3 border rounded-3 bg-light mb-3">
                            <div>
                                <div class="text-muted">Kontrol kipas saat mode MANUAL aktif</div>
                                <div class="fw-semibold" id="fan-status-text">Status kipas: {{ ($setting->fan_status ?? false) ? 'ON' : 'OFF' }}</div>
                            </div>
                            <div class="btn-group" role="group">
                                <button type="button" id="fan-on-btn" class="btn btn-success" onclick="toggleFanPower(true)">Fan ON</button>
                                <button type="button" id="fan-off-btn" class="btn btn-danger" onclick="toggleFanPower(false)">Fan OFF</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-custom h-100">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-gear text-primary me-2"></i>Pengaturan Batas Sensor</span>
                    @guest
                    <span class="badge bg-warning text-dark"><i class="fas fa-lock me-1"></i>Login diperlukan</span>
                    @endguest
                </div>
                <div class="card-body">
                    @auth
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-medium">Satuan Suhu</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="temperature_unit" id="unit-celsius" value="celsius"
                                        {{ ($setting->temperature_unit ?? 'celsius') === 'celsius' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="unit-celsius">Celsius (°C)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="temperature_unit" id="unit-fahrenheit" value="fahrenheit"
                                        {{ ($setting->temperature_unit ?? 'celsius') === 'fahrenheit' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="unit-fahrenheit">Fahrenheit (°F)</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">
                                Batas Suhu Maksimal
                                <span id="settings-unit-label">({{ ($setting->temperature_unit ?? 'celsius') === 'fahrenheit' ? '°F' : '°C' }})</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-temperature-high text-danger"></i></span>
                                <input type="number" step="0.1" name="max_temperature" class="form-control" value="{{ $setting->max_temperature ?? 30.0 }}" required>
                            </div>
                            <div class="form-text">Jika suhu melebihi batas ini, sistem memicu peringatan.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium">Batas Kualitas Udara MQ-135 (nilai ADC)</label>
                            <div class="row g-2">
                                <div class="col">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fa-solid fa-arrow-down text-success"></i></span>
                                        <input type="number" name="min_air_quality" class="form-control" value="{{ $setting->min_air_quality ?? 100 }}" required>
                                    </div>
                                    <div class="form-text">Batas bawah (nilai ≤ ini = BAIK)</div>
                                </div>
                                <div class="col">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fa-solid fa-arrow-up text-danger"></i></span>
                                        <input type="number" name="max_air_quality" class="form-control" value="{{ $setting->max_air_quality ?? 500 }}" required>
                                    </div>
                                    <div class="form-text">Batas atas (nilai > ini = BURUK)</div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 rounded-3">
                            <i class="fa-solid fa-floppy-disk me-2"></i>SIMPAN PENGATURAN
                        </button>
                    </form>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-lock fa-2x mb-3 d-block opacity-30"></i>
                        <p>Silakan <a href="{{ route('login') }}">login</a> untuk mengubah pengaturan sensor.</p>
                    </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    {{-- Realtime Charts --}}
    <div class="card card-custom mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-sm-3">
                    <label class="form-label">Jumlah titik</label>
                    <select id="chart-count" class="form-select" onchange="renderChartData()">
                        <option value="24">24 terakhir</option>
                        <option value="12">12 terakhir</option>
                        <option value="6">6 terakhir</option>
                        <option value="all">Semua</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label class="form-label">Filter grafik</label>
                    <select id="chart-filter-type" class="form-select" onchange="updateChartFilterInputs()">
                        <option value="none">Tanpa filter</option>
                        <option value="day">Hari</option>
                        <option value="hour">Jam</option>
                        <option value="temp-min">Suhu min</option>
                        <option value="temp-max">Suhu max</option>
                    </select>
                </div>
                <div class="col-sm-4" id="chart-filter-input-wrapper">
                    <label class="form-label invisible">Filter input</label>
                    <input type="text" id="chart-filter-value" class="form-control" placeholder="Masukkan filter" disabled>
                </div>
                <div class="col-sm-2 text-end">
                    <button type="button" class="btn btn-primary w-100" onclick="renderChartData()">Terapkan</button>
                </div>
            </div>
        </div>
    </div>
    <div id="section-charts" class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card card-custom">
                <div class="card-header card-header-custom border-0 pb-0">
                    <i class="fas fa-fire text-danger me-2"></i>Grafik Suhu (24 Jam)
                </div>
                <div class="card-body">
                    <canvas id="chartTemp" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom">
                <div class="card-header card-header-custom border-0 pb-0">
                    <i class="fas fa-tint text-info me-2"></i>Grafik Kelembapan (24 Jam)
                </div>
                <div class="card-body">
                    <canvas id="chartHum" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-custom">
                <div class="card-header card-header-custom border-0 pb-0">
                    <i class="fas fa-wind text-warning me-2"></i>Grafik Udara (24 Jam)
                </div>
                <div class="card-body">
                    <canvas id="chartAir" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Historis --}}
    <div id="section-history" class="card card-custom mb-4">
        <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-table-list text-primary me-2"></i>Data Historis Terakhir</span>
            <button class="btn btn-sm btn-outline-primary" onclick="fetchData()"><i class="fa-solid fa-rotate-right me-1"></i>Refresh</button>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end mb-3">
                <div class="col-sm-2">
                    <label class="form-label">Jumlah data</label>
                    <input type="number" id="history-count" class="form-control" min="1" max="500" value="10" oninput="renderHistoryTable()">
                </div>
                <div class="col-sm-3">
                    <label class="form-label">Filter</label>
                    <select id="history-filter-type" class="form-select" onchange="updateHistoryFilterInputs()">
                        <option value="none">Tanpa filter</option>
                        <option value="day">Hari</option>
                        <option value="hour">Jam</option>
                        <option value="temp-min">Suhu min</option>
                        <option value="temp-max">Suhu max</option>
                        <option value="hum-min">Kelembapan min</option>
                        <option value="hum-max">Kelembapan max</option>
                        <option value="air-min">Udara min</option>
                        <option value="air-max">Udara max</option>
                    </select>
                </div>
                <div class="col-sm-4" id="history-filter-input-wrapper">
                    <label class="form-label invisible">Filter input</label>
                    <input type="text" id="history-filter-value" class="form-control" placeholder="Masukkan filter" disabled oninput="renderHistoryTable()">
                </div>
                <div class="col-sm-3">
                    <label class="form-label">Urutkan</label>
                    <select id="history-sort" class="form-select" onchange="renderHistoryTable()">
                        <option value="default">Terbaru</option>
                        <option value="oldest">Paling Lama</option>
                        <option value="temp-asc">Suhu Terendah (Min)</option>
                        <option value="temp-desc">Suhu Tertinggi (Max)</option>
                        <option value="hum-asc">Kelembapan Terendah (Min)</option>
                        <option value="hum-desc">Kelembapan Tertinggi (Max)</option>
                        <option value="air-asc">ADC Terendah (Min)</option>
                        <option value="air-desc">ADC Tertinggi (Max)</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Waktu</th>
                            <th>Suhu</th>
                            <th>Kelembapan (%)</th>
                            <th>Kualitas Udara (ADC)</th>
                        </tr>
                    </thead>
                    <tbody id="history-table">
                        @forelse($history as $item)
                        <tr>
                            <td class="ps-4">{{ $item->created_at->format('d M Y, H:i:s') }}</td>
                            <td>
                                <span class="badge {{ $item->temperature > ($setting->max_temperature ?? 30) ? 'bg-danger' : 'bg-primary' }} bg-opacity-10 text-dark border">
                                    {{ $item->temperature }}°C
                                </span>
                            </td>
                            <td>{{ $item->humidity }}</td>
                            <td>
                                @php
                                    $minA = $setting->min_air_quality ?? 0;
                                    $maxA = $setting->max_air_quality ?? 500;
                                    if ($item->air_quality <= $minA) {
                                        $airClass = 'bg-success';
                                    } elseif ($item->air_quality <= $maxA) {
                                        $airClass = 'bg-warning';
                                    } else {
                                        $airClass = 'bg-danger';
                                    }
                                @endphp
                                <span class="badge {{ $airClass }} bg-opacity-10 text-dark border">
                                    {{ $item->air_quality }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-database mb-2 d-block opacity-30 fa-2x"></i>
                                Belum ada data sensor masuk.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Current temperature unit (from server)
    let currentUnit = '{{ $setting->temperature_unit ?? "celsius" }}';
    let isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};
    let currentFanAutoMode = @json($setting->fan_auto_mode ?? true);

    function updateFanModeButtons() {
        const btnAuto = document.getElementById('fan-mode-auto');
        const btnManual = document.getElementById('fan-mode-manual');
        if (!btnAuto || !btnManual) return;
        if (currentFanAutoMode) {
            btnAuto.classList.remove('btn-outline-primary'); btnAuto.classList.add('btn-primary');
            btnManual.classList.remove('btn-secondary'); btnManual.classList.add('btn-outline-secondary');
        } else {
            btnManual.classList.remove('btn-outline-secondary'); btnManual.classList.add('btn-secondary');
            btnAuto.classList.remove('btn-primary'); btnAuto.classList.add('btn-outline-primary');
        }
    }

    async function setFanMode(mode) {
        if (!isLoggedIn) { alert('Silakan login untuk mengubah mode kipas.'); return; }
        try {
            const res = await fetch('{{ route('settings.fan.mode') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ mode })
            });

            if (!res.ok) throw new Error('Network error');
            const data = await res.json();
            if (data.success) {
                currentFanAutoMode = (data.mode === 'auto');
                updateFanModeButtons();
                updateFanControlUI();
            } else {
                alert('Gagal mengubah mode kipas.');
            }
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan saat mengubah mode kipas.');
        }
    }

    // ---- Chart Setup ----
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { display: false },
            y: { beginAtZero: false, grid: { color: 'rgba(0,0,0,0.04)' } }
        },
        elements: {
            line: { tension: 0.4 },
            point: { radius: 0, hitRadius: 10, hoverRadius: 4 }
        }
    };

    let tempChart, humChart, airChart;

    const tempCanvasEl = document.getElementById('chartTemp');
    if (tempCanvasEl) {
        const ctxTemp = tempCanvasEl.getContext('2d');
        tempChart = new Chart(ctxTemp, {
            type: 'line',
            data: { labels: [], datasets: [{ label: 'Suhu', data: [], borderColor: '#e74c3c', backgroundColor: 'rgba(231, 76, 60, 0.08)', fill: true, borderWidth: 2 }] },
            options: commonOptions
        });
    }

    const humCanvasEl = document.getElementById('chartHum');
    if (humCanvasEl) {
        const ctxHum = humCanvasEl.getContext('2d');
        humChart = new Chart(ctxHum, {
            type: 'line',
            data: { labels: [], datasets: [{ label: 'Kelembapan', data: [], borderColor: '#3498db', backgroundColor: 'rgba(52, 152, 219, 0.08)', fill: true, borderWidth: 2 }] },
            options: commonOptions
        });
    }

    const airCanvasEl = document.getElementById('chartAir');
    if (airCanvasEl) {
        const ctxAir = airCanvasEl.getContext('2d');
        airChart = new Chart(ctxAir, {
            type: 'line',
            data: { labels: [], datasets: [{ label: 'Kualitas Udara', data: [], borderColor: '#f39c12', backgroundColor: 'rgba(243, 156, 18, 0.08)', fill: true, borderWidth: 2 }] },
            options: commonOptions
        });
    }

    // Initialize chart data from PHP
    let initialData = @json($history->reverse()->values());
    window.latestDataSet = initialData;
    let avgTemperature = {{ $avgTemp ?? 'null' }};
    let avgHumidity = {{ $avgHum ?? 'null' }};
    let avgAirQuality = {{ $avgAir ?? 'null' }};
    let latestTempRaw = {{ $latestData->temperature ?? 'null' }};
    let currentFanStatus = {{ ($setting->fan_status ?? false) ? 'true' : 'false' }};
    let maxTemperatureSetting = {{ $setting->max_temperature ?? 30 }};
    let minAirQualitySetting = {{ $setting->min_air_quality ?? 100 }};
    let maxAirQualitySetting = {{ $setting->max_air_quality ?? 500 }};
    // Load persisted filter settings
    const savedFilter = JSON.parse(localStorage.getItem('dashboardFilter')) || {};
    if (savedFilter.type) document.getElementById('history-filter-type').value = savedFilter.type;
    if (savedFilter.value) document.getElementById('history-filter-value').value = savedFilter.value;
    if (savedFilter.sort) document.getElementById('history-sort').value = savedFilter.sort;


    // Initialize display unit and update charts / history table accordingly
    updateUnitDisplay(currentUnit);

    // Initialize fan mode buttons state
    updateFanModeButtons();
    updateFanControlUI();
    updateSensorDetails(initialData.length ? initialData[initialData.length - 1] : null);
    updateHistoryFilterInputs();

    function celsiusToFahrenheit(c) {
        return Math.round(((c * 9/5) + 32) * 10) / 10;
    }

    function updateCharts(data = window.latestDataSet) {
        const filtered = filterChartData(data);
        if (!filtered || filtered.length === 0) return;

        const countValue = document.getElementById('chart-count') ? document.getElementById('chart-count').value : '24';
        const chartData = countValue === 'all' ? filtered : filtered.slice(Math.max(filtered.length - parseInt(countValue), 0));

        const labels = chartData.map(item => {
            let d = new Date(item.created_at);
            return d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
        });

        const temps = chartData.map(item => {
            let t = parseFloat(item.temperature);
            return currentUnit === 'fahrenheit' ? celsiusToFahrenheit(t) : t;
        });
        const hums = chartData.map(item => item.humidity);
        const airs = chartData.map(item => item.air_quality);

        if (tempChart) {
            tempChart.data.labels = labels;
            tempChart.data.datasets[0].data = temps;
            tempChart.update();
        }
        if (humChart) {
            humChart.data.labels = labels;
            humChart.data.datasets[0].data = hums;
            humChart.update();
        }
        if (airChart) {
            airChart.data.labels = labels;
            airChart.data.datasets[0].data = airs;
            airChart.update();
        }
    }

    function updateRangePercentages(data = window.latestDataSet) {
        const rows = Array.isArray(data) ? data : [];
        const total = rows.length;
        const counts = {
            tempLow: 0,
            tempMid: 0,
            tempHigh: 0,
            humLow: 0,
            humMid: 0,
            humHigh: 0
        };

        rows.forEach(item => {
            const temp = parseFloat(item.temperature);
            const hum = parseFloat(item.humidity);
            if (!Number.isFinite(temp) || !Number.isFinite(hum)) return;

            if (temp <= 25) counts.tempLow += 1;
            else if (temp <= 30) counts.tempMid += 1;
            else counts.tempHigh += 1;

            if (hum <= 30) counts.humLow += 1;
            else if (hum <= 75) counts.humMid += 1;
            else counts.humHigh += 1;
        });

        const percentText = count => total > 0 ? ((count / total) * 100).toFixed(1) + '%' : '--%';
        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.innerText = value;
        };

        setText('temp-range-low-percent', percentText(counts.tempLow));
        setText('temp-range-mid-percent', percentText(counts.tempMid));
        setText('temp-range-high-percent', percentText(counts.tempHigh));
        setText('hum-range-low-percent', percentText(counts.humLow));
        setText('hum-range-mid-percent', percentText(counts.humMid));
        setText('hum-range-high-percent', percentText(counts.humHigh));
    }

    function updateSensorDetails(latest) {
        const tempC = latest && latest.temperature !== null ? parseFloat(latest.temperature) : null;
        const hum = latest ? latest.humidity : '--';
        const air = latest ? latest.air_quality : '--';

        if (tempC !== null) {
            const displayTemp = currentUnit === 'fahrenheit' ? celsiusToFahrenheit(tempC) : tempC;
            document.getElementById('detail-temp-current').innerText = displayTemp;
            document.getElementById('detail-temp-unit').innerText = '°' + (currentUnit === 'fahrenheit' ? 'F' : 'C');

            const tempIndicator = document.getElementById('dht22-temp-indicator');
            if (tempIndicator) {
                tempIndicator.innerText = displayTemp + '°' + (currentUnit === 'fahrenheit' ? 'F' : 'C');
            }

            applyIndicatorColors(tempC, parseFloat(hum), parseFloat(air));
        }

        document.getElementById('detail-hum-current').innerText = hum;
        document.getElementById('detail-air-current').innerText = air;

        document.getElementById('detail-temp-average').innerText = avgTemperature !== null ? (currentUnit === 'fahrenheit' ? celsiusToFahrenheit(avgTemperature) : avgTemperature) : '--';
        document.getElementById('detail-hum-average').innerText = avgHumidity !== null ? avgHumidity : '--';
        document.getElementById('detail-air-average').innerText = avgAirQuality !== null ? avgAirQuality : '--';

        const dhtHumIndicator = document.getElementById('dht22-hum-indicator');
        const mqIndicator = document.getElementById('mq135-indicator');
        if (dhtHumIndicator) dhtHumIndicator.innerText = hum !== '--' ? hum + '%' : '--';
        if (mqIndicator) mqIndicator.innerText = air !== '--' ? air : '--';

        // Update air quality category display on metric card
        const airCategoryEl = document.getElementById('air-status-category');
        if (airCategoryEl && air !== '--') {
            const airVal = parseFloat(air);
            if (airVal <= minAirQualitySetting) {
                airCategoryEl.innerText = 'BAIK';
                airCategoryEl.style.color = '#10b981';
            } else if (airVal <= maxAirQualitySetting) {
                airCategoryEl.innerText = 'SEDANG';
                airCategoryEl.style.color = '#f59e0b';
            } else {
                airCategoryEl.innerText = 'BURUK';
                airCategoryEl.style.color = '#ef4444';
            }
        }
    }

    function toggleUnit() {
        const nextUnit = currentUnit === 'celsius' ? 'fahrenheit' : 'celsius';
        updateUnitDisplay(nextUnit);
        if (isLoggedIn) {
            changeUnit(nextUnit);
        }
    }

    function renderHistoryTable() {
        const count = parseInt(document.getElementById('history-count').value) || 24;
        const filterType = document.getElementById('history-filter-type').value;
        const filterValue = document.getElementById('history-filter-value').value.trim();

        let data = window.latestDataSet.slice();
        if (filterType !== 'none' && filterValue !== '') {
            data = data.filter(item => {
                const date = new Date(item.created_at);
                if (filterType === 'day') {
                    return item.created_at.startsWith(filterValue);
                }
                if (filterType === 'hour') {
                    return date.getHours().toString().padStart(2, '0') === filterValue.padStart(2, '0');
                }
                if (filterType === 'temp-min') {
                    return parseFloat(item.temperature) >= parseFloat(filterValue);
                }
                if (filterType === 'temp-max') {
                    return parseFloat(item.temperature) <= parseFloat(filterValue);
                }
                if (filterType === 'hum-min') {
                    return parseFloat(item.humidity) >= parseFloat(filterValue);
                }
                if (filterType === 'hum-max') {
                    return parseFloat(item.humidity) <= parseFloat(filterValue);
                }
                if (filterType === 'air-min') {
                    return parseFloat(item.air_quality) >= parseFloat(filterValue);
                }
                if (filterType === 'air-max') {
                    return parseFloat(item.air_quality) <= parseFloat(filterValue);
                }
                return true;
            });
        }

        // Apply sorting
        const sortType = document.getElementById('history-sort').value;
        const sortFunctions = {
            'default': (a, b) => new Date(b.created_at) - new Date(a.created_at),  // Newest first
            'oldest': (a, b) => new Date(a.created_at) - new Date(b.created_at),
            'temp-asc': (a, b) => parseFloat(a.temperature) - parseFloat(b.temperature),
            'temp-desc': (a, b) => parseFloat(b.temperature) - parseFloat(a.temperature),
            'hum-asc': (a, b) => parseFloat(a.humidity) - parseFloat(b.humidity),
            'hum-desc': (a, b) => parseFloat(b.humidity) - parseFloat(a.humidity),
            'air-asc': (a, b) => parseFloat(a.air_quality) - parseFloat(b.air_quality),
            'air-desc': (a, b) => parseFloat(b.air_quality) - parseFloat(a.air_quality)
        };
        if (sortFunctions[sortType]) data.sort(sortFunctions[sortType]);

        // Persist sort preference to localStorage
        const saved = {
            type: document.getElementById('history-filter-type').value,
            value: document.getElementById('history-filter-value').value,
            sort: sortType
        };
        localStorage.setItem('dashboardFilter', JSON.stringify(saved));

        const sliced = data.slice(0, count);
        const rows = sliced.map(item => {
            const temp = currentUnit === 'fahrenheit' ? celsiusToFahrenheit(parseFloat(item.temperature)) : item.temperature;
            const tempSuffix = currentUnit === 'fahrenheit' ? '°F' : '°C';
            return `
                <tr>
                    <td class="ps-4">${new Date(item.created_at).toLocaleString('id-ID', { year:'numeric', month:'2-digit', day:'2-digit', hour:'2-digit', minute:'2-digit', second:'2-digit' })}</td>
                    <td><span class="badge ${parseFloat(item.temperature) < 25 ? 'bg-primary' : (parseFloat(item.temperature) <= 30 ? 'bg-warning' : 'bg-danger')} bg-opacity-10 text-dark border">${temp}${tempSuffix}</span></td>
                    <td>${item.humidity}<span class="badge ms-2 ${parseFloat(item.humidity) < 30 ? 'bg-danger' : (parseFloat(item.humidity) <= 75 ? 'bg-warning' : 'bg-primary')} bg-opacity-10 text-dark border"></span></td>
                    <td>
                        ${(() => {
                            const a = parseFloat(item.air_quality);
                            if (a <= minAirQualitySetting) return `<span class="badge bg-success bg-opacity-10 text-dark border">${item.air_quality}</span>`;
                            if (a <= maxAirQualitySetting) return `<span class="badge bg-warning bg-opacity-10 text-dark border">${item.air_quality}</span>`;
                            return `<span class="badge bg-danger bg-opacity-10 text-dark border">${item.air_quality}</span>`;
                        })()}
                    </td>
                </tr>
            `;
        }).join('');

        document.getElementById('history-table').innerHTML = rows || '<tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-database mb-2 d-block opacity-30 fa-2x"></i>Tidak ada data sesuai filter.</td></tr>';
        updateCharts();
        // Hitung persentase dari SEMUA data 24 jam, bukan dari filtered data
        updateRangePercentages(window.latestDataSet);
    }

    function updateHistoryFilterInputs() {
        const filterType = document.getElementById('history-filter-type').value;
        const filterInput = document.getElementById('history-filter-value');
        if (!filterInput) return;
        // Enable/disable input based on filter type
        if (filterType === 'none') {
            filterInput.value = '';
            filterInput.disabled = true;
            filterInput.placeholder = 'Masukkan filter';
        } else {
            filterInput.disabled = false;
            const placeholders = {
                'day': 'Format YYYY-MM-DD',
                'hour': 'Format HH (00-23)',
                'temp-min': 'Minimum suhu',
                'temp-max': 'Maksimum suhu',
                'hum-min': 'Minimum kelembapan (%)',
                'hum-max': 'Maksimum kelembapan (%)',
                'air-min': 'Min ADC',
                'air-max': 'Max ADC'
            };
            filterInput.placeholder = placeholders[filterType] || '';
        }
        // Persist filter settings
        const saved = {
            type: filterType,
            value: filterInput.value,
            sort: document.getElementById('history-sort') ? document.getElementById('history-sort').value : ''
        };
        localStorage.setItem('dashboardFilter', JSON.stringify(saved));
        renderHistoryTable();
    }

    function updateChartFilterInputs() {
        const filterType = document.getElementById('chart-filter-type').value;
        const filterInput = document.getElementById('chart-filter-value');
        if (!filterInput) return;
        if (filterType === 'none') {
            filterInput.value = '';
            filterInput.disabled = true;
            filterInput.placeholder = 'Masukkan filter';
        } else {
            filterInput.disabled = false;
            const placeholders = {
                'day': 'Format YYYY-MM-DD',
                'hour': 'Format HH (00-23)',
                'temp-min': 'Minimum suhu',
                'temp-max': 'Maksimum suhu',
                'hum-min': 'Minimum kelembapan (%)',
                'hum-max': 'Maksimum kelembapan (%)',
                'air-min': 'Min ADC',
                'air-max': 'Max ADC'
            };
            filterInput.placeholder = placeholders[filterType] || '';
        }
    }

    function renderChartData() {
        updateCharts(window.latestDataSet);
    }

    function filterChartData(data) {
        const filterType = document.getElementById('chart-filter-type').value;
        const filterValue = document.getElementById('chart-filter-value').value.trim();
        if (!filterValue || filterType === 'none') return data;
        return data.filter(item => {
            const date = new Date(item.created_at);
            const temp = parseFloat(item.temperature);
            const hum = parseFloat(item.humidity);
            const air = parseFloat(item.air_quality);
            switch (filterType) {
                case 'day': return item.created_at.slice(0, 10) === filterValue;
                case 'hour': return date.getHours().toString().padStart(2, '0') === filterValue.padStart(2, '0');
                case 'temp-min': return temp >= parseFloat(filterValue);
                case 'temp-max': return temp <= parseFloat(filterValue);
                case 'hum-min': return hum >= parseFloat(filterValue);
                case 'hum-max': return hum <= parseFloat(filterValue);
                case 'air-min': return air >= parseFloat(filterValue);
                case 'air-max': return air <= parseFloat(filterValue);
                default: return true;
            }
        });
    }

    function applyIndicatorColors(temp, hum, air) {
        const tempBadge = document.getElementById('temp-status-badge');
        const humBadge = document.getElementById('hum-status-badge');
        const airBadge = document.getElementById('air-status-badge');
        const tempIndicator = document.getElementById('dht22-temp-indicator');
        const humIndicator = document.getElementById('dht22-hum-indicator');
        const mqIndicator = document.getElementById('mq135-indicator');

        if (tempBadge) {
            if (temp <= 25) {
                tempBadge.className = 'badge bg-primary';
                tempBadge.innerText = 'Suhu: Normal';
            } else if (temp <= 30) {
                tempBadge.className = 'badge bg-warning text-dark';
                tempBadge.innerText = 'Suhu: Siaga';
            } else {
                tempBadge.className = 'badge bg-danger';
                tempBadge.innerText = 'Suhu: Panas';
            }
        }

        if (humBadge) {
            if (hum <= 30) {
                humBadge.className = 'badge bg-primary';
                humBadge.innerText = 'Kelembapan: Rendah';
            } else if (hum <= 75) {
                humBadge.className = 'badge bg-warning text-dark';
                humBadge.innerText = 'Kelembapan: Normal';
            } else {
                humBadge.className = 'badge bg-danger';
                humBadge.innerText = 'Kelembapan: Tinggi';
            }
        }

        if (airBadge) {
            if (!Number.isFinite(air)) {
                airBadge.className = 'badge bg-secondary';
                airBadge.innerText = 'Udara: --';
            } else if (air <= minAirQualitySetting) {
                airBadge.className = 'badge bg-success';
                airBadge.innerText = 'Udara: Baik';
            } else if (air <= maxAirQualitySetting) {
                airBadge.className = 'badge bg-warning text-dark';
                airBadge.innerText = 'Udara: Sedang';
            } else {
                airBadge.className = 'badge bg-danger';
                airBadge.innerText = 'Udara: Buruk';
            }
        }

        if (tempIndicator) {
            tempIndicator.style.borderColor = temp <= 25 ? '#3b82f6' : temp <= 30 ? '#f59e0b' : '#ef4444';
            tempIndicator.style.color = temp <= 25 ? '#3b82f6' : temp <= 30 ? '#f59e0b' : '#ef4444';
            tempIndicator.style.backgroundColor = temp <= 25 ? 'rgba(59,130,246,0.08)' : temp <= 30 ? 'rgba(251,191,36,0.12)' : 'rgba(239,68,68,0.12)';
        }

        if (humIndicator) {
            humIndicator.style.borderColor = hum <= 30 ? '#3b82f6' : hum <= 75 ? '#f59e0b' : '#ef4444';
            humIndicator.style.color = hum <= 30 ? '#3b82f6' : hum <= 75 ? '#f59e0b' : '#ef4444';
            humIndicator.style.backgroundColor = hum <= 30 ? 'rgba(59,130,246,0.08)' : hum <= 75 ? 'rgba(251,191,36,0.12)' : 'rgba(239,68,68,0.12)';
        }

        if (mqIndicator) {
            if (!Number.isFinite(air)) {
                mqIndicator.style.borderColor = '#6b7280';
                mqIndicator.style.color = '#6b7280';
                mqIndicator.style.backgroundColor = 'rgba(107,114,128,0.08)';
            } else if (air <= minAirQualitySetting) {
                mqIndicator.style.borderColor = '#10b981';
                mqIndicator.style.color = '#10b981';
                mqIndicator.style.backgroundColor = 'rgba(16,185,129,0.08)';
            } else if (air <= maxAirQualitySetting) {
                mqIndicator.style.borderColor = '#f59e0b';
                mqIndicator.style.color = '#f59e0b';
                mqIndicator.style.backgroundColor = 'rgba(251,191,36,0.12)';
            } else {
                mqIndicator.style.borderColor = '#ef4444';
                mqIndicator.style.color = '#ef4444';
                mqIndicator.style.backgroundColor = 'rgba(239,68,68,0.12)';
            }
        }
    }

    function updateFanControlUI() {
        const manualControls = document.getElementById('fan-manual-controls');
        const statusText = document.getElementById('fan-status-text');
        const fanOnBtn = document.getElementById('fan-on-btn');
        const fanOffBtn = document.getElementById('fan-off-btn');

        if (manualControls) {
            manualControls.classList.toggle('d-none', currentFanAutoMode);
        }
        if (statusText) {
            statusText.innerText = 'Status kipas: ' + (currentFanStatus ? 'ON' : 'OFF');
        }
        if (fanOnBtn && fanOffBtn) {
            fanOnBtn.disabled = currentFanAutoMode;
            fanOffBtn.disabled = currentFanAutoMode;
            fanOnBtn.className = currentFanStatus ? 'btn btn-success' : 'btn btn-outline-success';
            fanOffBtn.className = currentFanStatus ? 'btn btn-outline-danger' : 'btn btn-danger';
        }
    }

    async function toggleFanPower(status) {
        if (!isLoggedIn) {
            alert('Silakan login untuk mengontrol kipas.');
            return;
        }
        if (currentFanAutoMode) {
            alert('Nyalakan mode MANUAL untuk mengontrol fan secara langsung.');
            return;
        }

        try {
            const response = await fetch('{{ route('settings.fan') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: status })
            });
            const data = await response.json();
            if (data.success) {
                currentFanStatus = status;
                updateFanControlUI();
            }
        } catch (error) {
            console.error(error);
            alert('Gagal mengubah status kipas.');
        }
    }

    // ---- Fetch Data Periodically ----
    let lastFetchSuccess = true;

    async function fetchData() {
        try {
            const response = await fetch('{{ route('data.latest') }}');
            if (!response.ok) throw new Error("Network error");

            const res = await response.json();

            // Online status
            const statusEl = document.getElementById('device-status');
            if (statusEl) {
                statusEl.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i>Online';
                statusEl.className = 'badge bg-success status-badge';
            }
            lastFetchSuccess = true;

            // Update sensor cards
            if (res.latest) {
                let tempVal = parseFloat(res.latest.temperature);
                latestTempRaw = tempVal;
                if (currentUnit === 'fahrenheit') {
                    tempVal = celsiusToFahrenheit(tempVal);
                }
                document.getElementById('val-temp').innerText = tempVal;
                document.getElementById('val-hum').innerText = res.latest.humidity;
                document.getElementById('val-air').innerText = res.latest.air_quality;
                updateSensorDetails(res.latest);
            }

            // Update unit from server settings only for logged-in users
            if (isLoggedIn && res.setting && res.setting.temperature_unit) {
                updateUnitDisplay(res.setting.temperature_unit);
            }

            // Update Charts
            if (res.history) {
                window.latestDataSet = res.history;
                renderHistoryTable();
            }

        } catch (error) {
            console.error("Failed to fetch data", error);
            const statusEl = document.getElementById('device-status');
            if (statusEl) {
                statusEl.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i>Offline';
                statusEl.className = 'badge bg-danger status-badge';
            }
            lastFetchSuccess = false;
        }
    }

    // Poll every 5 seconds
    setInterval(fetchData, 5000);

    // ---- Bug #4 Fix: Unit toggle ----
    function updateUnitDisplay(unit) {
        currentUnit = unit;
        const label = document.getElementById('temp-unit-label');
        const toggleBtn = document.getElementById('btn-unit-toggle');
        if (label) {
            label.innerText = unit === 'fahrenheit' ? '°F' : '°C';
        }
        if (toggleBtn) {
            toggleBtn.innerText = 'Ubah ke °' + (unit === 'celsius' ? 'F' : 'C');
        }
        if (latestTempRaw !== null) {
            const displayTemp = unit === 'fahrenheit' ? celsiusToFahrenheit(latestTempRaw) : latestTempRaw;
            document.getElementById('val-temp').innerText = displayTemp;
        }
        if (document.getElementById('detail-temp-current')) {
            if (latestTempRaw !== null) {
                const displayTemp = unit === 'fahrenheit' ? celsiusToFahrenheit(latestTempRaw) : latestTempRaw;
                document.getElementById('detail-temp-current').innerText = displayTemp;

                const tempIndicator = document.getElementById('dht22-temp-indicator');
                if (tempIndicator) {
                    tempIndicator.innerText = displayTemp + '°' + (unit === 'fahrenheit' ? 'F' : 'C');
                }

                const humValue = document.getElementById('detail-hum-current') ? parseFloat(document.getElementById('detail-hum-current').innerText) : null;
                const airValue = document.getElementById('detail-air-current') ? parseFloat(document.getElementById('detail-air-current').innerText) : null;
                applyIndicatorColors(displayTemp, humValue, airValue);
            }
        }
        if (document.getElementById('detail-temp-unit')) {
            document.getElementById('detail-temp-unit').innerText = '°' + (unit === 'fahrenheit' ? 'F' : 'C');
        }
        // Initialize chart filter UI on page load with persisted values
        updateChartFilterInputs();
        renderHistoryTable();
    }

    async function changeUnit(unit) {
        if (!isLoggedIn) {
            alert('Silakan login untuk mengubah satuan suhu.');
            return;
        }
        try {
            const response = await fetch('{{ route('settings.unit') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ unit: unit })
            });

            const data = await response.json();
            if (data.success) {
                updateUnitDisplay(unit);
                // Re-fetch immediately to update display
                fetchData();
            }
        } catch(e) {
            alert('Gagal mengubah satuan suhu.');
        }
    }

    // ---- Bug #2 Fix: Toggle LED (was missing the controller method) ----
    async function toggleLed(led, status) {
        if (!isLoggedIn) {
            alert('Silakan login untuk mengontrol LED.');
            return;
        }
        try {
            const response = await fetch('{{ route('settings.led') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ led: led, status: status })
            });

            const data = await response.json();
            if (data.success) {
                // Update UI without full page reload
                const isOn = status === 1;
                if (led === 'red') {
                    document.getElementById('status-red-text').innerText = 'Status: ' + (isOn ? 'Menyala' : 'Mati');
                    document.getElementById('btn-led-red-on').className = isOn ? 'btn btn-danger btn-toggle me-2' : 'btn btn-outline-danger btn-toggle me-2';
                    document.getElementById('btn-led-red-off').className = isOn ? 'btn btn-outline-secondary btn-toggle' : 'btn btn-secondary btn-toggle';
                } else {
                    document.getElementById('status-green-text').innerText = 'Status: ' + (isOn ? 'Menyala' : 'Mati');
                    document.getElementById('btn-led-green-on').className = isOn ? 'btn btn-success btn-toggle me-2' : 'btn btn-outline-success btn-toggle me-2';
                    document.getElementById('btn-led-green-off').className = isOn ? 'btn btn-outline-secondary btn-toggle' : 'btn btn-secondary btn-toggle';
                }
            }
        } catch(e) {
            alert("Gagal mengubah status LED.");
        }
    }

    // Update settings form unit label when radio changes
    document.querySelectorAll('input[name="temperature_unit"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const lbl = document.getElementById('settings-unit-label');
            if (lbl) lbl.innerText = this.value === 'fahrenheit' ? '(°F)' : '(°C)';
        });
    });
</script>
@endpush
