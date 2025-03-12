<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomerController extends Controller
{
    public function processData()
    {

        $fetch = Http::get(env("LINK_API_DATA"));


        $data = $fetch->json()['DATA'];

        $rows = explode("\n", trim($data));

        $header = explode("|", trim(array_shift($rows)));
        $indexNIM = array_search("NIM", $header);
        $indexYMD = array_search("YMD", $header);
        $indexNAMA = array_search("NAMA", $header);

        $formattedData = collect($rows)->map(function ($row) use ($indexNIM, $indexYMD, $indexNAMA) {
            $parts = explode("|", trim($row));

            return [
                'nim'  => $indexNIM !== false ? ($parts[$indexNIM] ?? '') : '',
                'ymd'  => $indexYMD !== false ? ($parts[$indexYMD] ?? '') : '',
                'nama' => $indexNAMA !== false ? ($parts[$indexNAMA] ?? '') : '',
            ];
        })->filter(function ($item) {
            return !empty($item['nim']) && !empty($item['ymd']);
        });

        Customer::upsert($formattedData->toArray(), ['nim'], ['ymd', 'nama']);

        return response()->json([
            'message' => 'Data berhasil disimpan',
            'data' => $formattedData
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->query('q');

        if (empty($query)) {
            return response()->json(['message' => 'No customers found'], 404);
        }

        $customers = Customer::query()
            ->where('nim', 'like', "%{$query}%")
            ->orWhere('nama', 'like', "%{$query}%")
            ->orWhere('ymd', 'like', "%{$query}%")
            ->get();

        return $customers->isEmpty()
            ? response()->json(['message' => 'No customers found'], 404)
            : response()->json($customers);
    }

    public function searchByNim(Request $request)
    {
        $validated = $request->validate([
            'nim'  => 'required|string|max:20',
        ]);
        $customers = Customer::query()->where('nim', '=', $validated['nim'])->first();
        return $customers->isEmpty()
            ? response()->json(['message' => 'No customers found'], 404)
            : response()->json($customers);
    }

    public function searchByNama(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
        ]);
        $customers = Customer::query()->where('nama', '=', $validated['nama'])->first();
        return $customers->isEmpty()
            ? response()->json(['message' => 'No customers found'], 404)
            : response()->json($customers);
    }


    public function searchByYmd(Request $request)
    {
        $validated = $request->validate([
            'ymd' => 'required|date_format:Ymd',
        ]);

        $customers = Customer::query()->where('ymd', '=', $validated['ymd'])->first();
        return $customers->isEmpty()
            ? response()->json(['message' => 'No customers found'], 404)
            : response()->json($customers);
    }
}
