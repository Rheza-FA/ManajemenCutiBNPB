<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PegawaiPnsImport;
use App\Imports\PegawaiCpnsImport;
use App\Imports\PegawaiPppkImport;
use App\Imports\RiwayatCutiImport;

class ImportController extends Controller
{
    public function index()
    {
        return view('import'); 
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'kategori' => 'required|in:pns,cpns,pppk,riwayat_cuti',
        ]);

        $file = $request->file('file');
        
        try {
            switch ($request->kategori) {
                case 'pns': Excel::import(new PegawaiPnsImport, $file); break;
                case 'cpns': Excel::import(new PegawaiCpnsImport, $file); break;
                case 'pppk': Excel::import(new PegawaiPppkImport, $file); break;
                case 'riwayat_cuti': Excel::import(new RiwayatCutiImport, $file); break;
            }
            return redirect()->back()->with('success', 'Data berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}