<?php
namespace Dialect\Trafikverket;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Exception;
use Carbon\Carbon;

class TrafikverketQueryBuilder {
    protected $model = "";
    protected $limit = 0;
    protected $skip = 0;
    protected $order_by = null;
    protected $last_modified = null;
    public $filters = [];

    public $operators = [
        "=" => "EQ",
        "exists" => "EXISTS",
        ">" => "GT",
        ">=" => "GTE",
        "<" => "LT",
        "<=" => "LTE",
        "!=" => "NE",
        "like" => "LIKE",
        "notlike" => "NOTLIKE",
        "in" => "IN",
        "notin" => "NOTIN",
        "within" => "WITHIN"
    ];

    function __construct($model) {
        $this->model = $model;
    }


    public function orWhere($field, $operator = null, $value = null){
        return $this->where($field, $operator, $value, $type = "OR");
    }

    public function where($field, $operator = null, $value = null, $type = "AND"){
        if($field instanceof \Closure)
        {
            return $this->handleNested($field, $type);
        }
        if($operator && !$value)
        {
            $op = "EQ";
            $value = $operator;
        }
        else
        {
            $op = $this->operators[strtolower($operator)];

        }


        if(!$op)
        {
            throw new Exception("Invalid operator: ", $operator);
        }

        $this->filters[] = [
            "field" => $field,
            "operator" => $op,
            "value" => $value,
            "type" => $type
        ];

        return $this;

    }

    public function handleNested($func, $type){
        call_user_func($func, $query = new TrafikverketQueryBuilder(""));
        $this->filters[] = [
            "filters" => $query->filters,
            "type" => "NESTED_".$type
        ];
        return $this;
    }


    public function limit($limit){
        $this->limit = $limit;
        return $this;
    }

    public function skip($skip){
        $this->skip = $skip;
        return $this;
    }

    public function orderBy($order_by){
        $this->order_by = $order_by;
        return $this;
    }

    public function lastModified($timestamp) {
        if(!$timestamp instanceof Carbon) {
            $timestamp = Carbon::createFromTimestamp($timestamp);
        }

        $this->last_modified = $timestamp->format('Y-m-d\TH:i:s.00');

        return $this;
    }


    public function toXml(){

        $dom = new \DOMDocument;
        $dom->formatOutput = TRUE;
        $dom->preserveWhiteSpace = FALSE;
        $dom->loadXML('<REQUEST/>');

        $login = $dom->createElement("LOGIN");
        $login->setAttribute("authenticationkey", config("trafikverket.api_token"));

        $query = $dom->createElement("QUERY");
        $query->setAttribute("objecttype", $this->model);
        $query->setAttribute("limit", $this->limit);
        $query->setAttribute("skip", $this->skip);

        $filter = $dom->createElement("FILTER");
        if($this->last_modified)
        {
            $query->setAttribute("lastmodified", "true");
            $el = $dom->createElement("GT");
            $el->setAttribute("name", "ModifiedTime");
            $el->setAttribute("value", $this->last_modified);
            $filter->appendChild($el);
        }

        $this->filtersToXml($dom, $this->filters, $filter);
        $query->appendChild($filter);

        $dom->documentElement->appendChild($login);
        $dom->documentElement->appendChild($query);

        return html_entity_decode($dom->saveXML(), ENT_COMPAT, 'UTF-8');

    }

    private function filtersToXml(\DOMDocument $dom, $filters, \DOMElement &$parent){
        $ors = $dom->createElement("OR");
        $ands = $dom->createElement("AND");

        foreach($filters as $filter){

            if($filter["type"] == "NESTED_AND")
            {
                $this->filtersToXml($dom, $filter["filters"], $and);
            }
            elseif($filter["type"] == "NESTED_OR")
            {
                $this->filtersToXml($dom, $filter["filters"], $ors);
            }
            else
            {
                $el = $dom->createElement($filter["operator"]);
                $el->setAttribute("name", $filter["field"]);
                $el->setAttribute("value", $filter["value"]);

                if($filter["type"] == "OR")
                {
                    $ors->appendChild($el);
                }
                else
                {
                    $ands->appendChild($el);
                }
            }

            //only append if the group has any children
            if($ors->childNodes->length){
                if($ands->childNodes->length){
                    $ors->appendChild($ands);
                }
                $parent->appendChild($ors);
            }
            if(!$ors->childNodes->length && $ands->childNodes->length){
                $parent->appendChild($ands);
            }
        }

    }

    public function get(){
        $client = new Client();

        $result = $client->post(config( "trafikverket.url"), [
            "body" => $this->toXml(),
            'headers'  => ['content-type' => 'text/xml'],
        ]);

        $json = json_decode($result->getBody()->getContents())->RESPONSE->RESULT[0];

        if(property_exists($json,$this->model)) return $json->{$this->model};
        return [];
    }
}