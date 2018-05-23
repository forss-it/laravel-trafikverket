<?php

use Tests\TestCase;
use Dialect\Trafikverket\Trafikverket;
use Dialect\Trafikverket\TrafikverketQueryBuilder;

class TrafikverketTest extends TestCase {

    public function setUp() {
        Parent::setUp();
        config(['trafikverket.api_token' => "7eed08f6ddac4016ba4e30d780e8619a"]);
        config(['trafikverket.url' => "http://api.trafikinfo.trafikverket.se/v1.2/data.json"]);
    }

    /** @test */
    public function can_instantiate_query_builder() {
        $trafikverket = Trafikverket::trainStation();
        $this->assertInstanceOf(TrafikverketQueryBuilder::class, $trafikverket);
    }

    /** @test */
    public function can_limit_response() {
        $station = Trafikverket::trainStation()->limit(2)->get();
        $this->assertCount(2, $station);
    }

    /** @test */
    public function can_filter_response() {
        $stations = Trafikverket::trainStation()->where("AdvertisedLocationName", "Alingsås")->get();
        $this->assertCount(1, $stations);
        $this->assertEquals($stations[0]->AdvertisedLocationName, "Alingsås");
    }

    /** @test */
    public function can_filter_with_and() {
        $stations = Trafikverket::trainStation()->where("AdvertisedLocationName", "LIKE", "köping")->where("AdvertisedShortLocationName", "Linköping")->get();

        $this->assertCount(1, $stations);
        $this->assertEquals($stations[0]->AdvertisedLocationName, "Linköping C");

    }

    /** @test */
    public function can_filter_with_or() {
        $stations = Trafikverket::trainStation()->where("AdvertisedShortLocationName", "Katrineholm")->orWhere("AdvertisedShortLocationName", "Linköping")->get();

        $this->assertCount(2, $stations);

    }

    /** @test */
    public function can_nest_filters() {
        $stations = Trafikverket::trainStation()->where("AdvertisedLocationName", "=", "Norrköping C")->orWhere(function($query) {
                return $query->where("AdvertisedShortLocationName", "Katrineholm")->orWhere("AdvertisedShortLocationName", "Linköping");
            })->get();

        $this->assertCount(3, $stations);

    }

}