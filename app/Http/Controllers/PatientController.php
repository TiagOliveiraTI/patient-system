<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Models\Address;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $patients = [];

        $patients = Cache::remember('patients', 15, function () {
            return Patient::paginate(10);
        });

        return $patients;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $addressData = $request->input('endereco');

        $viaCepResponse = $this->validateIfZipCodeExists($addressData);


        if (array_key_exists('erro', $viaCepResponse)) {
            return response()->json([
                "data" => sprintf("O cep %s não existe!", $request['cep'])
            ])->setStatusCode(404);
        }

        $address = [];

        $address['zip_code'] = $viaCepResponse['cep'];
        $address['street'] = !empty($addressData['rua']) ? $addressData['rua'] : $viaCepResponse['logradouro'];
        $address['number'] = $addressData['numero'];
        $address['complement'] = $viaCepResponse['complemento'];
        $address['neighborhood'] = !empty($addressData['bairro']) ? $addressData['bairro'] : $viaCepResponse['bairro'];
        $address['city'] = $viaCepResponse['localidade'];
        $address['stateCode'] =  !empty($addressData['uf']) ? $addressData['uf'] : $viaCepResponse['uf'];
        $address['ibge'] = $viaCepResponse['ibge'];
        $address['gia'] = $viaCepResponse['gia'];
        $address['ddd'] = $viaCepResponse['ddd'];
        $address['siafi'] = $viaCepResponse['siafi'];

        $newAddress = Address::firstOrCreate($address);

        $patient = new Patient();
        $patient->cpf = $request->input('cpf');
        $patient->photo = $request->input('foto');
        $patient->name = $request->input('nome');
        $patient->mother_name = $request->input('nome_mae');
        $patient->birth_date = $request->input('data_nascimento');
        $patient->cns = $request->input('cns');
        $patient->address_id = $newAddress->id;
        $patient->save();

        return response()->json([
            'message' => 'Patient created successfully',
            'data' => $patient
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function validateIfZipCodeExists(array $request): array|JsonResponse
    {
        $zipCode = preg_replace('/[^0-9]/', '', $request['cep']);

        return json_decode($this->makeCepConsult($zipCode), true);
    }

    private function makeCepConsult(string $cep): string
    {
        $apiUrl = sprintf('%s/%s/json', getenv('VIACEP_URL'), $cep);

        return Http::get($apiUrl);
    }
}
