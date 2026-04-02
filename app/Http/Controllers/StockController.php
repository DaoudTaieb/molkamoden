<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('stocks')
            ->join('produits', 'stocks.produitid', '=', 'produits.produitid')
            ->join('sites', 'stocks.siteid', '=', 'sites.siteid')
            ->leftJoin('familles', 'produits.familleid', '=', 'familles.familleid')
            ->leftJoin('sousfamilles', 'produits.sousfamilleid', '=', 'sousfamilles.sousfamilleid')
            ->leftJoin('fournisseurs', 'produits.fournisseurid', '=', 'fournisseurs.fournisseurid')
            ->select(
                'stocks.*',
                'produits.produitcode',
                'produits.produitlibelle',
                'sites.libelle as site_libelle',
                'familles.famillelibelle as famille',
                'sousfamilles.sousfamillelibelle as sous_famille',
                'fournisseurs.nom as fournisseur'
            );

        // Filters
        if ($request->site_id) {
            $query->where('stocks.siteid', $request->site_id);
        }
        if ($request->famille_id) {
            $query->where('produits.familleid', $request->famille_id);
        }
        if ($request->sous_famille_id) {
            $query->where('produits.sousfamilleid', $request->sous_famille_id);
        }
        if ($request->fournisseur_id) {
            $query->where('produits.fournisseurid', $request->fournisseur_id);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('produits.produitlibelle', 'like', '%' . $request->search . '%')
                    ->orWhere('produits.produitcode', 'like', '%' . $request->search . '%');
            });
        }

        $stocks = $query->paginate(50)->withQueryString();

        return Inertia::render('Stock', [
            'stocks' => $stocks,
            'filters' => $request->only(['site_id', 'famille_id', 'sous_famille_id', 'fournisseur_id', 'search']),
            'sites' => DB::table('sites')->select('siteid as id', 'libelle')->get(),
            'familles' => DB::table('familles')->select('familleid as id', 'famillelibelle as libelle')->get(),
            'sous_familles' => DB::table('sousfamilles')->select('sousfamilleid as id', 'sousfamillelibelle as libelle')->get(),
            'fournisseurs' => DB::table('fournisseurs')->select('fournisseurid as id', 'nom as libelle')->get(),
        ]);
    }
}
