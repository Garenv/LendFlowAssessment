<?php

namespace Tests\Unit;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BooksTest extends TestCase
{

    public function test_nyt_books_api()
    {
        Http::fake();

        // Change or remove any keys.
        // To test properly, change the values of the keys to valid values consistent with the api response.
        Http::get(route('best_sellers_uri'), [
//            'author' => 'David Adams Richards',
//            'isbn' => '0671003542',
//            'title' => '#ASKGARYVEE',
            'offset' => 19
        ]);

        Http::assertSent(function (Request $request) {
            // Make sure the return value is consistent with the variables value that's defined in Http::get(...) above
            return $request['offset'] == 19;
        });
    }
}


