<?php

namespace App\Http\Controllers\Bplo;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Permit;
use App\Services\PermitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermitController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('review', Application::class);

        $statuses = [Application::STATUS_PAYMENT_VERIFIED, Application::STATUS_PERMIT_RELEASED];
        $status = in_array($request->query('status'), $statuses, true) ? $request->query('status') : null;

        $applications = Application::query()
            ->whereIn('status', $statuses)
            ->with('permit')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->query('q').'%';
                $query->where(function ($q) use ($term): void {
                    $q->where('tracking_number', 'like', $term)
                        ->orWhere('applicant_name', 'like', $term)
                        ->orWhere('business_name', 'like', $term)
                        ->orWhereHas('permit', fn ($permit) => $permit->where('permit_number', 'like', $term));
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [Application::STATUS_PAYMENT_VERIFIED])
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('bplo.permits.index', [
            'applications' => $applications,
            'readyCount' => Application::where('status', Application::STATUS_PAYMENT_VERIFIED)->count(),
            'releasedCount' => Application::where('status', Application::STATUS_PERMIT_RELEASED)->count(),
            'status' => $status,
        ]);
    }

    public function release(Application $application, PermitService $permits): RedirectResponse
    {
        $this->authorize('review', $application);

        try {
            $permits->release($application, request()->user());
        } catch (\Throwable $e) {
            return redirect()
                ->route('bplo.applications.show', $application)
                ->with('error', 'Permit release failed: '.$e->getMessage());
        }

        return redirect()
            ->route('bplo.applications.show', $application)
            ->with('success', 'Permit released successfully.');
    }

    public function regenerate(Permit $permit, PermitService $permits): RedirectResponse
    {
        $this->authorize('review', Application::class);

        try {
            $permits->regenerate($permit, request()->user());
        } catch (\Throwable $e) {
            return back()->with('error', 'Permit regeneration failed: '.$e->getMessage());
        }

        return redirect()
            ->route('bplo.applications.show', $permit->application)
            ->with('success', 'Permit regenerated with a fresh verification token.');
    }
}
