@extends('layouts.app')

@section('title', 'Inspector Portal')

@section('content')
    <div class="page-header inspector-page-header">
        <div>
            <p class="inspector-eyebrow">Municipal Personnel Workspace</p>
            <h1>Inspector Portal</h1>
            <p class="muted mb-0">Scan permit QR codes and monitor permit validity using records issued by BPLO.</p>
        </div>
        <a href="{{ route('inspector.permits.index') }}" class="btn">Permit Monitoring</a>
    </div>

    <div class="stat-grid">
        <x-stat-card :value="$counts['active']" label="Active Permits" icon="✅" accent="green" />
        <x-stat-card :value="$counts['expired']" label="Expired Permits" icon="⚠️" accent="amber" />
        <x-stat-card :value="$counts['revoked']" label="Revoked Permits" icon="⛔" accent="red" />
        <x-stat-card :value="$counts['scans_today']" label="Scans Today" icon="🔍" accent="mint" />
    </div>

    <div class="inspector-tools-grid">
        <section class="card">
            <div class="card-head">
                <div>
                    <h2 class="section">Scan a permit QR code</h2>
                    <p class="muted mb-0">A scan opens the system verification result and is added to the shared scan history.</p>
                </div>
            </div>
            <div class="inspector-scan-actions">
                <button type="button" id="start-qr-scan" class="btn">Start camera scanner</button>
                <button type="button" id="stop-qr-scan" class="btn btn-secondary" hidden>Stop camera</button>
            </div>
            <p id="qr-scan-status" class="inspector-scan-status" role="status" aria-live="polite">
                Camera scanning works in supported browsers over HTTPS or localhost. You can also scan with your phone’s camera or enter the code below.
            </p>
            <div id="qr-scanner-panel" class="inspector-scanner-panel" hidden>
                <video id="qr-camera" class="inspector-camera" muted playsinline></video>
            </div>

            <form method="GET" action="{{ route('verify.lookup') }}" class="inspector-manual-verify">
                <div class="form-group">
                    <label for="inspector-code">Enter permit QR code or verification link</label>
                    <input type="text" id="inspector-code" name="code" autocomplete="off" placeholder="Paste the code or QR verification link">
                </div>
                <button type="submit" class="btn btn-mint">Verify Permit</button>
            </form>
            <p class="muted inspector-scan-note">The verification result is created by the same system that applicants and the public use. Its outcome appears below in Recent Scans.</p>
        </section>

        <section class="card inspector-monitor-card">
            <div class="card-head">
                <h2 class="section">Permit monitoring</h2>
                <a href="{{ route('inspector.permits.index') }}" class="btn btn-sm btn-secondary">Open permit list</a>
            </div>
            <p>Check current validity, business details, and previous QR verification activity for any issued permit.</p>
            <ul class="inspector-monitor-list">
                <li><span class="badge badge-green">Active</span> Valid permit with an unexpired date.</li>
                <li><span class="badge badge-yellow">Expired</span> Permit is past its validity date.</li>
                <li><span class="badge badge-red">Revoked</span> Permit was withdrawn and is no longer valid.</li>
            </ul>
            <p class="muted mb-0">Permit records and QR outcomes come from the BPLO issuance and public verification flow.</p>
        </section>
    </div>

    <div class="card">
        <div class="card-head">
            <h2 class="section">Recent QR scans</h2>
            <span class="muted">{{ $counts['scans_today'] }} today</span>
        </div>
        @if ($recentScans->isEmpty())
            <x-empty-state icon="🔍" title="No scans yet"
                message="Each permit QR verification will be recorded here." />
        @else
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr><th>Time</th><th>Permit No.</th><th>Business</th><th>Result</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($recentScans as $scan)
                            <tr>
                                <td>{{ $scan->created_at?->format('M d, Y g:i A') }}</td>
                                <td>{{ $scan->permit?->permit_number ?? '—' }}</td>
                                <td>{{ $scan->permit?->business_name ?? '—' }}</td>
                                <td>
                                    @php
                                        $badge = match ($scan->result) {
                                            'VALID' => 'badge-green',
                                            'EXPIRED' => 'badge-yellow',
                                            'REVOKED', 'NOT_FOUND' => 'badge-red',
                                            default => 'badge-gray',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $scan->result }}</span>
                                </td>
                                <td>
                                    @if ($scan->permit)
                                        <a href="{{ route('inspector.permits.show', $scan->permit) }}" class="btn btn-sm btn-secondary">View permit</a>
                                    @else
                                        <span class="muted">No permit record</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <script>
        (function () {
            var startButton = document.getElementById('start-qr-scan');
            var stopButton = document.getElementById('stop-qr-scan');
            var status = document.getElementById('qr-scan-status');
            var panel = document.getElementById('qr-scanner-panel');
            var video = document.getElementById('qr-camera');
            var stream = null;
            var scanning = false;
            var verifyUrl = @json(route('verify.lookup'));

            function setStatus(message) {
                status.textContent = message;
            }

            function stopCamera() {
                scanning = false;
                if (stream) {
                    stream.getTracks().forEach(function (track) { track.stop(); });
                    stream = null;
                }
                video.srcObject = null;
                panel.hidden = true;
                startButton.hidden = false;
                stopButton.hidden = true;
            }

            async function scanFrame(detector) {
                if (!scanning) return;

                try {
                    var codes = await detector.detect(video);
                    if (codes.length && codes[0].rawValue) {
                        var scannedValue = codes[0].rawValue;
                        stopCamera();
                        setStatus('QR code detected. Opening the permit verification result…');
                        var destination = new URL(verifyUrl, window.location.origin);
                        destination.searchParams.set('code', scannedValue);
                        window.location.assign(destination.toString());
                        return;
                    }
                } catch (error) {
                    // Keep scanning; a frame may be blurry while the camera focuses.
                }

                if (scanning) window.setTimeout(function () { scanFrame(detector); }, 220);
            }

            startButton.addEventListener('click', async function () {
                if (!window.isSecureContext) {
                    setStatus('The browser blocks camera access on this connection. Open the permit QR with your phone camera, or use the code entry field below.');
                    return;
                }
                if (!('BarcodeDetector' in window) || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    setStatus('This browser does not support in-app QR scanning. Use your phone camera to open the QR code, or use the code entry field below.');
                    return;
                }

                try {
                    var detector = new BarcodeDetector({ formats: ['qr_code'] });
                    stream = await navigator.mediaDevices.getUserMedia({
                        audio: false,
                        video: { facingMode: { ideal: 'environment' } }
                    });
                    video.srcObject = stream;
                    panel.hidden = false;
                    startButton.hidden = true;
                    stopButton.hidden = false;
                    await video.play();
                    scanning = true;
                    setStatus('Camera is on. Hold the permit QR code in front of the camera.');
                    scanFrame(detector);
                } catch (error) {
                    stopCamera();
                    setStatus('Could not start the camera. Check browser permission and try again, or enter the QR code below.');
                }
            });

            stopButton.addEventListener('click', function () {
                stopCamera();
                setStatus('Camera stopped. Start it again or enter the QR code below.');
            });

            window.addEventListener('pagehide', stopCamera);
        })();
    </script>
@endsection
