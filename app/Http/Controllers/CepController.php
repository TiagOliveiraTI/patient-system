<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CepController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $addresses = [];

        $addresses = Cache::remember('address', 15, function () {
            return Address::paginate(10);
        });

        return $addresses;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $body = json_decode($request->getContent(), true);

        $viaCepResponse = $this->validateIfZipCodeExists($body);

        if (array_key_exists('erro', $viaCepResponse)) {
            return response()->json([
                "data" => sprintf("O cep %s não existe!", $request['cep'])
            ])->setStatusCode(404);
        }

        $address = [];
        $address['zip_code'] = $viaCepResponse['cep'];
        $address['street'] = !empty($body['rua']) ? $body['rua'] : $viaCepResponse['logradouro'];
        $address['number'] = $body['numero'];
        $address['complement'] = $viaCepResponse['complemento'];
        $address['neighborhood'] = !empty($body['bairro']) ? $body['bairro'] : $viaCepResponse['bairro'];
        $address['city'] = $viaCepResponse['localidade'];
        $address['stateCode'] = $viaCepResponse['uf'];
        $address['ibge'] = $viaCepResponse['ibge'];
        $address['gia'] = $viaCepResponse['gia'];
        $address['ddd'] = $viaCepResponse['ddd'];
        $address['siafi'] = $viaCepResponse['siafi'];

        $newAddress = Address::firstOrcreate($address);

        return response()->json([
            "data" => $newAddress
        ])->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $addressId)
    {
        return Address::find($addressId);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $addressId)
    {
        $body = json_decode($request->getContent(), true);

        $viaCepResponse = $this->validateIfZipCodeExists($body);

        $address = Address::find($addressId);

        $address['zip_code'] = $viaCepResponse['cep'];
        $address['street'] = !empty($body['rua']) ? $body['rua'] : $viaCepResponse['logradouro'];
        $address['number'] = $body['numero'];
        $address['complement'] = $viaCepResponse['complemento'];
        $address['neighbor'] = !empty($body['bairro']) ? $body['bairro'] : $viaCepResponse['bairro'];
        $address['city'] = $viaCepResponse['localidade'];
        $address['stateCode'] = $viaCepResponse['uf'];
        $address['ibge'] = $viaCepResponse['ibge'];
        $address['gia'] = $viaCepResponse['gia'];
        $address['ddd'] = $viaCepResponse['ddd'];
        $address['siafi'] = $viaCepResponse['siafi'];

        $address->save();

        return response()->json([
            "data" => $address
        ])->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $addressId)
    {
        try {
            $patient = Address::findOrFail($addressId);
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
}
