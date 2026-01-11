<?php
/**
 * Service per la gestione dei prodotti
 */

class ProductService {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Ottieni tutti i prodotti
     */
    public function getAllProducts() {
        $stmt = $this->db->prepare('
            SELECT id, name, price, stock_quantity, description
            FROM products
            ORDER BY name ASC
        ');
        
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $products = [];
        foreach ($results as $row) {
            $products[] = new Product($row);
        }
        
        return ['success' => true, 'data' => $products];
    }
    
    /**
     * Ottieni un prodotto per ID
     */
    public function getProductById($id) {
        $stmt = $this->db->prepare('
            SELECT id, name, price, stock_quantity, description
            FROM products
            WHERE id = ?
        ');
        
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            return ['success' => true, 'data' => new Product($row)];
        }
        
        return ['success' => false, 'error' => 'Prodotto non trovato'];
    }
    
    /**
     * Crea un nuovo prodotto
     */
    public function createProduct($data) {
        try {
            $product = new Product($data);
            
            $stmt = $this->db->prepare('
                INSERT INTO products (name, price, stock_quantity, description)
                VALUES (?, ?, ?, ?)
            ');
            
            $stmt->execute([
                $product->name,
                $product->price,
                $product->stock_quantity,
                $product->description
            ]);
            
            $product->id = $this->db->lastInsertId();
            
            return ['success' => true, 'data' => $product];
        } catch (PDOException $e) {
            error_log("ProductService::createProduct error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nella creazione del prodotto'];
        }
    }
    
    /**
     * Aggiorna un prodotto esistente
     */
    public function updateProduct($id, $data) {
        try {
            $product = new Product($data);
            $product->id = $id;
            
            $stmt = $this->db->prepare('
                UPDATE products
                SET name = ?, price = ?, stock_quantity = ?, description = ?
                WHERE id = ?
            ');
            
            $stmt->execute([
                $product->name,
                $product->price,
                $product->stock_quantity,
                $product->description,
                $id
            ]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'data' => $product];
            } else {
                return ['success' => false, 'error' => 'Prodotto non trovato o nessuna modifica'];
            }
        } catch (PDOException $e) {
            error_log("ProductService::updateProduct error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'aggiornamento del prodotto'];
        }
    }
    
    /**
     * Elimina un prodotto
     */
    public function deleteProduct($id) {
        try {
            $stmt = $this->db->prepare('DELETE FROM products WHERE id = ?');
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Prodotto eliminato'];
            } else {
                return ['success' => false, 'error' => 'Prodotto non trovato'];
            }
        } catch (PDOException $e) {
            error_log("ProductService::deleteProduct error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'eliminazione del prodotto'];
        }
    }
}
