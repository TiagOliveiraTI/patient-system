<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Models\Address;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $patients = Cache::remember('patients', 15, function () {
            return Patient::with('address')->paginate(10);
        });

        return $patients;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $address = [];
        $patient = new Patient();

        try {
            $validatedData = $request->validate(Patient::rules());

            $addressData = $validatedData['endereco'];

            $viaCepResponse = $this->validateIfZipCodeExists($addressData);

            if (array_key_exists('erro', $viaCepResponse)) {
                return response()
                    ->json([
                        "data" => sprintf("O cep %s não existe!", $addressData['cep'])
                    ])->setStatusCode(404);
            }

            $address = $this->makeAddressData($viaCepResponse, $address, $addressData);

            $newAddress = Address::firstOrCreate($address);

            $this->makePatientData($validatedData, $patient, $newAddress);

            return response()->json([
                'message' => 'Patient created successfully',
                'data' => $patient
            ], 201);
        } catch (BadRequestException $exception) {
            return response()->json([
                'error' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Patient::find($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $address = [];
            $patient = Patient::findOrFail($id);

            $validatedData = $request->validate(Patient::rules());

            $addressData = $validatedData['endereco'];
            $viaCepResponse = $this->validateIfZipCodeExists($addressData);

            if (array_key_exists('erro', $viaCepResponse)) {
                return response()
                    ->json([
                        "data" => sprintf("O cep %s não existe!", $addressData['cep'])
                    ])->setStatusCode(404);
            }

            $address = $this->makeAddressData($viaCepResponse, $address, $addressData);

            $newAddress = Address::firstOrCreate($address);

            $this->makePatientData($validatedData, $patient, $newAddress);

            return response()->json([
                'message' => 'Patient updated successfully',
                'data' => $patient
            ], 200);
        } catch (BadRequestException $exception) {
            return response()->json([
                'error' => $exception->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $patient = Patient::findOrFail($id);
            return $patient->delete();
        } catch (BadRequestException $exception) {
            return response()->json([
                'error' => $exception->getMessage()
            ], 400);
        }
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

    /**
     * @param JsonResponse|array $viaCepResponse
     * @param array $address
     * @param mixed $addressData
     * @return array
     */
    private function makeAddressData(JsonResponse|array $viaCepResponse, array $address, mixed $addressData): array
    {
        $address['zip_code'] = $viaCepResponse['cep'];
        $address['street'] = !empty($addressData['rua']) ? $addressData['rua'] : $viaCepResponse['logradouro'];
        $address['number'] = $addressData['numero'];
        $address['complement'] = $viaCepResponse['complemento'];
        $address['neighborhood'] = !empty($addressData['bairro']) ? $addressData['bairro'] : $viaCepResponse['bairro'];
        $address['city'] = $viaCepResponse['localidade'];
        $address['stateCode'] = !empty($addressData['uf']) ? $addressData['uf'] : $viaCepResponse['uf'];
        $address['ibge'] = $viaCepResponse['ibge'];
        $address['gia'] = $viaCepResponse['gia'];
        $address['ddd'] = $viaCepResponse['ddd'];
        $address['siafi'] = $viaCepResponse['siafi'];
        return $address;
    }

    /**
     * @param array $validatedData
     * @param Patient $patient
     * @param $newAddress
     * @return void
     */
    private function makePatientData(array $validatedData, Patient $patient, $newAddress): void
    {
        $patient->cpf = preg_replace('/[^0-9]/', '', $validatedData['cpf']);
        $patient->photo = $validatedData['foto'];
        $patient->name = $validatedData['nome'];
        $patient->mother_name = $validatedData['nome_mae'];
        $patient->birth_date = $validatedData['data_nascimento'];
        $patient->cns = $validatedData['cns'];
        $patient->address_id = $newAddress->id;
        $patient->save();
    }
}
