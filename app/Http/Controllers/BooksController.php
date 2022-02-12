<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BooksController extends Controller
{

    public function validation($request)
    {
        try {
            $response = [
                'status' => 'failed',
                'message' => 'one of the fields are incorrect!'
            ];

            // Regex to allow alphabet and special characters for title because some titles have the "#" symbol
            $validation = Validator::make($request->all(), [
                'author' => 'string|regex:/^[A-Z@~`!@#$%^*()_=+\\\';:\/?>.,-]/i',
                'title'  => 'string|regex:/^[A-Z@~`!@#$%^*()_=+\\\';:\/?>.,-]/i'
            ]);

            if(!$validation->fails()) {
                return [
                    'status' => 'success',
                    'message' => 'Fields successfully validated!'
                ];
            }

            return $response;

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }

    }

    public function index(Request $request)
    {

        try {
            $apiKey           = env('API_KEY');
            $nytBooksEndpoint = env('NYT_BOOKS_ENDPOINT');
            $author           = $request->get('author');
            $title            = $request->get('title');
            $isbn             = $request->get('isbn');
            $offSet           = $request->get('offset');

            $validationStatus = $this->validation($request);

            if($validationStatus['status'] == "failed") {
                return response()->json(['status' => 'Field validation has failed!'], 404);
            }

            $keysArray       = ['isbn10', 'isbn13'];
            $valuesArray     = explode(';', $isbn);
            $queryArray      = [];

            foreach ($valuesArray as $i => $value) {
                $queryArray[$keysArray[$i]] = $value;
            }

            $queryParams = [
                'api-key'    => $apiKey,
                'author'     => $author,
                'title'      => $title,
//                'offset'     => $offSet,
                'isbn'       => [(object) $queryArray] // empties the results when this key value is here for some reason
            ];

            dd($queryParams);

            $response        = Http::get($nytBooksEndpoint, $queryParams);
            $results         = $response->json();

            $collection      = collect($results);
            $outputs         = $collection->chunk(20)[$offSet];

            return $results;

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }

    }
}
