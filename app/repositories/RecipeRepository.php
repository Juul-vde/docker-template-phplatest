<?php

namespace App\Repositories;

use App\Models\Recipe;
use PDO;

class RecipeRepository extends BaseRepository
{
    protected $table = 'recipes';

    public function findAll()
    {
        $sql = "SELECT r.*, 
                GROUP_CONCAT(DISTINCT c.id ORDER BY c.display_order) as category_ids,
                GROUP_CONCAT(DISTINCT c.name ORDER BY c.display_order) as category_names,
                GROUP_CONCAT(DISTINCT c.icon ORDER BY c.display_order SEPARATOR '|||') as category_icons,
                GROUP_CONCAT(DISTINCT c.color ORDER BY c.display_order SEPARATOR '|||') as category_colors
                FROM {$this->table} r 
                LEFT JOIN recipe_categories rc ON r.id = rc.recipe_id
                LEFT JOIN categories c ON rc.category_id = c.id 
                GROUP BY r.id
                ORDER BY r.title ASC";
        $stmt = $this->execute($sql);
        $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Parse the concatenated categories
        foreach ($recipes as &$recipe) {
            if ($recipe['category_ids']) {
                $recipe['categories'] = $this->parseCategories(
                    $recipe['category_ids'],
                    $recipe['category_names'],
                    $recipe['category_icons'],
                    $recipe['category_colors']
                );
            } else {
                $recipe['categories'] = [];
            }
        }
        
        return $recipes;
    }
    
    private function parseCategories($ids, $names, $icons, $colors)
    {
        if (!$ids) return [];
        
        $idArray = explode(',', $ids);
        $nameArray = explode(',', $names);
        $iconArray = explode('|||', $icons);
        $colorArray = explode('|||', $colors);
        
        $categories = [];
        for ($i = 0; $i < count($idArray); $i++) {
            $categories[] = [
                'id' => $idArray[$i],
                'name' => $nameArray[$i] ?? '',
                'icon' => $iconArray[$i] ?? '',
                'color' => $colorArray[$i] ?? '#6c757d'
            ];
        }
        
        return $categories;
    }

    public function create(Recipe $recipe)
    {
        $sql = "INSERT INTO {$this->table} (title, description, instructions, image_url, prep_time, cook_time, servings, difficulty, category_id) 
                VALUES (:title, :description, :instructions, :image_url, :prep_time, :cook_time, :servings, :difficulty, :category_id)";
        
        $this->execute($sql, [
            ':title' => $recipe->getTitle(),
            ':description' => $recipe->getDescription(),
            ':instructions' => $recipe->getInstructions(),
            ':image_url' => $recipe->getImageUrl(),
            ':prep_time' => $recipe->getPrepTime(),
            ':cook_time' => $recipe->getCookTime(),
            ':servings' => $recipe->getServings(),
            ':difficulty' => $recipe->getDifficulty(),
            ':category_id' => $recipe->getCategory()
        ]);

        return $this->db->lastInsertId();
    }

    public function update(Recipe $recipe)
    {
        $sql = "UPDATE {$this->table} SET title = :title, description = :description, instructions = :instructions, 
                image_url = :image_url, prep_time = :prep_time, cook_time = :cook_time, servings = :servings, 
                difficulty = :difficulty, category_id = :category_id WHERE id = :id";
        
        return $this->execute($sql, [
            ':id' => $recipe->getId(),
            ':title' => $recipe->getTitle(),
            ':description' => $recipe->getDescription(),
            ':instructions' => $recipe->getInstructions(),
            ':image_url' => $recipe->getImageUrl(),
            ':prep_time' => $recipe->getPrepTime(),
            ':cook_time' => $recipe->getCookTime(),
            ':servings' => $recipe->getServings(),
            ':difficulty' => $recipe->getDifficulty(),
            ':category_id' => $recipe->getCategory()
        ])->rowCount() > 0;
    }

