<?php

namespace App\Repositories;

use App\Models\Category;
use PDO;

class CategoryRepository extends BaseRepository
{
    protected $table = 'categories';
    
    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY display_order ASC, name ASC";
        $stmt = $this->execute($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(Category $category)
    {
        $sql = "INSERT INTO {$this->table} (name, description, icon) 
                VALUES (:name, :description, :icon)";
        
        $this->execute($sql, [
            ':name' => $category->getName(),
            ':description' => $category->getDescription(),
            ':icon' => $category->getIcon()
        ]);

        return $this->db->lastInsertId();
    }

    public function update(Category $category)
    {
        $sql = "UPDATE {$this->table} SET name = :name, description = :description, icon = :icon WHERE id = :id";
        
        return $this->execute($sql, [
            ':id' => $category->getId(),
            ':name' => $category->getName(),
            ':description' => $category->getDescription(),
            ':icon' => $category->getIcon()
        ])->rowCount() > 0;
    }

    public function findByName($name)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = :name";
        $stmt = $this->execute($sql, [':name' => $name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCategoryWithRecipes($categoryId)
    {
        $sql = "SELECT c.*, COUNT(r.id) as recipe_count FROM {$this->table} c 
                LEFT JOIN recipes r ON c.id = r.category_id 
                WHERE c.id = :id GROUP BY c.id";
        $stmt = $this->execute($sql, [':id' => $categoryId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
