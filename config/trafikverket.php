<?php

return [
	"api_token" => env("TRAFIKVERKET_API_TOKEN", ''),
	"url" => env("TRAFIKVERKET_URL", 'http://api.trafikinfo.trafikverket.se/v1.2/data.json'),
];