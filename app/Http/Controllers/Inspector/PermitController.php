<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use App\Models\Permit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermitController extends Controller
{
    public function index(Request $request): View
    {
        $conditions = ['VALID', 'EXPIRED', 'REVOKED'];
        $condition = in_array($request->query('condition'), $conditions, true) ? $request->query('condition') : null;

        $permits = Permit::query()
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->query('q').'%';
                $query->where(function ($q) use ($term): void {
                    $q->where('permit_number', 'like', $term)
                        ->orWhere('business_name', 'like', $term)
                        ->orWhere('business_address', 'like', $term);
                });
            })
            ->when($condition === 'VALID', function ($query): void {
                $query->where('status', Permit::STATUS_ACTIVE)
                    ->where(function ($q): void {
                        $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', today());
                    });
            })
            ->when($condition === 'EXPIRED', function ($query): void {
                $query->where('status', Permit::STATUS_ACTIVE)
                    ->whereNotNull('valid_until')
                    ->whereDate('valid_until', '<', today());
            })
            ->when($condition === 'REVOKED', fn ($query) => $query->where('status', Permit::STATUS_REVOKED))
            ->latest('issued_at')
            ->paginate(15)
            ->withQueryString();

        return view('inspector.index', [
            'permits' => $permits,
            'condition' => $condition,
        ]);
    }

    public function show(Permit $permit): View
    {
        $permit->load(['qrVerifications' => fn ($q) => $q->latest('created_at')->limit(50)]);

        return view('inspector.show', [
            'permit' => $permit,
        ]);
    }
}
