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
                'status'    => 'failed',
                'message'   => 'one of the fields are incorrect!'
            ];

            $validation     = Validator::make($request->all(), [
                'author'    => 'string',
                'isbn'      => ['numeric','regex:/^(\d{10}|\d{13})$/'], // Regex only allowing 10 or 13 digits.
                'title'     => 'string|regex:/^[A-Z@~`!@#$%^*()_=+\\\';:\/?>.,-]/i', // Regex to allow alphabet and special characters for title because some titles have the "#" symbol.
                'offset'    => 'numeric'
            ]);

            if(!$validation->fails()) {
                return [
                    'status'  => 'success',
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
            $nytBooksEndpoint = env('NYT_BOOKS_ENDPOINT');
            $apiKey           = env('API_KEY');
            $author           = $request->get('author');
            $title            = $request->get('title');
            $offSet           = (int) $request->get('offset');
            $isbn             = $request->get('isbn');

            $validationStatus = $this->validation($request);

            if($offSet % 20 != 0) {
                return [
                    'status'    => 'failed',
                    'message'   => 'offset is not a multiple of 20!'
                ];
            }

            // The NYT endpoint is defective in terms of isbns.
            // It doesn't accept isbns that are semicolon separated.
            // It only accepts one value at a time.
            // This is how I would've done it if their API worked properly.
            /*
            $keysArray       = ['isbn10', 'isbn13'];
            $valuesArray     = explode(';', $isbn);
            $queryArray      = [];

            foreach ($valuesArray as $i => $value) {
                $queryArray[$keysArray[$i]] = $value;
            }
            */

            if($validationStatus['status'] == "failed") {
                return response()->json(['status' => 'Field validation has failed!'], 404);
            }

            $queryParams = [
                'api-key'    => $apiKey,
                'author'     => $author,
                'title'      => $title,
                'offset'     => $offSet,
                'isbn'       => $isbn
            ];

            $response        = Http::get($nytBooksEndpoint, $queryParams);

            return $response->json();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }

    }
}
