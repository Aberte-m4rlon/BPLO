@extends('layouts.app')

@section('title', 'BPLO Personnel Portal')

@php use App\Models\Application; @endphp

@section('content')
    <div class="page-header bplo-page-header bplo-dashboard-hero">
        <div class="bplo-hero-copy">
            <p class="bplo-eyebrow">MUNICIPAL PERSONNEL WORKSPACE</p>
            <h1>Good day, {{ auth()->user()->name }}</h1>
            <p>Manage application reviews and permit releases from one place.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('bplo.applications.index') }}" class="btn bplo-hero-primary"><span aria-hidden="true">▤</span> Application Review</a>
            <a href="{{ route('bplo.permits.index') }}" class="btn bplo-hero-secondary"><span aria-hidden="true">↗</span> Permit Release</a>
        </div>
    </div>

    <div class="card bplo-flow-card">
        <div class="card-head">
            <div>
                <p class="bplo-eyebrow">AT A GLANCE</p>
                <h2 class="section">Permit workflow</h2>
                <p class="muted mb-0">Follow each application from submission through permit issuance.</p>
            </div>
            <span class="bplo-live-label"><span aria-hidden="true"></span> Live workflow</span>
        </div>
        <div class="bplo-workflow">
            <a class="bplo-workflow-step" href="{{ route('bplo.applications.index', ['status' => Application::STATUS_SUBMITTED]) }}">
                <span class="step-number">01</span>
                <strong>Application submitted</strong>
                <small>{{ $counts['pending_review'] }} new</small>
            </a>
            <a class="bplo-workflow-step" href="{{ route('bplo.applications.index') }}">
                <span class="step-number">02</span>
                <strong>BPLO review</strong>
                <small>{{ $counts['pending_review'] + $counts['in_review'] }} in queue</small>
            </a>
            <a class="bplo-workflow-step" href="{{ route('bplo.applications.index', ['status' => Application::STATUS_PAYMENT_PENDING]) }}">
                <span class="step-number">03</span>
                <strong>Treasurer verifies payment</strong>
                <small>{{ $counts['awaiting_payment'] }} awaiting verification</small>
            </a>
            <a class="bplo-workflow-step" href="{{ route('bplo.permits.index', ['status' => Application::STATUS_PAYMENT_VERIFIED]) }}">
                <span class="step-number">04</span>
                <strong>BPLO releases permit</strong>
                <small>{{ $counts['paid'] }} ready to release</small>
            </a>
            <a class="bplo-workflow-step" href="{{ route('bplo.permits.index', ['status' => Application::STATUS_PERMIT_RELEASED]) }}">
                <span class="step-number">05</span>
                <strong>Permit issued</strong>
                <small>{{ $counts['released'] }} released</small>
            </a>
        </div>
    </div>

    <div class="stat-grid">
        <x-stat-card :value="$counts['total']" label="Submitted Applications" accent="mint" />
        <x-stat-card :value="$counts['pending_review']" label="New for Review" accent="amber" />
        <x-stat-card :value="$counts['in_review']" label="Under Review" accent="mint" />
        <x-stat-card :value="$counts['for_correction']" label="For Correction" accent="amber" />
        <x-stat-card :value="$counts['approved']" label="Approved for Payment" accent="green" />
        <x-stat-card :value="$counts['awaiting_payment']" label="Payment Verification" accent="amber" />
        <x-stat-card :value="$counts['paid']" label="Ready to Release" accent="green" />
        <x-stat-card :value="$counts['released']" label="Permits Released" accent="green" />
    </div>

    <div class="bplo-queue-grid">
        <section class="card">
            <div class="card-head">
                <h2 class="section">Application review queue</h2>
                <a href="{{ route('bplo.applications.index') }}" class="btn btn-sm btn-secondary">All applications</a>
            </div>
            @if ($reviewQueue->isEmpty())
                <x-empty-state icon="📋" title="Review queue is clear"
                    message="Newly submitted applications will appear here." />
            @else
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>Tracking No.</th><th>Business</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($reviewQueue as $application)
                                <tr>
                                    <td>{{ $application->tracking_number }}</td>
                                    <td>{{ $application->business_name }}</td>
                                    <td><x-status-badge :status="$application->status" /></td>
                                    <td><a href="{{ route('bplo.applications.show', $application) }}" class="btn btn-sm">Review</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section class="card">
            <div class="card-head">
                <h2 class="section">Ready for permit release</h2>
                <a href="{{ route('bplo.permits.index') }}" class="btn btn-sm btn-secondary">Release desk</a>
            </div>
            @if ($releaseQueue->isEmpty())
                <x-empty-state icon="🧾" title="No permits ready to release"
                    message="Applications appear here after the Treasurer verifies payment." />
            @else
                <div class="table-wrap">
                    <table class="data">
                        <thead><tr><th>Tracking No.</th><th>Business</th><th>Payment</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($releaseQueue as $application)
                                <tr>
                                    <td>{{ $application->tracking_number }}</td>
                                    <td>{{ $application->business_name }}</td>
                                    <td><x-status-badge :status="$application->status" /></td>
                                    <td><a href="{{ route('bplo.applications.show', $application) }}" class="btn btn-sm btn-success">Open to release</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <section class="card">
        <div class="card-head">
            <h2 class="section">Recent Applications</h2>
            <a href="{{ route('bplo.applications.index') }}" class="btn btn-sm btn-secondary">View all</a>
        </div>
        @if ($recent->isEmpty())
            <x-empty-state icon="📋" title="No applications yet"
                message="Submitted applications will appear here and enter the review workflow." />
        @else
            <div class="table-wrap">
                <table class="data">
                    <thead><tr><th>Tracking No.</th><th>Applicant</th><th>Business</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($recent as $application)
                            <tr>
                                <td>{{ $application->tracking_number }}</td>
                                <td>{{ $application->applicant_name }}</td>
                                <td>{{ $application->business_name }}</td>
                                <td><x-status-badge :status="$application->status" /></td>
                                <td>{{ $application->submitted_at?->format('M d, Y') }}</td>
                                <td><a href="{{ route('bplo.applications.show', $application) }}" class="btn btn-sm">Review</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
