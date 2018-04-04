<?php 
namespace XcooBee\Auth;

use JsonSerializable;

class AuthRequest implements JsonSerializable {
    public function __construct(array $array) {
        $this->array = $array;
    }
    
    public function jsonSerialize() {
        return $this->array;
    }
}