<?php
/**
 * Controller per la gestione dei prodotti
 * Delega la logica business a ProductService
 */

require_once __DIR__ . '/../Models/Product.php';
require_once __DIR__ . '/../Services/ProductService.php';

class ProductsController {
    private $service;
    
    public function __construct($db) {
        $this->service = new ProductService($db);
    }
    
    /**
     * Ottieni tutti i prodotti
     */
    public function getAll() {
        $result = $this->service->getAllProducts();
        
        // Converti oggetti Model in array per la risposta JSON
        if ($result['success'] && isset($result['data'])) {
            $result['products'] = array_map(function($product) {
                return $product->toArray();
            }, $result['data']);
            unset($result['data']);
        }
        
        return $result;
    }
    
    /**
     * Ottieni un singolo prodotto per ID
     */
    public function getById($id) {
        $result = $this->service->getProductById($id);
        
        // Converti oggetto Model in array per la risposta JSON
        if ($result['success'] && isset($result['data'])) {
            $result['product'] = $result['data']->toArray();
            unset($result['data']);
        }
        
        return $result;
    }
    
    /**
     * Crea un nuovo prodotto
     */
    public function create($data) {
        // Validazione dati
        if (empty($data['name'])) {
            return [
                'success' => false,
                'error' => 'Nome prodotto obbligatorio'
            ];
        }
        
        $result = $this->service->createProduct($data);
        
        // Converti oggetto Model in array per la risposta JSON
        if ($result['success'] && isset($result['data'])) {
            $result['product'] = $result['data']->toArray();
            $result['id'] = $result['data']->id;
            unset($result['data']);
        }
        
        return $result;
    }
    
    /**
     * Aggiorna un prodotto esistente
     */
    public function update($id, $data) {
        // Validazione dati
        if (empty($data['name'])) {
            return [
                'success' => false,
                'error' => 'Nome prodotto obbligatorio'
            ];
        }
        
        $result = $this->service->updateProduct($id, $data);
        
        // Converti oggetto Model in array per la risposta JSON
        if ($result['success'] && isset($result['data'])) {
            $result['product'] = $result['data']->toArray();
            unset($result['data']);
        }
        
        return $result;
    }
    
    /**
     * Elimina un prodotto
     */
    public function delete($id) {
        return $this->service->deleteProduct($id);
    }
    
    /**
     * Salvataggio batch di tutti i prodotti
     * Delega al Service per gestire la transazione
     */
    public function saveAll($products) {
        if (!is_array($products)) {
            return [
                'success' => false,
                'error' => 'Dati prodotti non validi'
            ];
        }
        
        try {
            $insertedCount = 0;
            $errors = [];
            
            foreach ($products as $productData) {
                $result = $this->service->createProduct($productData);
                if ($result['success']) {
                    $insertedCount++;
                } else {
                    $errors[] = $result['error'] ?? 'Errore sconosciuto';
                }
            }
            
            return [
                'success' => true,
                'message' => "Salvati {$insertedCount} prodotti",
                'count' => $insertedCount,
                'errors' => $errors
            ];
        } catch (Exception $e) {
            error_log("Errore saveAll prodotti: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Errore nel salvataggio dei prodotti',
                'details' => $e->getMessage()
            ];
        }
    }
}
