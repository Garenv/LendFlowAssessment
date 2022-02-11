<?php

namespace Tests\Unit;

use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class BooksTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_example()
    {

        $this->json('GET',  '/1/nyt/best-sellers', [
            'api-key' => env('API_KEY'),
            "author" => "David Adams Richards",
            "isbn" => "0671003542",
        ])->assertStatus(200);

    }
}


