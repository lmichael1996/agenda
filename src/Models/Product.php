<?php
/**
 * Model Product - Rappresenta l'entità Prodotto
 */

class Product {
    public $id;
    public $name;
    public $price;
    public $stock_quantity;
    public $description;
    
    /**
     * Costruttore
     */
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }
    
    /**
     * Popola l'oggetto con i dati
     */
    public function hydrate($data) {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->price = $data['price'] ?? 0.0;
        $this->stock_quantity = $data['stock_quantity'] ?? 0;
        $this->description = $data['description'] ?? '';
    }
    
    /**
     * Converte l'oggetto in array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'stock_quantity' => $this->stock_quantity,
            'description' => $this->description
        ];
    }
    
    /**
     * Valida i dati del prodotto
     */
    public function validate() {
        $errors = [];
        
        if (empty(trim($this->name))) {
            $errors[] = 'Il nome del prodotto è obbligatorio';
        }
        
        if ($this->price < 0) {
            $errors[] = 'Il prezzo non può essere negativo';
        }
        
        if ($this->stock_quantity < 0) {
            $errors[] = 'La giacenza non può essere negativa';
        }
        
        return empty($errors) ? ['valid' => true] : ['valid' => false, 'errors' => $errors];
    }
}

