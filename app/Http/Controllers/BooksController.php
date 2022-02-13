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
                'message'   => 'Defective query param detected!'
            ];

            $validation     = Validator::make($request->all(), [
                'author'    => ['string','regex:/^[a-z][a-z\s]*$/i'], // Regex that only allows letters along with spaces (first name and last name of authors).  Numbers or other special chars will be rejected.
                'isbn'      => ['numeric','regex:/^(\d{10}|\d{13})$/'], // Regex only allowing 10 or 13 digits.
                'title'     => 'string|regex:/^[A-Z@~`!@#$%^*()_=+\\\';:\/?>.,-]/i', // Regex to allow alphabet and special characters for title because some titles have the "#" symbol.
                'offset'    => ['numeric','regex:/^-?\d*[020]$/'] // Regex to allow only multiples of 20.
            ]);

            if(!$validation->fails()) {
                $response = [
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

            // The NYT endpoint's defective in terms of isbns.
            // It doesn't accept isbns that are semicolon separated.
            // It only accepts one value at a time.
            // For example: https://api.nytimes.com/svc/books/v3/lists/best-sellers/history.json?api-key=yourApiKey&isbn=0871404427;9780871404428 won't work.
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
              // 'isbn'       => [(object) $queryArray] // It would've looked like this: [{"isbn10":"0670022632", "isbn13":"9780670022632"}]]
            ];

            $response        = Http::get($nytBooksEndpoint, $queryParams);

            return $response->json();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw new \Exception($e->getMessage(), $e->getCode(), $e);
        }

    }
}
