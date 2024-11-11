<?php
class MapsAPI {
    private $apiKey;
    
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }
    
    public function getMapScript() {
        return "https://maps.googleapis.com/maps/api/js?key={$this->apiKey}&libraries=places&callback=initMap&v=weekly";
    }
}