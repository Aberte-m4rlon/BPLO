@extends('layouts.app')

@section('title', 'BPLO Permit Release Desk')

@php use App\Models\Application; @endphp

@section('content')
    <div class="page-header bplo-page-header">
        <div>
            <p class="bplo-eyebrow">PERMIT MANAGEMENT</p>
            <h1>Permit Release Desk</h1>
            <p class="muted mb-0">Release permits after Treasury confirms payment, or review permits already issued.</p>
        </div>
        <a href="{{ route('bplo.dashboard') }}" class="btn btn-secondary">← BPLO dashboard</a>
    </div>

    <div class="stat-grid bplo-release-stats">
        <x-stat-card :value="$readyCount" label="Ready to Release" accent="amber" />
        <x-stat-card :value="$releasedCount" label="Permits Released" accent="green" />
    </div>

    <div class="alert alert-info">
        Only payment-verified applications can be released. Releasing here creates the permit and QR verification record and updates the applicant’s application status.
    </div>

    <div class="card">
        <div class="card-head">
            <h2 class="section">Payment-verified applications and issued permits</h2>
            <a href="{{ route('bplo.applications.index') }}" class="btn btn-sm btn-secondary">Application review</a>
        </div>

        <form method="GET" action="{{ route('bplo.permits.index') }}" class="toolbar">
            <div class="form-group">
                <label for="q">Search</label>
                <input type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Tracking, applicant, business, or permit no.">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">Ready and released</option>
                    <option value="{{ Application::STATUS_PAYMENT_VERIFIED }}" @selected($status === Application::STATUS_PAYMENT_VERIFIED)>Ready to release</option>
                    <option value="{{ Application::STATUS_PERMIT_RELEASED }}" @selected($status === Application::STATUS_PERMIT_RELEASED)>Released</option>
                </select>
            </div>
            <div class="form-group bplo-filter-actions">
                <button type="submit" class="btn">Search</button>
                <a href="{{ route('bplo.permits.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Tracking No.</th><th>Applicant</th><th>Business</th><th>Status</th><th>Permit No.</th><th>Valid Until</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        <tr>
                            <td>{{ $application->tracking_number }}</td>
                            <td>{{ $application->applicant_name }}</td>
                            <td>{{ $application->business_name }}</td>
                            <td><x-status-badge :status="$application->status" /></td>
                            <td>{{ $application->permit?->permit_number ?? '—' }}</td>
                            <td>{{ $application->permit?->valid_until?->format('M d, Y') ?? '—' }}</td>
                            <td>
                                @if ($application->status === Application::STATUS_PAYMENT_VERIFIED)
                                    <form method="POST" action="{{ route('bplo.applications.release', $application) }}" onsubmit="return confirm('Release this permit now?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Release Permit</button>
                                    </form>
                                @else
                                    <a href="{{ route('bplo.applications.show', $application) }}" class="btn btn-sm btn-secondary">View record</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="muted">No payment-verified or released permits match your search.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$applications" />
    </div>
@endsection