    public function findByCategory($categoryId)
    {
        $sql = "SELECT DISTINCT r.*,
                GROUP_CONCAT(DISTINCT c.id ORDER BY c.display_order) as category_ids,
                GROUP_CONCAT(DISTINCT c.name ORDER BY c.display_order) as category_names,
                GROUP_CONCAT(DISTINCT c.icon ORDER BY c.display_order SEPARATOR '|||') as category_icons,
                GROUP_CONCAT(DISTINCT c.color ORDER BY c.display_order SEPARATOR '|||') as category_colors
                FROM {$this->table} r 
                INNER JOIN recipe_categories rc ON r.id = rc.recipe_id
                LEFT JOIN recipe_categories rc2 ON r.id = rc2.recipe_id
                LEFT JOIN categories c ON rc2.category_id = c.id
                WHERE rc.category_id = :category_id
                GROUP BY r.id";
        $stmt = $this->execute($sql, [':category_id' => $categoryId]);
        $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Parse the concatenated categories
        foreach ($recipes as &$recipe) {
            if ($recipe['category_ids']) {
                $recipe['categories'] = $this->parseCategories(
                    $recipe['category_ids'],
                    $recipe['category_names'],
                    $recipe['category_icons'],
                    $recipe['category_colors']
                );
            } else {
                $recipe['categories'] = [];
            }
        }
        
        return $recipes;
    }

    public function findByTag($tagId)
    {
        $sql = "SELECT DISTINCT r.*,
                GROUP_CONCAT(DISTINCT c.id ORDER BY c.display_order) as category_ids,
                GROUP_CONCAT(DISTINCT c.name ORDER BY c.display_order) as category_names,
                GROUP_CONCAT(DISTINCT c.icon ORDER BY c.display_order SEPARATOR '|||') as category_icons,
                GROUP_CONCAT(DISTINCT c.color ORDER BY c.display_order SEPARATOR '|||') as category_colors
                FROM {$this->table} r 
                INNER JOIN recipe_tags rt ON r.id = rt.recipe_id
                LEFT JOIN recipe_categories rc ON r.id = rc.recipe_id
                LEFT JOIN categories c ON rc.category_id = c.id
                WHERE rt.tag_id = :tag_id
                GROUP BY r.id";
        $stmt = $this->execute($sql, [':tag_id' => $tagId]);
        $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Parse the concatenated categories
        foreach ($recipes as &$recipe) {
            if ($recipe['category_ids']) {
                $recipe['categories'] = $this->parseCategories(
                    $recipe['category_ids'],
                    $recipe['category_names'],
                    $recipe['category_icons'],
                    $recipe['category_colors']
                );
            } else {
                $recipe['categories'] = [];
            }
        }
        
        return $recipes;
    }

    public function getRecipeWithTags($recipeId)
    {
        $sql = "SELECT r.*, 
                GROUP_CONCAT(DISTINCT t.name) as tags,
                GROUP_CONCAT(DISTINCT c.id ORDER BY c.display_order) as category_ids,
                GROUP_CONCAT(DISTINCT c.name ORDER BY c.display_order) as category_names,
                GROUP_CONCAT(DISTINCT c.icon ORDER BY c.display_order SEPARATOR '|||') as category_icons,
                GROUP_CONCAT(DISTINCT c.color ORDER BY c.display_order SEPARATOR '|||') as category_colors
                FROM {$this->table} r 
                LEFT JOIN recipe_tags rt ON r.id = rt.recipe_id 
                LEFT JOIN tags t ON rt.tag_id = t.id
                LEFT JOIN recipe_categories rc ON r.id = rc.recipe_id
                LEFT JOIN categories c ON rc.category_id = c.id
                WHERE r.id = :id GROUP BY r.id";
        $stmt = $this->execute($sql, [':id' => $recipeId]);
        $recipe = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Parse categories
        if ($recipe && $recipe['category_ids']) {
            $recipe['categories'] = $this->parseCategories(
                $recipe['category_ids'],
                $recipe['category_names'],
                $recipe['category_icons'],
                $recipe['category_colors']
            );
        } else {
            $recipe['categories'] = [];
        }
        
        // Parse tags
        if ($recipe && $recipe['tags']) {
            $recipe['tags'] = explode(',', $recipe['tags']);
        }
        
        return $recipe;
    }

