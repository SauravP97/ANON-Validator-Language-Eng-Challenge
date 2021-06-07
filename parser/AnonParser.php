<?php

    class AnonParser{
        
        private $parseString;
        private $valid;
        private $parsedJson;
        private $stringMap;
        private $errorMessage ;
        private $errorKey;

        function __construct($parseString)
        {
            $this->parseString = trim($parseString);
            $this->root = null;
            $this->valid = true;
            $this->parsedJson = [];
            $this->stringMap = [];
            $this->errorMessage = [];
        }

        // Fetches all the commas and colons present at the current
        // level of the JSON string
        function transformCurrentLevelSeperators($object){
            $object = trim($object);
            if($object[strlen($object)-1] == ','){
                $object = substr($object, 0, strlen($object)-1);
            }
            $level = 0;
            $transformedObject = "";

            for($i=0; $i<strlen($object); $i++){
                $char = $object[$i];
                if($char=='{' || $char=='['){
                    $level++;
                }
                else if($char == '}' || $char == ']'){
                    $level--;
                }
                if($level == 0){
                    if($char == ','){
                        $transformedObject .= "<comma>";
                    }
                    else if($char == ':'){
                        $transformedObject .= "<colon>";
                    }
                    else{
                        $transformedObject .= $char;
                    }
                }
                else{
                    $transformedObject .= $char;
                }
            }
            return $transformedObject;
        }

        // Validates the JSON Data Types
        // Allowed Datatypes
        //  1. String
        //  2. Numbers
        //  3. Boolean
        function validateJsonDataType($value){
            if(!$this->valid){
                return 0;
            }
            if($value[0]=='<' && $value[strlen($value)-1]=='>'){
                if(@$this->stringMap[$value]){
                    $value = $this->stringMap[$value];
                    return $value;
                }
                for($cc=0; $cc<5; $cc++){
                    $nvalue = substr($value, strpos($value, ">")+1, 3+$cc);
                    if(@$this->stringMap[$nvalue]){
                        $nvalue = $this->stringMap[$nvalue];
                        return $nvalue;
                    }
                }
                $this->valid = false;
                $this->errorMessage[] = "Error parsing the JSON object at : ".$value;
                return 0;
            }
            if(is_numeric($value) || $value == "true" || $value == "false"){
                return $value;
            }
            $this->valid = false;
            $this->errorMessage[] = "Error parsing the JSON object at : ".$value;
            return 0;
        }

        // Checks whether JSON Object is Valid or not
        function validateJsonObject($object){
            if(!$this->valid){
                return [];
            }
            $curMap = [];
            $object = trim($object);
            if($object[0]=='{' && $object[strlen($object)-1]=='}'){
                $object = substr($object, 1, strlen($object)-2);
                if(strlen(trim($object))==0){
                    return $curMap;
                }
                $object = $this->transformCurrentLevelSeperators($object);
                
                $items = explode('<comma>', $object);
                foreach($items as $item){
                    $tokens = explode('<colon>', $item);
                    $key = trim($tokens[0]);
                    $value = trim($tokens[1]);
                    if(@$this->stringMap[$key]){
                        $key = $this->stringMap[$key];
                    }
                    else{
                        $this->errorMessage[] = "Invalid JSON Key at : ".$key;
                        $this->valid = false;
                        return $curMap;
                    }
                    $this->errorKey = $key;

                    if($value[0] == '{'){
                        $curMap[$key] = $this->validateJsonObject($value);
                    }
                    else if($value[0] == '['){
                        $curMap[$key] = $this->validateJsonArray($value);
                    }
                    else{
                        $curMap[$key] = $this->validateJsonDataType($value);
                    }
                }
            }
            else{
                $this->errorMessage[] = "JSON object must start and end with a { } near ".$this->errorKey;
                $this->valid = false;
                return $curMap;
            }
            return $curMap;
        }

        // Checks whether JSON Array is valid or not
        function validateJsonArray($object){
            if(!$this->valid){
                return [];
            }
            $curArray = [];
            $object = trim($object);
            if($object[0]=='[' && $object[strlen($object)-1]==']'){
                $object = substr($object, 1, strlen($object)-2);
                if(strlen(trim($object))==0){
                    return $curArray;
                }
                $object = $this->transformCurrentLevelSeperators($object);
                
                $values = explode('<comma>', $object);
                
                foreach($values as $value){
                    $value = trim($value);
                    if($value[0] == '{'){
                        $curArray[] = $this->validateJsonObject($value);
                    }
                    else if($value[0] == '['){
                        $curArray[] = $this->validateJsonArray($value);
                    }
                    else{
                        $curArray[] = $this->validateJsonDataType($value);
                    }
                }
            }
            else{
                $this->errorMessage[] = "Array of JSON objects must start and end with a [ ] near ".$this->errorKey;
                $this->valid = false;
                return $curArray;
            }    
            return $curArray;
        }

        function validateJsonString(){
            $this->extractComments();
            $this->errorKey = "Root Key";
            $this->parseString = str_replace("\n", " ", $this->parseString);

            $this->mapAllString(); 
            //error_log($this->parseString); 
            //error_log(print_r($this->stringMap, true));
            if($this->parseString[strlen($this->parseString)-1] == ','){
                $this->parseString = substr($this->parseString, 0, strlen($this->parseString)-1);
                $this->parseString = trim($this->parseString);
            }
            $this->parsedJson = $this->validateJsonObject($this->parseString);  
        }

        function extractComments(){
            $updatedString = "";
            $comment = false;
            for($i=0; $i<strlen($this->parseString); $i++){
                $char = $this->parseString[$i];
                if($i < strlen($this->parseString)-1){
                    $charNext = $this->parseString[$i+1];
                    if($char == '/' && $char == $charNext){
                        $comment = true;
                    }
                }
                if($comment && $char=="\n"){
                    $comment = false;
                }
                if(!$comment){
                    $updatedString .= $char;
                }
            }
            $this->parseString = $updatedString;
        }

        function mapAllString(){
            $keyValue = 0;
            $count = 0;
            $curKey = "";
            $formattedString = "";

            for($i=0; $i<strlen($this->parseString); $i++){
                $char = $this->parseString[$i];
                if($char=='\\' && $i+1<strlen($this->parseString) && $this->parseString[$i+1]=='"'){
                    if($count == 0){
                        $formattedString .= $this->parseString[$i+1];
                    }
                    else{
                        $curKey .= $this->parseString[$i+1];
                    }
                    $i++;
                    continue;
                }
                if($char == '"' && $count == 0){
                    if($i+2<strlen($this->parseString) && $this->parseString[$i+1] == $char && $this->parseString[$i+2] == $char){
                        $i = $i+2;
                        $curKey .= $char.$char.$char;
                        $count = 2;
                    }
                    else{
                        $curKey .= $char;
                        $count = 1;
                    }
                }
                else if($char == '"' && ($count == 1 || $count == 2)){
                    if($count == 1){
                        $curKey .= $char;
                        $this->stringMap["<".$keyValue.">"] = $curKey;
                        $curKey = "";
                        $count = 0;
                        $formattedString .= "<".$keyValue.">";
                        $keyValue++;
                    }
                    else if($i+2<strlen($this->parseString) && $this->parseString[$i+1] == $char && $this->parseString[$i+2] == $char){
                        $curKey .= $char.$char.$char;
                        $this->stringMap["<".$keyValue.">"] = $curKey;
                        $curKey = "";
                        $count = 0;
                        $formattedString .= "<".$keyValue.">";
                        $keyValue++;
                        $i = $i+2;
                    }
                    else{
                        $curKey .= $char;
                    }
                }
                else{
                    if($count == 0){
                        $formattedString .= $char;
                    }
                    else{
                        $curKey .= $char;
                    }
                }
            }
            $this->parseString = $formattedString;
        }

        function isValidJsonObject(){
            return $this->valid;
        }

        function getParsedJsonObject(){
            if($this->valid){
                return $this->parsedJson;
            }
        }

        function getErrorMessage(){
            return $this->errorMessage;
        }
    }

    if(!@$_POST['anonString']){
        echo json_encode(["status"=>false, "msg"=>"No anon string passed"]);
        die;
    }

    //Start ANON Parsing Process
    $parseObject = new AnonParser($_POST['anonString']);
    $parseObject->validateJsonString();
    echo json_encode(["status"=> true, "valid"=> $parseObject->isValidJsonObject(), "error"=> json_encode($parseObject->getErrorMessage()), "object"=> json_encode($parseObject->getParsedJsonObject())]);
?>
