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

    public function searchByNim($q)
    {
        $customer = Customer::query()->where('nim', '=', $q)->first();
        return $customer
            ? response()->json($customer)
            : response()->json(['message' => 'No customers found'], 404);
    }

    public function searchByNama($q)
    {
        $customer = Customer::query()->where('nama', '=', $q)->first();
        return $customer
            ? response()->json($customer)
            : response()->json(['message' => 'No customers found'], 404);
    }

    public function searchByYmd($q)
    {
        $customer = Customer::query()->where('ymd', '=', $q)->first();
        return $customer
            ? response()->json($customer)
            : response()->json(['message' => 'No customers found'], 404);
    }
}