    public function addTag($recipeId, $tagId)
    {
        $sql = "INSERT INTO recipe_tags (recipe_id, tag_id) VALUES (:recipe_id, :tag_id)";
        return $this->execute($sql, [':recipe_id' => $recipeId, ':tag_id' => $tagId])->rowCount() > 0;
    }

    public function removeTag($recipeId, $tagId)
    {
        $sql = "DELETE FROM recipe_tags WHERE recipe_id = :recipe_id AND tag_id = :tag_id";
        return $this->execute($sql, [':recipe_id' => $recipeId, ':tag_id' => $tagId])->rowCount() > 0;
    }

    public function addIngredient($recipeId, $ingredientId, $quantity, $unit)
    {
        $sql = "INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit) 
                VALUES (:recipe_id, :ingredient_id, :quantity, :unit)";
        return $this->execute($sql, [
            ':recipe_id' => $recipeId,
            ':ingredient_id' => $ingredientId,
            ':quantity' => $quantity,
            ':unit' => $unit
        ])->rowCount() > 0;
    }

    public function removeIngredient($recipeId, $ingredientId)
    {
        $sql = "DELETE FROM recipe_ingredients WHERE recipe_id = :recipe_id AND ingredient_id = :ingredient_id";
        return $this->execute($sql, [':recipe_id' => $recipeId, ':ingredient_id' => $ingredientId])->rowCount() > 0;
    }

    public function removeAllIngredients($recipeId)
    {
        $sql = "DELETE FROM recipe_ingredients WHERE recipe_id = :recipe_id";
        return $this->execute($sql, [':recipe_id' => $recipeId])->rowCount() > 0;
    }

    public function search($keyword)
    {
        $sql = "SELECT DISTINCT r.*,
                GROUP_CONCAT(DISTINCT c.id ORDER BY c.display_order) as category_ids,
                GROUP_CONCAT(DISTINCT c.name ORDER BY c.display_order) as category_names,
                GROUP_CONCAT(DISTINCT c.icon ORDER BY c.display_order SEPARATOR '|||') as category_icons,
                GROUP_CONCAT(DISTINCT c.color ORDER BY c.display_order SEPARATOR '|||') as category_colors
                FROM {$this->table} r
                LEFT JOIN recipe_categories rc ON r.id = rc.recipe_id
                LEFT JOIN categories c ON rc.category_id = c.id
                WHERE r.title LIKE :keyword OR r.description LIKE :keyword
                GROUP BY r.id";
        $stmt = $this->execute($sql, [':keyword' => "%$keyword%"]);
        $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Parse the concatenated categories
        foreach ($recipes as &$recipe) {
            if ($recipe['category_ids']) {
                $recipe['categories'] = $this->parseCategories(
                    $recipe['category_ids'],
                    $recipe['category_names'],
                    $recipe['category_icons'],
                    $recipe['category_colors']
                );
            } else {
                $recipe['categories'] = [];
            }
        }
        
        return $recipes;
    }
    
    public function addCategory($recipeId, $categoryId)
    {
        $sql = "INSERT IGNORE INTO recipe_categories (recipe_id, category_id) VALUES (:recipe_id, :category_id)";
        return $this->execute($sql, [':recipe_id' => $recipeId, ':category_id' => $categoryId])->rowCount() > 0;
    }
    
    public function removeCategory($recipeId, $categoryId)
    {
        $sql = "DELETE FROM recipe_categories WHERE recipe_id = :recipe_id AND category_id = :category_id";
        return $this->execute($sql, [':recipe_id' => $recipeId, ':category_id' => $categoryId])->rowCount() > 0;
    }
    
    public function removeAllCategories($recipeId)
    {
        $sql = "DELETE FROM recipe_categories WHERE recipe_id = :recipe_id";
        return $this->execute($sql, [':recipe_id' => $recipeId])->rowCount() > 0;
    }
}
