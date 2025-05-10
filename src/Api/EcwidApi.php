<?php
namespace EcwidApp\Api;

class EcwidApi {
    private $accessToken;
    private $storeId;
    private $baseUrl = ECWID_API_URL;
    
    public function setAccessToken($token) {
        $this->accessToken = $token;
    }
    
    public function setStoreId($storeId) {
        $this->storeId = $storeId;
    }
    
    public function getProducts($params = []) {
        $defaultParams = [
            'limit' => 100,
            'offset' => 0,
            'fields' => 'items(id,name,sku)'
        ];
        $params = array_merge($defaultParams, $params);
        return $this->request('GET', "/{$this->storeId}/products", $params);
    }
    
    public function updateProduct($productId, $data) {
        return $this->request('PUT', "/{$this->storeId}/products/{$productId}", [], $data);
    }
    
    public function getStoreProfile() {
        return $this->request('GET', "/{$this->storeId}/profile");
    }
    
    private function request($method, $endpoint, $params = [], $data = null) {
        $url = $this->baseUrl . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'Cache-Control: no-cache'
        ];
        
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new \Exception('Curl error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode >= 400) {
            $error = json_decode($response, true);
            throw new \Exception("API request failed: " . 
                ($error['errorMessage'] ?? "HTTP $httpCode"));
        }
        
        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response');
        }
        
        return $result;
    }
    
    public function batchUpdateProducts($updates) {
        $batch = array_map(function($update) {
            return [
                'method' => 'PUT',
                'url' => "/products/{$update['id']}",
                'body' => $update['data']
            ];
        }, $updates);
        
        return $this->request('POST', "/{$this->storeId}/batch", [], [
            'requests' => $batch
        ]);
    }
}
